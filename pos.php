<?php

require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['pos_cart'])) {
    $_SESSION['pos_cart'] = [];
}

$message = "";
$error = "";

$discountType = "None";
$discountPercentage = 0;
$discountAmount = 0;

/*
|--------------------------------------------------------------------------
| Get Logged-in User
|--------------------------------------------------------------------------
*/

$cashierId = $_SESSION['id'] ?? null;
$cashierName = $_SESSION['fullname'] ?? 'Cashier';

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
| Apply Cart Quantity Changes
|--------------------------------------------------------------------------
*/

if (isset($_POST['apply_cart_quantities'])) {

    $cartQuantities =
        json_decode(
            $_POST['cart_quantities'] ?? '{}',
            true
        );

    if (is_array($cartQuantities)) {

        foreach ($cartQuantities as $medicineId => $quantity) {

            $medicineId = (int)$medicineId;
            $quantity = (int)$quantity;

            if (!isset($_SESSION['pos_cart'][$medicineId])) {
                continue;
            }

            if ($quantity < 1) {
                $quantity = 1;
            }

            /*
             * Check latest stock.
             */

            $stmt = $conn->prepare("
                SELECT stock
                FROM medicines
                WHERE medicine_id = ?
            ");

            $stmt->bind_param("i", $medicineId);
            $stmt->execute();

            $result = $stmt->get_result();
            $medicine = $result->fetch_assoc();

            $stmt->close();

            if (!$medicine) {

                unset($_SESSION['pos_cart'][$medicineId]);

                continue;
            }

            $stock = (int)$medicine['stock'];

            if ($stock <= 0) {

                unset($_SESSION['pos_cart'][$medicineId]);

                continue;
            }

            if ($quantity > $stock) {

                $quantity = $stock;

            }

            $_SESSION['pos_cart'][$medicineId]['quantity'] =
                $quantity;
        }
    }

    /*
     * Redirect after POST so refresh does not
     * repeat the quantity update.
     */

    header("Location: pos.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Scan Product QR Code
|--------------------------------------------------------------------------
*/
if (isset($_POST['scan_qr'])) {

    $qrValue = trim($_POST['qr_value'] ?? '');

    if ($qrValue === '') {

        $error = "Please scan or enter a QR code.";

    } elseif (!preg_match('/^VM-MED-(\d+)$/', $qrValue, $matches)) {

        $error = "Invalid ValueMeds QR code.";

    } else {

        $medicineId = (int)$matches[1];

        $stmt = $conn->prepare("
            SELECT
                medicine_id,
                name,
                description,
                category,
                price,
                stock,
                expiry_date
            FROM medicines
            WHERE medicine_id = ?
        ");

        $stmt->bind_param("i", $medicineId);
        $stmt->execute();

        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        $stmt->close();

        if (!$product) {

            $error = "Product not found.";

        } elseif ((int)$product['stock'] <= 0) {

            $error = "This product is out of stock.";

        } else {

            $id = (int)$product['medicine_id'];

            $existingQuantity =
                $_SESSION['pos_cart'][$id]['quantity'] ?? 0;

            $newQuantity = $existingQuantity + 1;

            if ($newQuantity > (int)$product['stock']) {

                $error =
                    "Not enough stock available. Current stock: " .
                    $product['stock'];

            } else {

                $_SESSION['pos_cart'][$id] = [
                    'medicine_id' => $id,
                    'name'        => $product['name'],
                    'category'    => $product['category'],
                    'price'       => (float)$product['price'],
                    'quantity'    => $newQuantity,
                    'stock'       => (int)$product['stock']
                ];

                header(
                    "Location: pos.php?product_id=" .
                    $id .
                    "&scanned=1"
                );
                exit;
            }
        }
    }
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
                    'quantity'    => $newQuantity,
                    'stock'       => (int)$medicine['stock']
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

    $amountTendered =
    (float)($_POST['amount_tendered'] ?? 0);


    $discountType =
    $_POST['discount_type'] ?? 'None';


    $customerName =
    trim($_POST['customer_name'] ?? '');


    $customerIdNumber =
    trim($_POST['customer_id_number'] ?? '');

    if (empty($_SESSION['pos_cart'])) {

        $error = "Cart is empty.";

    } elseif (!$cashierId) {

        $error = "No cashier is logged in.";

    } elseif ($paymentMethod !== 'Cash' && $paymentMethod !== 'GCash') {

        $error = "Invalid payment method.";

    } else {

        /*
         * Save cart items for the receipt
         * before clearing the cart later.
         */

        $receiptItems = $_SESSION['pos_cart'];

        $totalAmount = 0;
        $totalQuantity = 0;

        $conn->begin_transaction();

        try {

            /*
             * Verify stock and get the latest
             * prices from the database.
             */

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
                        "Product ID " . $medicineId .
                        " no longer exists."
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

                $subtotal =
                    $item['price'] * $requestedQuantity;

                $totalAmount += $subtotal;
                $totalQuantity += $requestedQuantity;
            }

            unset($item);
            if(
                $discountType === "Senior Citizen"
                ||
                $discountType === "PWD"
            )
            {

                $discountPercentage = 20;

                $discountAmount =
                    $totalAmount * 0.20;

            }


            $totalAmount =
            $totalAmount - $discountAmount;

            /*
             * Validate payment AFTER the real
             * cart total has been calculated.
             */

            if ($amountTendered < $totalAmount) {

                if ($paymentMethod === 'GCash') {

                    throw new Exception(
                        "GCash payment must be at least PHP " .
                        number_format($totalAmount, 2)
                    );

                } else {

                    throw new Exception(
                        "Insufficient payment. Amount due: PHP " .
                        number_format($totalAmount, 2)
                    );
                }
            }


            $changeAmount =
                $amountTendered - $totalAmount;


            /*
             * Create Sale
             */

            $stmt = $conn->prepare("
                INSERT INTO sales
                    (
                    cashier_id,
                    total_amount,
                    discount_type,
                    discount_percentage,
                    discount_amount,
                    customer_name,
                    customer_id_number,
                    payment_method,
                    amount_paid,
                    change_amount
                    )
                VALUES
               (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
                )
            ");

            $stmt->bind_param(
                "idsddsssdd",
                $cashierId,
                $totalAmount,
                $discountType,
                $discountPercentage,
                $discountAmount,
                $customerName,
                $customerIdNumber,
                $paymentMethod,
                $amountTendered,
                $changeAmount
            );

            if (!$stmt->execute()) {

                throw new Exception(
                    "Failed to create sale."
                );
            }

            $saleId = $conn->insert_id;

            $stmt->close();


            /*
             * Save Sale Items + Update Stock
             */

            foreach ($_SESSION['pos_cart'] as $medicineId => $item) {

                $quantity = (int)$item['quantity'];
                $unitPrice = (float)$item['price'];

                $subtotal =
                    $unitPrice * $quantity;


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

                    throw new Exception(
                        "Failed to save sale item."
                    );
                }

                $stmt->close();


                /*
                 * Deduct Stock
                 */

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

                if (
                    !$stmt->execute() ||
                    $stmt->affected_rows !== 1
                ) {

                    throw new Exception(
                        "Failed to update stock for medicine ID " .
                        $medicineId
                    );
                }

                $stmt->close();
            }


            /*
             * Create Sales Report
             */

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

                throw new Exception(
                    "Failed to create sales report."
                );
            }

            $stmt->close();


            /*
             * Complete Transaction
             */

            $conn->commit();

            $_SESSION['pos_cart'] = [];


            /*
             * Save Receipt Information
             */

            $_SESSION['last_sale'] = [

                'sale_id' => $saleId,

                'subtotal' => $totalAmount + $discountAmount,

                'discount_type' => $discountType,

                'discount_amount' => $discountAmount,

                'total' => $totalAmount,

                'payment' => $amountTendered,

                'change' => $changeAmount,

                'payment_method' => $paymentMethod,

                'items' => $receiptItems

            ];
            header("Location: pos.php?sale_complete=1");
            exit;
        } 
        
        catch (Exception $e) {

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
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.3.0/qz-tray.js"></script>

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
.qr-scanner-content{
width:100%;
text-align:center;
padding:15px;
}

.qr-scanner-content > i{
margin-bottom:10px;
}

.qr-scanner-content p{
font-weight:600;
margin-bottom:12px;
}

.qr-form{
display:flex;
gap:8px;
max-width:450px;
margin:auto;
}

.qr-input{
flex:1;
padding:11px;
border:1px solid #ccc;
border-radius:8px;
outline:none;
font-size:14px;
}

.qr-input:focus{
border-color:#16246D;
box-shadow:0 0 0 2px rgba(22,36,109,.1);
}

.qr-scan-button{
border:none;
background:#16246D;
color:white;
padding:0 16px;
border-radius:8px;
cursor:pointer;
font-weight:600;
}

.qr-scan-button:hover{
background:#2b45b5;
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

.quantity-btn{
    width:30px;
    height:30px;
    border:none;
    background:#eef4ff;
    color:#16246D;
    border-radius:7px;
    cursor:pointer;
    font-size:18px;
    font-weight:bold;
    line-height:1;
}

.quantity-btn:hover{
background:#dce8ff;
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
.qr-form{
flex-direction:column;
}

.qr-scan-button{
    padding:11px;
}
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


.receipt-modal{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.55);
    display:flex;
    align-items:center;
    justify-content:center;
    z-index:9999;
    padding:20px;
}

.receipt-box{
    background:white;
    width:100%;
    max-width:420px;
    border-radius:14px;
    padding:25px;
    box-shadow:0 10px 40px rgba(0,0,0,.25);
}

.receipt-header{
    text-align:center;
    border-bottom:1px solid #ddd;
    padding-bottom:18px;
}

.receipt-header i{
    font-size:45px;
    color:#16246D;
    margin-bottom:10px;
}

.receipt-header h2{
    margin:0 0 5px;
}

.receipt-header p{
    margin:0;
    color:#777;
}

.receipt-details{
    padding:20px 0;
}

.receipt-details > div{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #eee;
}

.receipt-details span{
    color:#666;
}

.receipt-details strong{
    color:#16246D;
}

.receipt-change strong{
    font-size:20px;
}

.receipt-actions{
    display:flex;
    gap:10px;
}

.receipt-print-button,
.receipt-close-button{
    flex:1;
    border:none;
    padding:12px;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
}

.receipt-print-button{
    background:#16246D;
    color:white;
}

.receipt-close-button{
    background:#eee;
    color:#333;
}

.receipt-print-button:hover{
    background:#2b45b5;
}

.receipt-close-button:hover{
    background:#ddd;
}

.receipt-items{
    border-bottom:1px solid #ddd;
    padding-bottom:15px;
}

.receipt-item{
    padding:8px 0;
}

.receipt-item-name{
    font-weight:600;
    margin-bottom:4px;
}

.receipt-item-info{
    display:flex;
    justify-content:space-between;
    color:#666;
    font-size:14px;
}

.receipt-item-info strong{
    color:#16246D;
}
.product-quantity-controls{
    display:flex;
    align-items:center;
    gap:8px;
}

.product-quantity-controls input{
    width:80px;
    text-align:center;
}

.product-quantity-btn{
    width:38px;
    height:38px;
    border:none;
    border-radius:8px;
    background:#eef4ff;
    color:#16246D;
    cursor:pointer;
    font-size:20px;
    font-weight:bold;
    line-height:1;
}

.product-quantity-btn:hover{
    background:#dce8ff;
}

.cart-quantity-input{
    width:55px;
    height:30px;
    border:1px solid #ccc;
    border-radius:7px;
    text-align:center;
    font-weight:bold;
    color:#16246D;
}
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
    appearance: textfield;
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
    <a href="refund.php">
        <i class="fas fa-chart-recycle"></i>
        refund
    </a>
    <a href="exchanges.php">
        <i class="fa-solid fa-arrow-right-arrow-left"></i>
        Item Exchange
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

    <div class="qr-scanner-content">

        <i class="fas fa-qrcode fa-3x"></i>

        <p>Scan Product QR Code</p>

        <form method="POST" class="qr-form">

            <input
                type="text"
                name="qr_value"
                id="qr_value"
                class="qr-input"
                placeholder="Scan or enter VM-MED-11"
                autocomplete="off"
                autofocus
            >

            <button
                type="submit"
                name="scan_qr"
                class="qr-scan-button">

                <i class="fas fa-qrcode"></i>
                Scan

            </button>

        </form>

    </div>

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

PHP <?php echo number_format($product['price'], 2); ?>

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

    <div class="product-quantity-controls">

        <button
            type="button"
            class="product-quantity-btn"
            onclick="changeProductQuantity(-1)">
            −
        </button>

        <input
            type="number"
            name="quantity"
            id="productQuantity"
            value="1"
            min="1"
            max="<?php
                echo $selectedProduct['stock'] ?? 1;
            ?>"
            <?php
            if (
                !$selectedProduct ||
                (int)$selectedProduct['stock'] <= 0
            ) {
                echo 'disabled';
            }
            ?>
        >

        <button
            type="button"
            class="product-quantity-btn"
            onclick="changeProductQuantity(1)">
            +
        </button>

        </div>

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

    <div class="quantity-controls">

        <button
            type="button"
            class="quantity-btn"
            onclick="changeCartQuantity(
                <?php echo (int)$item['medicine_id']; ?>,
                -1
            )">

            −

        </button>


        <input
            type="number"
            class="cart-quantity-input"
            id="cart_quantity_<?php echo (int)$item['medicine_id']; ?>"
            value="<?php echo (int)$item['quantity']; ?>"
            min="1"
            max="<?php echo (int)($item['stock'] ?? 999999); ?>"
            data-medicine-id="<?php
                echo (int)$item['medicine_id'];
            ?>"
        >


        <button
            type="button"
            class="quantity-btn"
            onclick="changeCartQuantity(
                <?php echo (int)$item['medicine_id']; ?>,
                1
            )">

            +

        </button>

    </div>

</td>

        <td>
            PHP <?php
                echo number_format(
                    $item['price'],
                    2
                );
            ?>
        </td>

        <td>
            PHP <?php
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


<?php 
endif; 
?>


<form method="POST" id="cartQuantityForm">

    <input
        type="hidden"
        name="cart_quantities"
        id="cartQuantities"
    >

    <button
        type="submit"
        name="apply_cart_quantities"
        class="confirm"
        style="width:100%; margin-top:10px;"
    >
        <i class="fas fa-check"></i>
        Apply Quantity Changes
    </button>

</form>
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


<!-- SUBTOTAL -->

<div class="summary-row">

<span>
Subtotal
</span>

<b id="subtotalDisplay">
PHP <?php echo number_format($totalAmount, 2); ?>
</b>

</div>


<!-- DISCOUNT -->

<div class="summary-row">

<span>
Discount
</span>

<b id="discountDisplay">
PHP 0.00
</b>

</div>


<!-- FINAL TOTAL -->

<div class="summary-row total">

<span>
Total Amount
</span>

<b id="finalTotalDisplay">
PHP <?php echo number_format($totalAmount, 2); ?>
</b>

</div>


<form method="POST">
<div class="group" style="margin-top:15px;">

<label>
Discount
</label>

<select 
name="discount_type" 
id="discount_type"
onchange="updateDiscount()">

<option value="None">
No Discount
</option>

<option value="Senior Citizen">
Senior Citizen (20%)
</option>

<option value="PWD">
PWD (20%)
</option>

</select>

</div>


<div class="group" style="margin-top:15px;">

<label>
Customer Full Name (Optional)
</label>

<input
type="text"
name="customer_name">

</div>


<div class="group" style="margin-top:15px;">

<label>
ID Number (Optional)
</label>

<input
type="text"
name="customer_id_number">

</div>

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
PHP 0.00
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


<?php if (isset($_SESSION['last_sale'])): ?>

<div id="receiptModal" class="receipt-modal">

    <div class="receipt-box">

        <div class="receipt-header">

            <i class="fas fa-check-circle"></i>

            <h2>Sale Completed</h2>

            <p>Thank you for your purchase.</p>

        </div>


        <div class="receipt-items">

        <?php 
        foreach ($_SESSION['last_sale']['items'] as $item): 
        ?>

            <div class="receipt-item">

                <div class="receipt-item-name">
                    <?php 
                    echo htmlspecialchars($item['name']); 
                    ?>
                </div>

                <div class="receipt-item-info">

                    <span>
                        <?php 
                        echo (int)$item['quantity']; 
                        ?>
                        ×
                        PHP <?php echo number_format(
                            $item['price'],
                            2
                        ); ?>
                    </span>

                    <strong>
                        PHP <?php 
                        echo number_format(
                            $item['price'] * $item['quantity'],
                            2
                        ); ?>
                    </strong>

                </div>

            </div>

        <?php 
        endforeach; 
        ?>

    </div>

<!-- =========================================================
receipt-details
========================================================= -->
        <div class="receipt-details">

            <div>
                <span>Sale ID</span>
                <strong>
                    <?php 
                    echo $_SESSION['last_sale']['sale_id']; 
                    ?>
                </strong>
            </div>

            <div>
                <span>Payment Method</span>
                <strong>
                    <?php echo htmlspecialchars(
                        $_SESSION['last_sale']['payment_method']
                    ); ?>
                </strong>
            </div>
            <div>

            <span>Subtotal</span>
            <strong>
                PHP
                <?php
                echo number_format(
                    $_SESSION['last_sale']['subtotal'],
                    2
                );
                ?>
                </strong>
            </div>
            <div>
                <span>Discount</span>
                <strong>
                <?php
                echo htmlspecialchars(
                    $_SESSION['last_sale']['discount_type']
                );
                ?>
                <br>
                PHP
                <?php
                echo number_format(
                    $_SESSION['last_sale']['discount_amount'],
                    2
                );
                ?>
                </strong>
            </div>
            <div>
                <span>Total</span>
                <strong>
                    PHP <?php echo number_format(
                        $_SESSION['last_sale']['total'],
                        2
                    ); ?>
                </strong>
            </div>

            <div>
                <span>Amount Paid</span>
                <strong>
                    PHP <?php echo number_format(
                        $_SESSION['last_sale']['payment'],
                        2
                    ); ?>
                </strong>
            </div>

            <div class="receipt-change">

                <span>Change</span>

                <strong>
                    PHP <?php echo number_format(
                        $_SESSION['last_sale']['change'],
                        2
                    ); ?>
                </strong>

            </div>

        </div>

        <div class="receipt-actions">

            <button
                type="button"
                onclick="printReceipt()"
                class="receipt-print-button">

                <i class="fas fa-print"></i>
                Print Receipt

            </button>

            <button
                type="button"
                onclick="closeReceipt()"
                class="receipt-close-button">

                Done

            </button>

        </div>

    </div>

</div>

<?php unset($_SESSION['last_sale']); ?>

<?php endif; ?>


<!-- =========================================================
     Script
========================================================= -->
<script>

console.log("ValueMeds POS JavaScript loaded");

/*
|--------------------------------------------------------------------------
| QR SCANNER
|--------------------------------------------------------------------------
*/

const qrInput = document.getElementById("qr_value");

function focusQRScanner() {
    if (qrInput) {
        qrInput.focus();
        qrInput.select();
    }
}

if (qrInput) {

    window.addEventListener("load", function () {
        focusQRScanner();
    });

    qrInput.addEventListener("keydown", function (event) {

        if (event.key === "Enter") {

            event.preventDefault();

            const value = qrInput.value.trim();

            if (value === "") {
                return;
            }

            const form = qrInput.closest("form");

            if (!form) {
                return;
            }

            const scanButton =
                form.querySelector('button[name="scan_qr"]');

            if (scanButton) {
                form.requestSubmit(scanButton);
            }
        }
    });
}


/*
|--------------------------------------------------------------------------
| PAYMENT
|--------------------------------------------------------------------------
| Read the total directly from the page instead of embedding PHP inside
| JavaScript. This prevents a PHP/JavaScript syntax error from breaking
| every button on the page.
|--------------------------------------------------------------------------
*/

function getPageTotalAmount() {

    const summaryRows =
        document.querySelectorAll(".container .summary-row");

    for (const row of summaryRows) {

        const label = row.querySelector("span");

        if (
            label &&
            label.textContent.trim().toLowerCase() === "total amount"
        ) {

            const valueElement =
                row.querySelector("b");

            if (valueElement) {

                const value =
                    valueElement.textContent
                        .replace(/[^\d.-]/g, "")
                        .trim();

                return parseFloat(value) || 0;
            }
        }
    }

    return 0;
}

const subtotalAmount = getPageTotalAmount();

let discountAmount = 0;

let finalTotalAmount = subtotalAmount;

const paymentInput =
    document.getElementById("amount_tendered");

const changeDisplay =
    document.getElementById("changeAmount");


function updateChange() {

    if (!paymentInput || !changeDisplay) {
        return;
    }

    const amount =
        parseFloat(paymentInput.value) || 0;

    const change =
        amount - finalTotalAmount;

    if (amount <= 0) {

        changeDisplay.textContent = "PHP 0.00";
        changeDisplay.style.color = "#777";

    } else if (change < 0) {

        changeDisplay.textContent =
            "PHP " + Math.abs(change).toFixed(2) +
            " remaining";

        changeDisplay.style.color = "#c62828";

    } else {

        changeDisplay.textContent =
            "PHP " + change.toFixed(2);

        changeDisplay.style.color = "#16246D";
    }
}


function validatePayment() {

    const paymentInput =
        document.getElementById("amount_tendered");

    const checkoutButton =
        document.querySelector(
            'button[name="complete_sale"]'
        );

    if (!paymentInput || !checkoutButton) {
        return;
    }

    if (finalTotalAmount <= 0) {

        checkoutButton.disabled = true;
        return;
    }

    const amount =
        parseFloat(paymentInput.value) || 0;

    checkoutButton.disabled =
        amount < finalTotalAmount;
}

function updateDiscount(){

    const discountType =
        document.getElementById("discount_type").value;


    if(
        discountType === "Senior Citizen" ||
        discountType === "PWD"
    ){

        discountAmount =
            subtotalAmount * 0.20;

    }else{

        discountAmount = 0;

    }


    finalTotalAmount =
        subtotalAmount - discountAmount;



    document.getElementById(
        "discountDisplay"
    ).textContent =
        "PHP " + discountAmount.toFixed(2);



    document.getElementById(
        "finalTotalDisplay"
    ).textContent =
        "PHP " + finalTotalAmount.toFixed(2);



    updateChange();

    validatePayment();

}

function updatePaymentLabel() {

    const method =
        document.getElementById("payment_method");

    const label =
        document.getElementById("amountLabel");

    if (!method || !label) {
        return;
    }

    if (method.value === "GCash") {
        label.textContent = "GCash Amount";
    } else {
        label.textContent = "Amount Tendered";
    }

    updateChange();
    validatePayment();
}


if (paymentInput) {

    paymentInput.addEventListener(
        "input",
        function () {
            updateChange();
            validatePayment();
        }
    );
}


/*
|--------------------------------------------------------------------------
| PRODUCT QUANTITY
|--------------------------------------------------------------------------
*/

function changeProductQuantity(change) {

    const input =
        document.getElementById("productQuantity");

    if (!input) {
        return;
    }

    let quantity =
        parseInt(input.value, 10) || 1;

    const min =
        parseInt(input.min, 10) || 1;

    const max =
        parseInt(input.max, 10) || 999999;

    quantity += change;

    if (quantity < min) {
        quantity = min;
    }

    if (quantity > max) {
        quantity = max;
    }

    input.value = quantity;
}


/*
|--------------------------------------------------------------------------
| CART QUANTITY
|--------------------------------------------------------------------------
*/

function changeCartQuantity(medicineId, change) {

    const input =
        document.getElementById(
            "cart_quantity_" + medicineId
        );

    if (!input) {
        return;
    }

    let quantity =
        parseInt(input.value, 10) || 1;

    const min =
        parseInt(input.min, 10) || 1;

    const max =
        parseInt(input.max, 10) || 999999;

    quantity += change;

    if (quantity < min) {
        quantity = min;
    }

    if (quantity > max) {
        quantity = max;
    }

    input.value = quantity;
}


/*
|--------------------------------------------------------------------------
| APPLY CART QUANTITY CHANGES
|--------------------------------------------------------------------------
*/

const cartQuantityForm =
    document.getElementById("cartQuantityForm");

if (cartQuantityForm) {

    cartQuantityForm.addEventListener(
        "submit",
        function () {

            const inputs =
                document.querySelectorAll(
                    ".cart-quantity-input"
                );

            const quantities = {};

            inputs.forEach(function (input) {

                const medicineId =
                    input.dataset.medicineId;

                quantities[medicineId] =
                    parseInt(input.value, 10) || 1;
            });

            const hiddenInput =
                document.getElementById("cartQuantities");

            if (hiddenInput) {

                hiddenInput.value =
                    JSON.stringify(quantities);
            }
        }
    );
}


/*
|--------------------------------------------------------------------------
| RECEIPT MODAL
|--------------------------------------------------------------------------
*/

function closeReceipt() {

    const modal =
        document.getElementById("receiptModal");

    if (modal) {
        modal.style.display = "none";
    }
}


/*
|--------------------------------------------------------------------------
| RECEIPT INFORMATION
|--------------------------------------------------------------------------
| Everything is read from the visible receipt modal. No PHP is placed
| inside the JavaScript section.
|--------------------------------------------------------------------------
*/

function getReceiptValue(labelText) {

    const details =
        document.querySelectorAll(
            ".receipt-details > div"
        );

    for (const row of details) {

        const label =
            row.querySelector("span");

        const value =
            row.querySelector("strong");

        if (
            label &&
            value &&
            label.textContent.trim().toLowerCase() ===
            labelText.toLowerCase()
        ) {

            return value.textContent
                .replace(/\s+/g, " ")
                .trim();
        }
    }

    return "";
}


/*
|--------------------------------------------------------------------------
| PRINT RECEIPT
|--------------------------------------------------------------------------
*/

async function printReceipt() {

    const receipt = document.querySelector(".receipt-box");

    if (!receipt) {
        alert("Receipt information could not be found.");
        return;
    }

    if (typeof qz === "undefined") {
        alert("QZ Tray library could not be loaded.");
        return;
    }


    try {

        if (!qz.websocket.isActive()) {
            await qz.websocket.connect();
        }


        const printerName = "UTAK007";


        const config = qz.configs.create(
            printerName,
            {
                encoding:"UTF-8"
            }
        );


        const ESC = "\x1B";

        let data = [];

        const WIDTH = 48;


        function line(){
            return "-".repeat(WIDTH)+"\n";
        }


        function center(text){

            return text + "\n";

        }



        function row(left,right){

        const PRINT_WIDTH = 44;


        let gap =
            PRINT_WIDTH -
            left.length -
            right.length;


        if(gap < 1)
            gap = 1;


        return (
            left
            +
            " ".repeat(gap)
            +
            right
            +
            "\n"
        );

}

        function itemRow(qty,name,amount){

            const QTY_WIDTH = 5;
            const PRODUCT_WIDTH = 24;
            const AMOUNT_WIDTH = 15;


            let qtyText = String(qty);

            let amountText = String(amount)
                .replace(/\s+/g," ")
                .trim();


            if(name.length > PRODUCT_WIDTH){

                name =
                name.substring(
                    0,
                    PRODUCT_WIDTH
                );

            }


            return (
                qtyText.padEnd(QTY_WIDTH)
                +
                name.padEnd(PRODUCT_WIDTH)
                +
                amountText.padStart(AMOUNT_WIDTH)
                +
                "\n"
            );

        }

        data.push(
            ESC+"@"
        );


        /*
        =====================
        HEADER
        =====================
        */


        data.push(
            ESC+"a"+"\x01"
        );


        data.push(
            ESC+"a"+"\x01"
        );


        data.push(
            "ValueMeds\n"
        );


        data.push(
            "OFFICIAL SALES RECEIPT\n"
        );


        data.push(
            ESC+"a"+"\x00"
        );


        data.push("\n");


        data.push(
            ESC+"a"+"\x00"
        );



        /*
        =====================
        DATE TIME CASHIER
        =====================
        */


        let now =
            new Date();


        let date =
            now.toLocaleDateString(
                "en-PH",
                {
                    month:"short",
                    day:"2-digit",
                    year:"numeric"
                }
            );


        let time =
            now.toLocaleTimeString(
                "en-PH",
                {
                    hour:"2-digit",
                    minute:"2-digit"
                }
            );



        data.push(
            "Date: "
            +
            date
            +
            "\n"
        );


        data.push(
            "Time: "
            +
            time
            +
            "\n"
        );



        let cashier =
            document.querySelector(".admin")
            ?.textContent
            .replace(/\s+/g," ")
            .trim()
            ||
            "Cashier";


        data.push(
            "Cashier: "
            +
            cashier
            +
            "\n"
        );


        data.push(
            line()
        );



        /*
        =====================
        ITEM HEADER
        =====================
        */


        data.push(
            "Qty  "
            +
            "Product".padEnd(24)
            +
            "Amount".padStart(15)
            +
            "\n"
        );


        data.push(
            line()
        );



        /*
        =====================
        ITEMS
        =====================
        */


        const items =
            receipt.querySelectorAll(
                ".receipt-item"
            );


        let itemCount = 0;
        let quantityTotal = 0;



        items.forEach(item=>{


            let name =
                item.querySelector(
                    ".receipt-item-name"
                )
                .textContent
                .trim();



            /*
            Get quantity only
            Example:
            2 × PHP 10.00
            */

            let qtyText =
                item.querySelector(
                    ".receipt-item-info span"
                )
                .textContent
                .trim();


            let qtyMatch =
                qtyText.match(
                    /^(\d+)/
                );


            let qty =
                qtyMatch
                ? qtyMatch[1]
                : "1";



            /*
            Get subtotal only
            Example:
            PHP 20.00

            This avoids the unit price.
            */

            let amount =
                item.querySelector(
                    ".receipt-item-info strong"
                )
                .textContent
                .replace(/\s+/g," ")
                .trim();



            itemCount++;


            quantityTotal +=
                parseInt(qty);



            data.push(
                itemRow(
                    qty,
                    name,
                    amount
                )
            );


        });



        data.push(
            line()
        );



        /*
        =====================
        SUMMARY
        =====================
        */

       let subtotal =
            getReceiptValue(
                "Subtotal"
            );


        let discount =
            getReceiptValue(
                "Discount"
            );

        let total =
            getReceiptValue(
                "Total"
            );


        let paid =
            getReceiptValue(
                "Amount Paid"
            );


        let change =
            getReceiptValue(
                "Change"
            );


        let payment =
            getReceiptValue(
                "Payment Method"
            );


        data.push(
            row(
                "SUBTOTAL:",
                subtotal
            )
        );


        data.push(
            row(
                "DISCOUNT:",
                discount
            )
        );

        data.push(
            row(
                "TOTAL:",
                total
            )
        );

        data.push("\n");

        data.push(
            row(
                "Items:",
                itemCount.toString()
            )
        );

        data.push(
            row(
                "Quantity:",
                quantityTotal.toString()
            )
        );

        data.push(
            row(
                "Amount Paid:",
                paid
            )
        );

        data.push(
            row(
                "Change:",
                change
            )
        );

        data.push(
            line()
        );

        data.push(
            row(
                "Payment",
                payment
            )
        );

        data.push("\n\n");

        data.push(
            ESC+"a"+"\x01"
        );

        data.push(
            "Thank you!\n"
        );

        data.push(
            "for your Purchase.\n"
        );

         data.push("\n");
        data.push("\n");
        data.push("\n");

        data.push("\n");
        data.push("\n");
        data.push("\n");
        data.push("\n");
        data.push("\n");
        data.push("\n");
        data.push("\n");
        data.push("\n");
        data.push("\n");

        await qz.print(
            config,
            data
        );

        console.log(
            "Receipt printed successfully."
        );

    }
    catch(error){

        console.error(
            error
        );

        alert(
            "Unable to print receipt. Check QZ Tray and UTAK007 connection."
        );

    }

}

updateChange();
validatePayment();

</script>
</body>
</html>