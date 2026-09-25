<?php

require 'connection.php';

$message = "";
$error = "";

$medicine_id = null;
$qr_value = "";


/*
|--------------------------------------------------------------------------
| Add Product
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');

    $cost_price = floatval($_POST['cost_price'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);

    $stock = intval($_POST['stock'] ?? 0);

    $expiry_date = $_POST['expiry_date'] ?? '';


    /*
     * Basic validation
     */

    if ($name === '') {

        $error = "Product name is required.";

    } elseif ($price < 0 || $cost_price < 0) {

        $error = "Price cannot be negative.";

    } elseif ($stock < 0) {

        $error = "Stock cannot be negative.";

    } elseif ($expiry_date === '') {

        $error = "Expiration date is required.";

    } else {

        /*
         * IMPORTANT:
         *
         * We ALWAYS INSERT a new row.
         *
         * Even if the same product already exists.
         *
         * This allows the same medicine to have
         * different expiration dates.
         */

        $stmt = $conn->prepare("
            INSERT INTO medicines
            (
                name,
                description,
                category,
                price,
                cost_price,
                stock,
                expiry_date
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssddis",
            $name,
            $description,
            $category,
            $price,
            $cost_price,
            $stock,
            $expiry_date
        );


        if ($stmt->execute()) {

    /*
     * Get the ID of the newly created medicine.
     */
    $medicine_id = $stmt->insert_id;

    /*
     * Generate the value that will be stored
     * inside the QR code.
     */
    $qr_value = "VM-MED-" . $medicine_id;

    $message = "Product added successfully.";

    /*
     * Clear the form values after successful insert.
     */
    $name = "";
    $description = "";
    $category = "";
    $cost_price = "";
    $price = "";
    $stock = "";
    $expiry_date = "";

} else {

    $error = "Failed to add product: " . $stmt->error;
}


        $stmt->close();
    }
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Add Product | ValueMeds</title>

<link
rel="stylesheet"
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

.header{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:25px;
}

.header h1{
color:#16246D;
}

.admin{
background:white;
padding:10px 20px;
border-radius:30px;
box-shadow:0 5px 10px rgba(0,0,0,.08);
}


/* =========================
   FORM
========================= */

.container{
background:white;
padding:30px;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
max-width:900px;
}

.form-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

.group{
display:flex;
flex-direction:column;
}

.group.full{
grid-column:1 / -1;
}

.group label{
margin-bottom:8px;
font-weight:600;
color:#16246D;
}

.group input,
.group select,
.group textarea{
padding:12px;
border:1px solid #ccc;
border-radius:10px;
outline:none;
font-size:15px;
}

.group textarea{
resize:vertical;
min-height:100px;
}

.group input:focus,
.group select:focus,
.group textarea:focus{
border-color:#16246D;
}


/* =========================
   BUTTONS
========================= */

.buttons{
display:flex;
justify-content:flex-end;
gap:15px;
margin-top:25px;
}

.cancel{
background:#ccc;
color:#222;
padding:12px 25px;
border:none;
border-radius:10px;
text-decoration:none;
cursor:pointer;
}

.cancel:hover{
background:#b8b8b8;
}

.save{
background:#16246D;
color:white;
padding:12px 25px;
border:none;
border-radius:10px;
cursor:pointer;
font-weight:600;
}

.save:hover{
background:#2b45b5;
}


/* =========================
   MESSAGES
========================= */

.success{
background:#dff5e5;
color:#176b32;
padding:12px 15px;
border-radius:10px;
margin-bottom:20px;
}

.error{
background:#fde2e2;
color:#a51d1d;
padding:12px 15px;
border-radius:10px;
margin-bottom:20px;
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
}

.form-grid{
grid-template-columns:1fr;
}

.group.full{
grid-column:auto;
}

}


@media(max-width:650px){

.sidebar{
display:none;
}

.main{
margin-left:0;
width:100%;
padding:15px;
}

.container{
padding:20px;
}

.header h1{
font-size:24px;
}
}

/* =========================
   QR CODE
========================= */
.qr-box{
    margin-top:25px;
    background:#f8f9ff;
    border:1px solid #dfe3f5;
    border-radius:15px;
    padding:25px;
    text-align:center;
}

.qr-box h2{
    color:#16246D;
    margin-bottom:8px;
}

.qr-box p{
    color:#666;
    margin-bottom:20px;
}

.qrcode{
    display:flex;
    justify-content:center;
    margin:20px 0;
}

.qr-value{
    font-weight:600;
    color:#16246D;
    margin-bottom:20px;
    font-size:16px;
}

.qr-buttons{
    display:flex;
    justify-content:center;
    gap:10px;
}

.download-qr,
.print-qr{
    padding:11px 18px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    color:white;
    font-weight:600;
}

.download-qr{
    background:#16246D;
}

.print-qr{
    background:#555;
}

.download-qr:hover{
    background:#2b45b5;
}

.print-qr:hover{
    background:#333;
}

@media(max-width:650px){

    .qr-buttons{
        flex-direction:column;
    }

    .download-qr,
    .print-qr{
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


<!-- =========================================================
     MAIN
========================================================= -->

<div class="main">


<div class="header">

<h1>
Add Product
</h1>


<div class="admin">

<i class="fas fa-user"></i>

<?php

echo htmlspecialchars(
    $_SESSION['fullname'] ?? 'Admin'
);

?>

</div>

</div>


<div class="container">


<?php if ($message !== ''): ?>

<div class="success">

<i class="fas fa-circle-check"></i>

<?php
echo htmlspecialchars($message);
?>

</div>

<?php endif; ?>


<!-- =========================================================
     QR CODE
========================================================= -->
<?php if ($medicine_id !== null): ?>

<div class="qr-box">

    <h2>
        Product QR Code
    </h2>

    <p>
        Scan this QR code to identify this product.
    </p>

    <div
        id="qrcode"
        class="qrcode">
    </div>

    <div class="qr-value">

        <?= htmlspecialchars($qr_value); ?>

    </div>

    <div class="qr-buttons">

        <button
            type="button"
            class="download-qr"
            onclick="downloadQR()">

            <i class="fas fa-download"></i>

            Download QR

        </button>

        <button
            type="button"
            class="print-qr"
            onclick="printQR()">

            <i class="fas fa-print"></i>

            Print QR

        </button>

    </div>

</div>

<script>

const qrValue = <?= json_encode($qr_value); ?>;

new QRCode(
    document.getElementById("qrcode"),
    {
        text: qrValue,
        width: 220,
        height: 220
    }
);


function downloadQR()
{
    const canvas =
        document.querySelector("#qrcode canvas");

    if (!canvas) {
        alert("QR code is not ready yet.");
        return;
    }

    const link =
        document.createElement("a");

    link.download =
        qrValue + ".png";

    link.href =
        canvas.toDataURL("image/png");

    link.click();
}


function printQR()
{
    const canvas =
        document.querySelector("#qrcode canvas");

    if (!canvas) {
        alert("QR code is not ready yet.");
        return;
    }

    const image =
        canvas.toDataURL("image/png");

    const printWindow =
        window.open("", "_blank");

    printWindow.document.write(`
        <html>
        <head>
            <title>${qrValue}</title>

            <style>
                body {
                    text-align: center;
                    font-family: Arial, sans-serif;
                    padding-top: 40px;
                }

                img {
                    width: 300px;
                    height: 300px;
                }

                h2 {
                    margin-bottom: 10px;
                }
            </style>
        </head>

        <body>

            <h2>${qrValue}</h2>

            <img src="${image}">

            <script>
                window.onload = function() {
                    window.print();
                };
            <\/script>

        </body>
        </html>
    `);

    printWindow.document.close();
}

</script>

<?php endif; ?>


<?php if ($error !== ''): ?>

<div class="error">

<i class="fas fa-circle-exclamation"></i>

<?php
echo htmlspecialchars($error);
?>

</div>

<?php endif; ?>


<form
method="POST"
action="">


<div class="form-grid">


<!-- PRODUCT NAME -->

<div class="group">

<label>
Product Name
</label>

<input
type="text"
name="name"
placeholder="Enter product name"
value="<?php
echo htmlspecialchars($name ?? '');
?>"
required>

</div>


<!-- CATEGORY -->

<div class="group">

<label>
Category
</label>

<input
type="text"
name="category"
placeholder="e.g. Tablet, Capsule, Syrup"
value="<?php
echo htmlspecialchars($category ?? '');
?>"
required>

</div>


<!-- DESCRIPTION -->

<div class="group full">

<label>
Description
</label>

<textarea
name="description"
placeholder="Enter product description"><?php

echo htmlspecialchars(
    $description ?? ''
);

?></textarea>

</div>


<!-- COST PRICE -->

<div class="group">

<label>
Cost Price
</label>

<input
type="number"
name="cost_price"
step="0.01"
min="0"
placeholder="0.00"
value="<?php
echo htmlspecialchars(
    $cost_price ?? ''
);
?>"
required>

</div>


<!-- SELLING PRICE -->

<div class="group">

<label>
Selling Price
</label>

<input
type="number"
name="price"
step="0.01"
min="0"
placeholder="0.00"
value="<?php
echo htmlspecialchars(
    $price ?? ''
);
?>"
required>

</div>


<!-- STOCK -->

<div class="group">

<label>
Initial Stock
</label>

<input
type="number"
name="stock"
min="0"
placeholder="0"
value="<?php
echo htmlspecialchars(
    $stock ?? ''
);
?>"
required>

</div>


<!-- EXPIRATION DATE -->

<div class="group">

<label>
Expiration Date
</label>

<input
type="date"
name="expiry_date"
value="<?php
echo htmlspecialchars(
    $expiry_date ?? ''
);
?>"
required>

</div>


</div>


<div class="buttons">


<a
href="products.php"
class="cancel">

Cancel

</a>


<button
type="submit"
class="save">

<i class="fas fa-plus"></i>

Add Product

</button>


</div>


</form>


</div>


</div>


</body>

</html>