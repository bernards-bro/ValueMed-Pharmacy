<?php

require 'connection.php';

$fullname = $_SESSION['fullname'] ?? 'Admin';

$sql = "
    SELECT
        medicine_id,
        name,
        description,
        category,
        price,
        stock,
        expiry_date
    FROM medicines
    ORDER BY name ASC
";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html>

<head>

<title>Products</title>

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

/* Main */

.main{
margin-left:250px;
width:calc(100% - 250px);
padding:30px;
}

.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
}

.admin{
background:white;
padding:10px 20px;
border-radius:30px;
box-shadow:0 5px 10px rgba(0,0,0,.08);
}

/* Top */

.top{
display:flex;
justify-content:space-between;
margin-bottom:20px;
}

.search{
width:300px;
padding:12px;
border:none;
border-radius:30px;
box-shadow:0 5px 10px rgba(0,0,0,.08);
outline:none;
}

.add-btn{
background:#16246D;
color:white;
padding:12px 22px;
border:none;
border-radius:30px;
cursor:pointer;
}

.add-btn:hover{
background:#233b9a;
}

table{
width:100%;
border-collapse:collapse;
background:white;
border-radius:15px;
overflow:hidden;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

th{
background:#16246D;
color:white;
padding:15px;
}

td{
padding:15px;
text-align:center;
border-bottom:1px solid #eee;
}

tr:hover{
background:#f5f7fc;
}

.edit{
background:#28a745;
color:white;
padding:8px 12px;
border-radius:6px;
text-decoration:none;
margin-right:5px;
}

.delete{
background:#dc3545;
color:white;
padding:8px 12px;
border-radius:6px;
text-decoration:none;
}

/* =========================
   QR BUTTON
========================= */

.qr-btn{
    background:#16246D;
    color:white;
    border:none;
    padding:8px 12px;
    border-radius:6px;
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


@media(max-width:650px){

    .qr-modal-content{
        padding:20px;
    }

    .qr-modal-buttons{
        flex-direction:column;
    }

    .qr-modal-buttons button{
        width:100%;
    }

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

    <a href="products.php" class="active">
        <i class="fas fa-pills"></i>
        Products
    </a>

    <a href="inventory.php">
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

<div class="main">

<div class="header">

<h1>Products</h1>

<div class="admin">

    <i class="fas fa-user"></i>

    <?= htmlspecialchars($fullname); ?>

</div>

</div>

<div class="top">

<input
type="text"
class="search"
placeholder="Search products...">

<a href="add_products.php" class="add-btn">
<i class="fas fa-plus"></i>
ADD PRODUCT
</a>

</div>

<table>

<tr>

<th>ID</th>
<th>Name</th>
<th>Description</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Expiry Date</th>
<th>QR Code</th>
<th>Actions</th>

</tr>

<?php

foreach($result as $p){

?>

<tr>

<td><?= $p['medicine_id'];?></td>

<td><?= htmlspecialchars($p['name']); ?></td>

<td><?= htmlspecialchars($p['description'] ?? ''); ?></td>

<td><?= htmlspecialchars($p['category'] ?? ''); ?></td>

<td>₱<?= number_format($p['price'], 2); ?></td>

<td><?= (int)$p['stock']; ?></td>

<td>

<?php

if (!empty($p['expiry_date'])) {

    echo date(
        'M d, Y',
        strtotime($p['expiry_date'])
    );

} else {

    echo 'No expiry date';

}

?>

</td>


<td>

<button
    type="button"
    class="qr-btn"
    onclick="showQR(
        <?= (int)$p['medicine_id']; ?>,
        '<?= htmlspecialchars(
            $p['name'],
            ENT_QUOTES
        ); ?>'
    )">

    <i class="fas fa-qrcode"></i>

    QR

</button>

</td>


<td>

<a
    href="edit.php?id=<?= $p['medicine_id']; ?>"
    class="edit">

    <i class="fas fa-edit"></i>

</a>


<a
    href="delete.php?id=<?= $p['medicine_id']; ?>"
    class="delete">

    <i class="fas fa-trash"></i>

</a>

</td>

</tr>

<?php }

?>

</table>

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


/* =========================
   SHOW QR
========================= */

function showQR(medicineId, productName)
{
    currentQRValue = "VM-MED-" + medicineId;

    currentProductName = productName;


    document.getElementById(
        "qrProductName"
    ).textContent = productName;


    document.getElementById(
        "qrProductValue"
    ).textContent = currentQRValue;


    const qrContainer =
        document.getElementById(
            "productQRCode"
        );


    /* Clear previous QR */
    qrContainer.innerHTML = "";


    /* Generate QR */
    new QRCode(
        qrContainer,
        {
            text: currentQRValue,

            width: 250,
            height: 250,

            correctLevel:
                QRCode.CorrectLevel.H
        }
    );


    /* Show modal */
    document.getElementById(
        "qrModal"
    ).style.display = "flex";
}


/* =========================
   CLOSE QR
========================= */

function closeQR()
{
    document.getElementById(
        "qrModal"
    ).style.display = "none";
}


/* =========================
   DOWNLOAD QR
========================= */

function downloadProductQR()
{
    const canvas =
        document.querySelector(
            "#productQRCode canvas"
        );


    if (!canvas) {

        alert(
            "QR code is not ready yet."
        );

        return;
    }


    const link =
        document.createElement("a");


    link.download =
        currentQRValue + ".png";


    link.href =
        canvas.toDataURL(
            "image/png"
        );


    link.click();
}


/* =========================
   PRINT QR
========================= */

function printProductQR()
{
    if (!currentQRValue) {

        alert(
            "QR code is not ready yet."
        );

        return;
    }


    const qrCanvas =
        document.querySelector(
            "#productQRCode canvas"
        );


    if (!qrCanvas) {

        alert(
            "QR code is not ready yet."
        );

        return;
    }


    const qrImage =
        qrCanvas.toDataURL(
            "image/png"
        );


    const printWindow =
        window.open(
            "",
            "",
            "width=500,height=700"
        );


    if (!printWindow) {

        alert(
            "Please allow pop-ups for this website."
        );

        return;
    }


    printWindow.document.write(`

        <!DOCTYPE html>

        <html>

        <head>

            <meta charset="UTF-8">

            <title>
                ValueMeds QR Label
            </title>


            <style>

                @page {

                    /*
                       79.5 + 0.5 mm printer
                       = approximately 80 mm
                    */

                    size: 80mm auto;

                    margin: 0;
                }


                * {

                    box-sizing: border-box;
                }


                html,
                body {

                    margin: 0;

                    padding: 0;

                    width: 80mm;

                    background: white;

                    font-family:
                        Arial,
                        sans-serif;
                }


                body {

                    display: flex;

                    justify-content: center;
                }


                .label {

                    width: 79.5mm;

                    text-align: center;

                    padding:
                        5mm
                        4mm
                        6mm
                        4mm;
                }


                .brand {

                    font-size: 20px;

                    font-weight: bold;

                    color: #16246D;

                    margin-bottom: 3mm;
                }


                .product-name {

                    font-size: 14px;

                    font-weight: bold;

                    margin-bottom: 3mm;

                    word-wrap: break-word;
                }


                .qr-code {

                    width: 50mm;

                    height: 50mm;

                    margin:
                        0 auto
                        3mm
                        auto;
                }


                .qr-code img {

                    display: block;

                    width: 50mm;

                    height: 50mm;

                    margin: 0 auto;
                }


                .qr-value {

                    font-size: 13px;

                    font-weight: bold;

                    letter-spacing: 1px;

                    margin-top: 2mm;
                }


                .instruction {

                    font-size: 9px;

                    color: #666;

                    margin-top: 2mm;
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


                <div class="qr-code">

                    <img
                        src="${qrImage}"
                        alt="ValueMeds QR Code">

                </div>


                <div class="qr-value">

                    ${currentQRValue}

                </div>


                <div class="instruction">

                    Scan to identify product

                </div>


            </div>

        </body>

        </html>

    `);


    printWindow.document.close();


    /*
       Wait for the QR image to load
       before opening print preview.
    */

    setTimeout(
        function()
        {

            printWindow.focus();

            printWindow.print();

            printWindow.close();

        },
        500
    );
}


/* =========================
   ESCAPE HTML
========================= */

function escapeHTML(value)
{
    return String(value)
        .replace(
            /&/g,
            "&amp;"
        )
        .replace(
            /</g,
            "&lt;"
        )
        .replace(
            />/g,
            "&gt;"
        )
        .replace(
            /"/g,
            "&quot;"
        )
        .replace(
            /'/g,
            "&#039;"
        );
}


/* =========================
   CLOSE WHEN CLICKING OUTSIDE
========================= */

window.onclick =
    function(event)
    {

        const modal =
            document.getElementById(
                "qrModal"
            );


        if (
            event.target === modal
        ) {

            closeQR();

        }

    };

</script>

</body>

</html>