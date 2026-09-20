<?php
require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
|--------------------------------------------------------------------------
| Initialize Cart
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['pos_cart'])) {
    $_SESSION['pos_cart'] = [];
}

$message = "";
$error = "";

/*
|--------------------------------------------------------------------------
| Get Logged-in User
|--------------------------------------------------------------------------
*/

$cashierId = $_SESSION['id'] ?? null;


/*
|--------------------------------------------------------------------------
| Remove Item From Cart
|--------------------------------------------------------------------------
*/

if (isset($_POST['remove_item'])) {

    $medicineId = intval($_POST['medicine_id']);

    if (isset($_SESSION['pos_cart'][$medicineId])) {
        unset($_SESSION['pos_cart'][$medicineId]);
        $message = "Item removed from cart.";
    }
}


/*
|--------------------------------------------------------------------------
| Clear Cart
|--------------------------------------------------------------------------
*/

if (isset($_POST['clear_cart'])) {

    $_SESSION['pos_cart'] = [];

    $message = "Cart cleared.";
}


/*
|--------------------------------------------------------------------------
| Add Product To Cart
|--------------------------------------------------------------------------
*/

if (isset($_POST['add_to_cart'])) {

    $medicineId = intval($_POST['medicine_id']);
    $quantity = intval($_POST['quantity']);

    if ($medicineId <= 0) {

        $error = "Please select a product.";

    } elseif ($quantity <= 0) {

        $error = "Quantity must be at least 1.";

    } else {

        $stmt = $conn->prepare("
            SELECT
                medicine_id,
                name,
                category,
                price,
                stock
            FROM medicines
            WHERE medicine_id = ?
        ");

        $stmt->bind_param("i", $medicineId);
        $stmt->execute();

        $result = $stmt->get_result();
        $medicine = $result->fetch_assoc();

        $stmt->close();

        if (!$medicine) {

            $error = "Product not found.";

        } elseif ((int)$medicine['stock'] <= 0) {

            $error = "This product is out of stock.";

        } else {

            /*
             * If item already exists in cart,
             * add the new quantity.
             */

            $existingQuantity = $_SESSION['pos_cart'][$medicineId]['quantity'] ?? 0;

            $newQuantity = $existingQuantity + $quantity;

            if ($newQuantity > (int)$medicine['stock']) {

                $error = "Not enough stock available. Current stock: "
                    . $medicine['stock'];

            } else {

                $_SESSION['pos_cart'][$medicineId] = [
                    'medicine_id' => (int)$medicine['medicine_id'],
                    'name'        => $medicine['name'],
                    'category'    => $medicine['category'],
                    'price'       => (float)$medicine['price'],
                    'quantity'    => $newQuantity
                ];

                $message = $medicine['name'] . " added to cart.";
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| Complete Sale
|--------------------------------------------------------------------------
*/

if (isset($_POST['complete_sale'])) {

    $paymentMethod = $_POST['payment_method'] ?? 'Cash';
    $amountTendered = (float)($_POST['amount_tendered'] ?? 0);

    if (empty($_SESSION['pos_cart'])) {

        $error = "Your cart is empty.";
    } 

    elseif (!$cashierId) {
        $error = "You must be logged in before completing a sale.";
    }

    elseif (!in_array($paymentMethod, ['Cash', 'GCash'], true)) {

        $error = "Invalid payment method.";
    } 
    
    else {
        $cartValid = true;
        $totalAmount = 0;
        $totalQuantity = 0;

        foreach ($_SESSION['pos_cart'] as $item) {
            $stmt = $conn->prepare("
                SELECT
                    medicine_id,
                    name,
                    price,
                    stock
                FROM medicines
                WHERE medicine_id = ?
                FOR UPDATE
            ");
            $stmt->close();
        }

        $conn->begin_transaction();
        try {
            foreach ($_SESSION['pos_cart'] as $medicineId => &$item) {
                $stmt = $conn->prepare("
                    SELECT
                        medicine_id,
                        name,
                        price,
                        stock
                    FROM medicines
                    WHERE medicine_id = ?
                    FOR UPDATE
                ");

                $stmt->bind_param("i", $medicineId);
                $stmt->execute();

                $result = $stmt->get_result();
                $dbMedicine = $result->fetch_assoc();

                $stmt->close();

                if (!$dbMedicine) {
                    throw new Exception(
                        "Product ID " . $medicineId . " no longer exists."
                    );
                }

                $requestedQuantity = (int)$item['quantity'];
                $currentStock = (int)$dbMedicine['stock'];

                if ($requestedQuantity > $currentStock) {
                    throw new Exception(
                        $dbMedicine['name'] .
                        " does not have enough stock. Available: " .
                        $currentStock
                    );
                }

                $item['price'] = (float)$dbMedicine['price'];

                $subtotal = $item['price'] * $requestedQuantity;

                $totalAmount += $subtotal;
                $totalQuantity += $requestedQuantity;
            }

            unset($item);

            if ($paymentMethod === 'Cash') {
                if ($amountTendered < $totalAmount) {
                    throw new Exception(
                        "Insufficient payment. Amount due: ₱" .
                        number_format($totalAmount, 2)
                    );
                }
            } 

            else {
                if ($amountTendered < $totalAmount) {

                    throw new Exception(
                        "GCash payment must be at least ₱" .
                        number_format($totalAmount, 2)
                    );
                }
            }

            $changeAmount = $amountTendered - $totalAmount;
            $stmt = $conn->prepare("
                INSERT INTO sales
                (
                    cashier_id,
                    sale_date,
                    total_amount,
                    payment_method,
                    amount_paid,
                    change_amount
                )
                VALUES
                (
                    ?,
                    NOW(),
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            $stmt->bind_param(
                "idsdd",
                $cashierId,
                $totalAmount,
                $paymentMethod,
                $amountTendered,
                $changeAmount
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create sale.");
            }

            $saleId = $conn->insert_id;

            $stmt->close();

            foreach ($_SESSION['pos_cart'] as $medicineId => $item) {
                $quantity = (int)$item['quantity'];
                $unitPrice = (float)$item['price'];
                $subtotal = $unitPrice * $quantity;

                $stmt = $conn->prepare("
                    INSERT INTO sale_items
                    (
                        sale_id,
                        medicine_id,
                        quantity,
                        unit_price,
                        subtotal
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        ?
                    )
                ");

                $stmt->bind_param(
                    "iiidd",
                    $saleId,
                    $medicineId,
                    $quantity,
                    $unitPrice,
                    $subtotal
                );

                if (!$stmt->execute()) {
                    throw new Exception("Failed to save sale item.");
                }

                $stmt->close();

                $stmt = $conn->prepare("
                    UPDATE medicines
                    SET stock = stock - ?
                    WHERE medicine_id = ?
                    AND stock >= ?
                ");

                $stmt->bind_param(
                    "iii",
                    $quantity,
                    $medicineId,
                    $quantity
                );

                if (!$stmt->execute() || $stmt->affected_rows !== 1) {
                    throw new Exception(
                        "Failed to update stock for medicine ID " .
                        $medicineId
                    );
                }
                $stmt->close();
            }

            $reportDate = date("Y-m-d");

            $stmt = $conn->prepare("
                INSERT INTO sales_report
                (
                    sale_id,
                    report_date,
                    total_sales,
                    total_items
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            $stmt->bind_param(
                "isdi",
                $saleId,
                $reportDate,
                $totalAmount,
                $totalQuantity
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create sales report.");
            }

            $stmt->close();

            $conn->commit();

            $_SESSION['pos_cart'] = [];

            $message =
                "Sale completed successfully! " .
                "Sale ID: #" . $saleId .
                " | Total: ₱" . number_format($totalAmount, 2) .
                " | Change: ₱" . number_format($changeAmount, 2);

        } catch (Exception $e) {

            $conn->rollback();

            $error = $e->getMessage();
        }
    }
}

/*--------------------------------------------------------------------------
| Selected Product
--------------------------------------------------------------------------*/
$selectedProduct = null;

if (isset($_GET['product_id'])) {
    $productId = intval($_GET['product_id']);

    if ($productId > 0) {
        $stmt = $conn->prepare("
            SELECT
                medicine_id,
                name,
                category,
                price,
                stock,
                expiry_date
            FROM medicines
            WHERE medicine_id = ?
        ");

        $stmt->bind_param("i", $productId);
        $stmt->execute();

        $result = $stmt->get_result();
        $selectedProduct = $result->fetch_assoc();

        $stmt->close();
    }
}


/*--------------------------------------------------------------------------
| Product Search
--------------------------------------------------------------------------*/
$search = trim($_GET['search'] ?? '');

$searchResults = [];

if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT
            medicine_id,
            name,
            category,
            price,
            stock
        FROM medicines
        WHERE name LIKE ?
        OR category LIKE ?
        ORDER BY name ASC
        LIMIT 20
    ");

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "ss",
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
    $stmt->close();
}

else {
    $result = $conn->query("
        SELECT
            medicine_id,
            name,
            category,
            price,
            stock
        FROM medicines
        ORDER BY name ASC
    ");

    while ($row = $result->fetch_assoc()) {
        $searchResults[] = $row;
    }
}

/*--------------------------------------------------------------------------
| Cart Totals
--------------------------------------------------------------------------*/
$totalItems = count($_SESSION['pos_cart']);
$totalQuantity = 0;
$totalAmount = 0;

foreach ($_SESSION['pos_cart'] as $item) {

    $totalQuantity += (int)$item['quantity'];

    $totalAmount +=
        (float)$item['price'] *
        (int)$item['quantity'];
}
?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Point of Sale | ValueMeds</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


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
margin-bottom:30px;
}

.header h1{
color:#16246D;
}

.admin{
background:white;
padding:10px 20px;
border-radius:30px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

/* Messages */

.message{
background:#dff5e5;
color:#176b2c;
padding:15px;
border-radius:10px;
margin-bottom:20px;
}

.error{
background:#ffe1e1;
color:#a40000;
padding:15px;
border-radius:10px;
margin-bottom:20px;
}

/* POS */

.pos-top{
display:grid;
grid-template-columns:1fr 2fr;
gap:20px;
}

.container{
background:white;
padding:25px;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.section-title{
font-size:18px;
font-weight:bold;
color:#16246D;
margin-bottom:20px;
}

/* QR */

.scan-box{
height:180px;
border:2px dashed #16246D;
border-radius:15px;
display:flex;
justify-content:center;
align-items:center;
color:#16246D;
margin-bottom:15px;
}

.search-form{
display:flex;
gap:10px;
}

.search{
width:100%;
padding:12px;
border:1px solid #ccc;
border-radius:10px;
outline:none;
}

.search-btn{
border:none;
background:#16246D;
color:white;
padding:0 18px;
border-radius:10px;
cursor:pointer;
}

/* Search results */

.search-results{
margin-top:15px;
max-height:300px;
overflow-y:auto;
}

.product-result{
display:flex;
justify-content:space-between;
align-items:center;
padding:12px;
border-bottom:1px solid #eee;
}

.product-result-info strong{
display:block;
color:#16246D;
}

.product-result-info small{
color:#777;
}

.select-btn{
background:#16246D;
color:white;
text-decoration:none;
padding:8px 12px;
border-radius:8px;
font-size:13px;
}

.select-btn:hover{
background:#2b45b5;
}

.out-stock{
color:#c00;
}

.low-stock{
color:#d88700;
}

/* Product */

.product-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

.group{
display:flex;
flex-direction:column;
}

.group label{
margin-bottom:8px;
font-weight:600;
color:#16246D;
}

.group input,
.group select{
padding:12px;
border:1px solid #ccc;
border-radius:10px;
outline:none;
}

.buttons{
display:flex;
justify-content:flex-end;
gap:15px;
margin-top:20px;
}

.cancel{
background:#ccc;
border:none;
padding:12px 25px;
border-radius:10px;
cursor:pointer;
}

.confirm{
background:#16246D;
color:white;
border:none;
padding:12px 25px;
border-radius:10px;
cursor:pointer;
}

.confirm:hover{
background:#2b45b5;
}

.confirm:disabled{
background:#aaa;
cursor:not-allowed;
}

/* Bottom */

.pos-bottom{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
margin-top:20px;
}

/* Cart */

.cart-table{
width:100%;
border-collapse:collapse;
}

.cart-table th{
background:#eef4ff;
color:#16246D;
padding:12px;
text-align:left;
}

.cart-table td{
padding:12px;
border-bottom:1px solid #eee;
}

.remove{
border:none;
background:#eee;
color:#16246D;
padding:7px 10px;
border-radius:7px;
cursor:pointer;
}

.remove:hover{
background:#ddd;
}

/* Payment */

.summary-row{
display:flex;
justify-content:space-between;
padding:12px 0;
border-bottom:1px solid #eee;
}

.total{
font-size:20px;
font-weight:bold;
color:#16246D;
}

.checkout{
width:100%;
margin-top:20px;
padding:14px;
border:none;
border-radius:10px;
background:#16246D;
color:white;
font-size:16px;
font-weight:bold;
cursor:pointer;
}

.checkout:hover{
background:#2b45b5;
}

.checkout:disabled{
background:#aaa;
cursor:not-allowed;
}

.clear-cart{
width:100%;
margin-top:10px;
padding:10px;
border:none;
border-radius:10px;
background:#eee;
color:#16246D;
cursor:pointer;
}

/* Empty */

.empty{
text-align:center;
padding:30px;
color:#777;
}

/* Responsive */

@media(max-width:1100px){

.pos-top,
.pos-bottom{
grid-template-columns:1fr;
}

}

@media(max-width:900px){

.sidebar{
width:200px;
}

.main{
margin-left:200px;
width:calc(100% - 200px);
}

.product-grid{
grid-template-columns:1fr;
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

.header{
margin-bottom:20px;
}

.header h1{
font-size:24px;
}

.container{
padding:18px;
}

.pos-top,
.pos-bottom{
grid-template-columns:1fr;
}

.product-result{
gap:10px;
}

.cart-table{
font-size:13px;
}

.cart-table th,
.cart-table td{
padding:8px;
}

}

</style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->
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

    <a href="pos.php" class="active">
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

<h1>Point of Sale</h1>

<div class="admin">

<i class="fas fa-user"></i>

<?php echo htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?>

</div>

</div>


<!-- Messages -->

<?php if ($message): ?>

<div class="message">
<i class="fas fa-circle-check"></i>
<?php echo htmlspecialchars($message); ?>
</div>

<?php endif; ?>


<?php if ($error): ?>

<div class="error">
<i class="fas fa-circle-exclamation"></i>
<?php echo htmlspecialchars($error); ?>
</div>

<?php endif; ?>


<!-- =========================================================
     TOP
========================================================= -->

<div class="pos-top">


<!-- =====================================================
     SEARCH / QR
===================================================== -->

<div class="container">

<div class="section-title">
Scan Product QR Code / Barcode
</div>


<div class="scan-box">

<i class="fas fa-qrcode fa-3x"></i>

</div>


<form method="GET"
class="search-form">

<input
class="search"
type="text"
name="search"
value="<?php echo htmlspecialchars($search); ?>"
placeholder="🔍 Search Product">

<button
type="submit"
class="search-btn">

<i class="fas fa-search"></i>

</button>

</form>


<!-- Search Results -->

<?php if (!empty($searchResults)): ?>

<div class="search-results">

<?php foreach ($searchResults as $product): ?>

<div class="product-result">

<div class="product-result-info">

<strong>
<?php echo htmlspecialchars($product['name']); ?>
</strong>

<small>

<?php
echo htmlspecialchars(
    $product['category'] ?? 'Uncategorized'
);
?>

|

₱<?php echo number_format($product['price'], 2); ?>

|

<?php if ($product['stock'] == 0): ?>

<span class="out-stock">
Out of Stock
</span>

<?php elseif ($product['stock'] <= 20): ?>

<span class="low-stock">
Stock: <?php echo $product['stock']; ?>
</span>

<?php else: ?>

Stock: <?php echo $product['stock']; ?>

<?php endif; ?>

</small>

</div>


<?php if ((int)$product['stock'] > 0): ?>

<a
class="select-btn"
href="pos.php?search=<?php echo urlencode($search); ?>&product_id=<?php echo $product['medicine_id']; ?>">

Select

</a>

<?php else: ?>

<span class="out-stock">
Unavailable
</span>

<?php endif; ?>

</div>

<?php endforeach; ?>

</div>

<?php elseif ($search !== ''): ?>

<div class="empty">
No products found.
</div>

<?php endif; ?>

</div>


<!-- =====================================================
     PRODUCT INFORMATION
===================================================== -->
<div class="container">
    <div class="section-title">
        Product Information
    </div>

<form method="POST">
    <input
        type="hidden"
        name="medicine_id"
        value="<?php echo $selectedProduct['medicine_id'] ?? '';
    ?>">

    <div class="product-grid">
        <div class="group">
            <label>Product Name</label>

            <input
                type="text"
                value="
                <?php
                    echo htmlspecialchars(
                    $selectedProduct['name'] ?? ''
                    );
                ?>"
                placeholder="Select a product"
                readonly>
    </div>

    <div class="group">
        <label>Category</label>

            <input
                type="text"
                value="
                <?php
                    echo htmlspecialchars(
                    $selectedProduct['category'] ?? ''
                    );
                ?>"
                placeholder="Category"
                readonly>
    </div>


    <div class="group">
        <label>Current Stock</label>

            <input
                type="text"
                value="<?php
                    echo $selectedProduct['stock'] ?? '';
                ?>"
                placeholder="Current Stock"
                readonly>
    </div>

    <div class="group">
        <label>Quantity</label>

            <input
                type="number"
                name="quantity"
                value="1"
                min="1"
                max="
                <?php 
                    echo $selectedProduct['stock'] ?? 1; 
                 ?>"
            <?php
                if (!$selectedProduct ||
                    (int)$selectedProduct['stock'] <= 0) {
                    echo 'disabled';
                }
            ?>>
    </div>
</div>

<div class="buttons">
<a
href="pos.php"
class="cancel"
style="text-decoration:none;">

Cancel

</a>

<button
type="submit"
name="add_to_cart"
class="confirm"
<?php
if (!$selectedProduct ||
    (int)$selectedProduct['stock'] <= 0) {
    echo 'disabled';
}
?>>

<i class="fas fa-cart-plus"></i>
Add to Cart
</button>
</div>
</form>
</div>
</div>

<!-- =========================================================
     BOTTOM
========================================================= -->
<div class="pos-bottom">

<!-- =====================================================
     CART
===================================================== -->
<div class="container">
    <div class="section-title">
        Cart Items
    </div>

<?php
    if (empty($_SESSION['pos_cart'])): 
?>

<div class="empty">
    <i class="fas fa-cart-shopping fa-2x"></i>
        <br><br>
        Your cart is empty.
</div>

<?php 
    else: 
?>

<div style="overflow-x:auto;">
    <table class="cart-table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>

        <?php 
           foreach ($_SESSION['pos_cart'] as $item): 
        ?>

        <tr>
        <td>
        <strong>

        <?php
           echo htmlspecialchars($item['name']);
        ?>

        </strong>
        <br>
        <small>
            <?php
                echo htmlspecialchars(
                    $item['category'] ?? ''
                );
            ?>
        </small>
        </td>

        <td>
            <?php 
                echo $item['quantity']; 
            ?>
        </td>

        <td>
            ₱<?php
                echo number_format(
                    $item['price'],
                    2
                );
            ?>
        </td>

        <td>
            ₱<?php
                $subtotal =
                    $item['price'] *
                    $item['quantity'];
                echo number_format(
                    $subtotal,
                    2
                );
            ?>
        </td>

        <td>
            <form method="POST">

            <input
                type="hidden"
                name="medicine_id"
                value="<?php echo $item['medicine_id']; ?>">

                <button
                    type="submit"
                    name="remove_item"
                    class="remove"
                    title="Remove item">
                    <i class="fas fa-trash"></i>
                </button>

            </form>

        </td>


        </tr>

        <?php endforeach; ?>


        </tbody>

    </table>

</div>


<form method="POST">

<button
type="submit"
name="clear_cart"
class="clear-cart">

<i class="fas fa-trash"></i>

Clear Cart

</button>

</form>


<?php endif; ?>

</div>


<!-- =====================================================
     PAYMENT
===================================================== -->

<div class="container">

<div class="section-title">
Payment Summary
</div>


<div class="summary-row">

<span>Total Items</span>

<b>
<?php echo $totalItems; ?>
</b>

</div>


<div class="summary-row">

<span>Total Quantity</span>

<b>
<?php echo $totalQuantity; ?>
</b>

</div>


<div class="summary-row">

<span>Total Amount</span>

<b>
₱<?php echo number_format($totalAmount, 2); ?>
</b>

</div>


<form method="POST">


<div
class="group"
style="margin-top:15px;">

<label>
Payment Method
</label>


<select
name="payment_method"
id="payment_method"
onchange="updatePaymentLabel()">

<option value="Cash">
Cash
</option>

<option value="GCash">
GCash
</option>

</select>

</div>


<div
class="group"
style="margin-top:15px;">

<label id="amountLabel">
Amount Tendered
</label>


<input
type="number"
name="amount_tendered"
id="amount_tendered"
step="0.01"
min="0"
placeholder="Enter amount"
required>

</div>


<div class="summary-row total">

<span>
Change
</span>

<span id="changeAmount">
₱0.00
</span>

</div>


<button
type="submit"
name="complete_sale"
class="checkout"
<?php
if (empty($_SESSION['pos_cart'])) {
    echo 'disabled';
}
?>>

<i class="fas fa-cash-register"></i>

Complete Sale

</button>


</form>


</div>

</div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| Calculate Change
|--------------------------------------------------------------------------
*/

const totalAmount =
    <?php echo json_encode($totalAmount); ?>;

const paymentInput =
    document.getElementById("amount_tendered");

const changeDisplay =
    document.getElementById("changeAmount");


function updateChange(){

    const amount =
        parseFloat(paymentInput.value) || 0;

    const change =
        amount - totalAmount;

    if(change > 0){

        changeDisplay.textContent =
            "₱" + change.toFixed(2);

    }else{

        changeDisplay.textContent =
            "₱0.00";
    }
}


paymentInput.addEventListener(
    "input",
    updateChange
);


/*
|--------------------------------------------------------------------------
| Payment Method Label
|--------------------------------------------------------------------------
*/

function updatePaymentLabel(){

    const method =
        document.getElementById(
            "payment_method"
        ).value;

    const label =
        document.getElementById(
            "amountLabel"
        );

    if(method === "GCash"){

        label.textContent =
            "GCash Amount";

    }else{

        label.textContent =
            "Amount Tendered";
    }
}

</script>


</body>

</html>