<?php
require 'connection.php';

$fullname = $_SESSION['fullname'] ?? 'Admin';

/* =========================
   DELETE MEDICINE
========================= */
if (isset($_POST['delete'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("
        DELETE FROM medicines
        WHERE medicine_id = ?
    ");

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Medicine deleted successfully.";

    } else {
        $error = "Unable to delete medicine. It may already be used in a sale or stock record.";
    }
    $stmt->close();
}


/* =========================
   UPDATE STOCK
========================= */
if (isset($_POST['update_stock'])) {
    $id = intval($_POST['id']);
    $stock = intval($_POST['stock']);

    if ($stock < 0) {
        $error = "Stock cannot be negative.";

    } else {
        $stmt = $conn->prepare("
            UPDATE medicines
            SET stock = ?
            WHERE medicine_id = ?
        ");

        $stmt->bind_param("ii", $stock, $id);

        if ($stmt->execute()) {
            $message = "Stock updated successfully.";
        } else {
            $error = "Failed to update stock.";
        }
        $stmt->close();
    }
}

/* =========================
   SEARCH
========================= */
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

/* =========================
   GET MEDICINES
========================= */
if ($search !== "") {
    $stmt = $conn->prepare("
        SELECT
            medicine_id,
            name,
            category,
            stock,
            expiry_date
        FROM medicines
        WHERE name LIKE ?
        OR category LIKE ?
        ORDER BY name ASC
    ");

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "ss",
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $medicines = $stmt->get_result();

} else {
    $medicines = $conn->query("
        SELECT
            medicine_id,
            name,
            category,
            stock,
            expiry_date
        FROM medicines
        ORDER BY name ASC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Inventory | ValueMeds</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<script
src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js">
</script>
<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Segoe UI,sans-serif;
}

body{
    background:#F5F7FC;
    display:flex;
}


/* =========================
   SIDEBAR
========================= */

.sidebar{
    width:250px;
    height:100vh;
    background:#16246D;
    color:white;
    position:fixed;
    left:0;
    top:0;
    overflow:auto;
}

.logo{
    padding:25px;
    font-size:25px;
    font-weight:bold;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.menu-title{
    padding:20px 25px 10px;
    font-size:13px;
    opacity:.7;
    letter-spacing:1px;
}

.sidebar a{
    display:block;
    padding:14px 25px;
    color:white;
    text-decoration:none;
    transition:.3s;
}

.sidebar a i{
    width:25px;
}

.sidebar a:hover,
.sidebar .active{
    background:#8FB3E2;
    color:#16246D;
}

/* =========================
   MAIN
========================= */

.main{
    margin-left:250px;
    width:calc(100% - 250px);
    padding:30px;
}


/* =========================
   HEADER
========================= */

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.header h1{
    color:#16246D;
}

.admin{
    background:white;
    padding:10px 18px;
    border-radius:30px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    font-weight:600;
}


/* =========================
   CONTAINER
========================= */

.container{
    background:white;
    padding:25px;
    border-radius:18px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}


/* =========================
   TOP SECTION
========================= */

.top-section{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.top-section h2{
    color:#16246D;
}


/* =========================
   SEARCH
========================= */

.search-form{
    display:flex;
    gap:10px;
}

.search-form input{
    width:300px;
    padding:12px 15px;
    border:1px solid #ccc;
    border-radius:10px;
    outline:none;
}

.search-form button{
    background:#16246D;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:10px;
    cursor:pointer;
}

.search-form button:hover{
    background:#2b45b5;
}


/* =========================
   MESSAGES
========================= */

.message{
    background:#dff5e1;
    color:#1b6b2a;
    padding:12px 15px;
    border-radius:10px;
    margin-bottom:20px;
}

.error{
    background:#ffe0e0;
    color:#a00000;
    padding:12px 15px;
    border-radius:10px;
    margin-bottom:20px;
}


/* =========================
   TABLE
========================= */

.table-wrapper{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#16246D;
    color:white;
    padding:15px;
    text-align:left;
}

td{
    padding:14px 15px;
    border-bottom:1px solid #eee;
}

tr:hover{
    background:#F5F7FC;
}


/* =========================
   STATUS
========================= */

.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
    font-weight:bold;
}

.in-stock{
    background:#dff5e1;
    color:#1b6b2a;
}

.low-stock{
    background:#fff0c2;
    color:#856404;
}

.out-stock{
    background:#ffe0e0;
    color:#a00000;
}


/* =========================
   ACTION BUTTONS
========================= */

.actions{
    display:flex;
    gap:8px;
}

.edit-btn,
.delete-btn{
    border:none;
    padding:8px 12px;
    border-radius:8px;
    cursor:pointer;
}

.edit-btn{
    background:#e7efff;
    color:#16246D;
}

.delete-btn{
    background:#ffe0e0;
    color:#a00000;
}


/* =========================
   EDIT FORM
========================= */

.edit-form{
    display:flex;
    gap:5px;
    align-items:center;
}

.edit-form input{
    width:80px;
    padding:7px;
    border:1px solid #ccc;
    border-radius:7px;
}

.edit-form button{
    background:#16246D;
    color:white;
    border:none;
    padding:7px 10px;
    border-radius:7px;
    cursor:pointer;
}


/* =========================
   RESPONSIVE
========================= */
@media(max-width:900px){

    .sidebar{
        width:200px;
    }
    .main{
        margin-left:200px;
        width:calc(100% - 200px);
        padding:20px;
    }
    .top-section{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }
    .search-form input{
        width:250px;
    }
   
}

 /* =========================
       QR BUTTON
    ========================= */

    .qr-btn{
        background:#16246D;
        color:white;
        border:none;
        padding:8px 12px;
        border-radius:8px;
        cursor:pointer;
    }

    .qr-btn:hover{
        background:#2b45b5;
    }

    /* =========================
       QR MODAL
    ========================= */

    .qr-modal{
        display:none;
        position:fixed;
        z-index:9999;
        left:0;
        top:0;
        width:100%;
        height:100%;
        background:rgba(0,0,0,.6);

        justify-content:center;
        align-items:center;
    }

    .qr-modal-content{
        position:relative;
        background:white;
        width:420px;
        max-width:90%;
        padding:30px;
        border-radius:20px;
        text-align:center;

        box-shadow:0 10px 30px rgba(0,0,0,.25);
    }

    .qr-modal-content h2{
        color:#16246D;
        margin-bottom:10px;
    }

    .qr-product-name{
        color:#555;
        margin-bottom:15px;
    }

    .qr-product-value{
        color:#16246D;
        font-weight:600;
        font-size:16px;
        margin:15px 0 20px;
    }

    .product-qrcode{
        display:flex;
        justify-content:center;
        margin:20px 0;
    }

    .qr-close{
        position:absolute;
        top:12px;
        right:18px;

        border:none;
        background:none;

        font-size:30px;
        color:#555;

        cursor:pointer;
    }

    .qr-close:hover{
        color:#16246D;
    }

    .qr-modal-buttons{
        display:flex;
        justify-content:center;
        gap:10px;
    }

    .qr-modal-buttons button{
        border:none;
        color:white;
        padding:11px 18px;
        border-radius:8px;
        cursor:pointer;
        font-weight:600;
    }

    .qr-modal-buttons .download-qr{
        background:#16246D;
    }

    .qr-modal-buttons .print-qr{
        background:#555;
    }

    .qr-modal-buttons .download-qr:hover{
        background:#2b45b5;
    }

    .qr-modal-buttons .print-qr:hover{
        background:#333;
    }
</style>
</head>

<body>
<!-- =========================
     SIDEBAR
========================= -->
<div class="sidebar">
    <div class="logo">
        ValueMeds
    </div>

    <a href="dashboard.php">
        <i class="fas fa-home"></i>
        Dashboard
    </a>

    <div class="menu-title">
        INVENTORY
    </div>

    <a href="products.php">
        <i class="fas fa-pills"></i>
        Products
    </a>

    <a href="inventory.php" class="active">
        <i class="fas fa-box-open"></i>
        Inventory
    </a>

    <a href="stock_alerts.php">
        <i class="fas fa-triangle-exclamation"></i>
        Stock Alerts
    </a>

    <div class="menu-title">
    SALES
    </div>

    <a href="pos.php">
        <i class="fas fa-cash-register"></i>
        Point of Sale
    </a>

    <a href="sales_history.php">
        <i class="fas fa-clock-rotate-left"></i>
        Sales History
    </a>

    <a href="reports.php">
        <i class="fas fa-chart-column"></i>
        Reports
    </a>
</div>


<!-- =========================
     MAIN
========================= -->

<div class="main">
    <div class="header">
        <h1>INVENTORY</h1>
        <div class="admin">

    <i class="fas fa-user"></i>

    <?= htmlspecialchars($fullname); ?>

</div>
    </div>

    <div class="container">
        <div class="top-section">
            <h2>
                <i class="fas fa-boxes"></i>
                Medicine Inventory
            </h2>


            <!-- SEARCH -->
            <form method="GET" class="search-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search medicine..."
                    value="<?= htmlspecialchars($search) ?>">

                <button type="submit">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </form>
        </div>


        <!-- MESSAGES -->
        <?php if(isset($message)): ?>
            <div class="message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>


        <!-- =========================
             MEDICINE TABLE
        ========================= -->
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Medicine</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Expiration</th>
                    <th>QR Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($medicines->num_rows > 0): 
                ?>
                    <?php 
                    while ($medicine = $medicines->fetch_assoc()): 
                    ?>
                        <?php
                        /* =========================
                           DETERMINE STOCK STATUS
                        ========================= */
                        $stock = (int)$medicine['stock'];

                        if ($stock == 0) {
                            $status = "Out of Stock";
                            $statusClass = "out-stock";
                        } 
                        
                        elseif ($stock <= 20) {
                            $status = "Low Stock";
                            $statusClass = "low-stock";
                        } 

                        else {
                            $status = "In Stock";
                            $statusClass = "in-stock";
                        }

                        /* =========================
                           EXPIRY DATE
                        ========================= */
                        if (!empty($medicine['expiry_date'])) {
                            $expiryDate = date(
                                "M d, Y",
                                strtotime($medicine['expiry_date'])
                            );
                        } 

                        else {
                            $expiryDate = "No expiry date";
                        }
                        ?>

                        <tr>
                            <!-- ID -->
                            <td>
                                <?= (int)$medicine['medicine_id'] ?>
                            </td>

                            <!-- MEDICINE NAME -->
                            <td>
                                <strong>
                                    <?= htmlspecialchars($medicine['name']) ?>
                                </strong>
                            </td>

                            <!-- CATEGORY -->
                            <td>
                                <?= htmlspecialchars($medicine['category'] ?? 'N/A') ?>
                            </td>

                            <!-- STOCK -->
                            <td>
                                <form method="POST" class="edit-form">

                                    <input
                                        type="number"
                                        name="stock"
                                        min="0"
                                        value="<?= $stock ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$medicine['medicine_id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="update_stock"
                                        title="Update Stock"
                                    >
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- STATUS -->
                            <td>
                                <span class="status <?= $statusClass ?>">
                                    <?= $status ?>
                                </span>
                            </td>

                            <!-- EXPIRATION -->
                            <td>
                                <?= htmlspecialchars($expiryDate) ?>
                            </td>

                            <!-- QR CODE -->
                            <td>

                                <button
                                    type="button"
                                    class="qr-btn"
                                    onclick="showQR(
                                        <?= (int)$medicine['medicine_id'] ?>,
                                        '<?= htmlspecialchars(
                                            $medicine['name'],
                                            ENT_QUOTES
                                        ) ?>'
                                    )">

                                    <i class="fas fa-qrcode"></i>
                                    QR

                                </button>

                            </td>

                            <!-- ACTIONS -->
                            <td>
                                <div class="actions">

                                    <!-- DELETE -->
                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this medicine?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$medicine['medicine_id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete"
                                            class="delete-btn"
                                            title="Delete Medicine"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php
                    endwhile; 
                    ?>
                <?php 
                else: 
                ?>
                    <tr>
                        <td
                           colspan="8"
                            style="text-align:center;padding:30px;"
                        >
                            No medicines found.
                        </td>
                    </tr>
                <?php
                endif;
                ?>
                </tbody>
        </table>
        </div>
    </div>
</div>
<!-- QR MODAL -->

<div
    id="qrModal"
    class="qr-modal">

    <div class="qr-modal-content">

        <button
            type="button"
            class="qr-close"
            onclick="closeQR()">

            &times;

        </button>

        <h2>
            Product QR Code
        </h2>

        <p
            id="qrProductName"
            class="qr-product-name">
        </p>

        <div
            id="productQRCode"
            class="product-qrcode">
        </div>

        <div
            id="qrProductValue"
            class="qr-product-value">
        </div>

        <div class="qr-modal-buttons">

            <button
                type="button"
                class="download-qr"
                onclick="downloadProductQR()">

                <i class="fas fa-download"></i>
                Download

            </button>

            <button
                type="button"
                class="print-qr"
                onclick="printProductQR()">

                <i class="fas fa-print"></i>
                Print

            </button>

        </div>

    </div>

</div>

<script>
let currentQRValue = "";
let currentProductName = "";

function showQR(medicineId, productName)
{
    currentQRValue = "VM-MED-" + medicineId;
    currentProductName = productName;

    document.getElementById("qrProductName").textContent =
        productName;

    document.getElementById("qrProductValue").textContent =
        currentQRValue;

    const qrContainer =
        document.getElementById("productQRCode");

    qrContainer.innerHTML = "";

    new QRCode(
        qrContainer,
        {
            text: currentQRValue,
            width: 250,
            height: 250,
            correctLevel: QRCode.CorrectLevel.H
        }
    );

    document.getElementById("qrModal").style.display = "flex";
}

function closeQR()
{
    document.getElementById("qrModal").style.display = "none";
}

function downloadProductQR()
{
    const canvas =
        document.querySelector("#productQRCode canvas");

    if (!canvas) {
        alert("QR code is not ready yet.");
        return;
    }

    const link = document.createElement("a");

    link.download = currentQRValue + ".png";

    link.href = canvas.toDataURL("image/png");

    link.click();
}

function printProductQR()
{
    if (!currentQRValue) {
        alert("QR code is not ready yet.");
        return;
    }

    const qrCanvas =
        document.querySelector("#productQRCode canvas");

    if (!qrCanvas) {
        alert("QR code is not ready yet.");
        return;
    }

    /*
     * Create a completely separate PNG image
     * from the QR canvas.
     */
    const qrImage =
        qrCanvas.toDataURL("image/png");


    const printWindow =
        window.open(
            "",
            "_blank",
            "width=500,height=700"
        );


    if (!printWindow) {

        alert(
            "Please allow pop-ups for this website."
        );

        return;
    }


    printWindow.document.open();


    printWindow.document.write(`

        <!DOCTYPE html>

        <html>

        <head>

            <meta charset="UTF-8">

            <title>ValueMeds QR Label</title>


            <style>

                @page {

                    size: 80mm auto;

                    margin: 0;
                }


                html,
                body {

                    margin: 0;

                    padding: 0;

                    width: 80mm;

                    background: #ffffff;

                    font-family: Arial, sans-serif;

                    -webkit-print-color-adjust: exact;

                    print-color-adjust: exact;
                }


                * {

                    box-sizing: border-box;
                }


                body {

                    display: block;
                }


                .label {

                    width: 79.5mm;

                    text-align: center;

                    padding:
                        4mm
                        3mm
                        5mm
                        3mm;

                    margin: 0 auto;
                }


                .brand {

                    font-size: 19px;

                    font-weight: bold;

                    color: #000000;

                    margin-bottom: 2mm;
                }


                .product-name {

                    font-size: 14px;

                    font-weight: bold;

                    color: #000000;

                    margin-bottom: 3mm;

                    line-height: 1.2;

                    overflow-wrap: break-word;

                    word-break: break-word;
                }


                /*
                 * QR IMAGE
                 *
                 * Use a fixed physical size instead
                 * of relying on the original canvas size.
                 */
                .qr-image {

                    display: block;

                    width: 50mm;

                    height: 50mm;

                    min-width: 50mm;

                    min-height: 50mm;

                    max-width: 50mm;

                    max-height: 50mm;

                    margin: 0 auto 3mm auto;

                    object-fit: contain;

                    image-rendering: pixelated;
                }


                .qr-value {

                    font-size: 12px;

                    font-weight: bold;

                    color: #000000;

                    letter-spacing: 1px;

                    margin-top: 1mm;
                }


                .instruction {

                    font-size: 9px;

                    color: #000000;

                    margin-top: 2mm;
                }


                /*
                 * Prevent the QR image from being
                 * accidentally treated as a background.
                 */
                img {

                    visibility: visible !important;

                    opacity: 1 !important;
                }


                @media print {

                    html,
                    body {

                        width: 80mm;

                        margin: 0;

                        padding: 0;
                    }


                    .label {

                        width: 79.5mm;

                        margin: 0;

                        padding:
                            4mm
                            3mm
                            5mm
                            3mm;
                    }


                    .qr-image {

                        display: block !important;

                        visibility: visible !important;

                        opacity: 1 !important;

                        width: 50mm !important;

                        height: 50mm !important;
                    }

                }

            </style>

        </head>


        <body>

            <div class="label">


                <div class="brand">
                    ValueMeds
                </div>


                <div class="product-name">
                    ${escapeHTML(currentProductName)}
                </div>


                <img
                    id="qrPrintImage"
                    class="qr-image"
                    src="${qrImage}"
                    alt="ValueMeds QR Code"
                >


                <div class="qr-value">
                    ${currentQRValue}
                </div>


                <div class="instruction">
                    Scan to identify product
                </div>


            </div>


            <script>

                const qrImage =
                    document.getElementById(
                        "qrPrintImage"
                    );


                function startPrinting()
                {

                    if (!qrImage) {

                        window.print();

                        return;
                    }


                    /*
                     * Make absolutely sure the PNG
                     * has finished decoding.
                     */
                    if (
                        qrImage.complete &&
                        qrImage.naturalWidth > 0
                    ) {

                        qrImage.decode()
                            .then(function()
                            {

                                setTimeout(
                                    function()
                                    {

                                        window.focus();

                                        window.print();

                                    },
                                    300
                                );

                            })
                            .catch(function()
                            {

                                setTimeout(
                                    function()
                                    {

                                        window.focus();

                                        window.print();

                                    },
                                    300
                                );

                            });

                    }

                    else {

                        qrImage.onload =
                            function()
                            {

                                setTimeout(
                                    function()
                                    {

                                        window.focus();

                                        window.print();

                                    },
                                    300
                                );

                            };

                    }

                }


                window.onload =
                    function()
                    {

                        startPrinting();

                    };

            <\/script>

        </body>

        </html>

    `);


    printWindow.document.close();
}

function escapeHTML(value)
{
    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

window.onclick = function(event)
{
    const modal =
        document.getElementById("qrModal");

    if (event.target === modal) {
        closeQR();
    }
};
</script>
</body>
</html>