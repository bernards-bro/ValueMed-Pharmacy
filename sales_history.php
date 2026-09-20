<?php

require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Current User
|--------------------------------------------------------------------------
*/

$fullname = $_SESSION['fullname'] ?? 'Admin';


/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');
$paymentMethod = trim($_GET['payment_method'] ?? '');
$viewSaleId = intval($_GET['view'] ?? 0);


/*
|--------------------------------------------------------------------------
| Export Report
|--------------------------------------------------------------------------
*/

if (isset($_GET['export'])) {

    $conditions = [];
    $params = [];
    $types = "";

    if ($dateFrom !== '') {

        $conditions[] = "DATE(s.sale_date) >= ?";
        $params[] = $dateFrom;
        $types .= "s";
    }

    if ($dateTo !== '') {

        $conditions[] = "DATE(s.sale_date) <= ?";
        $params[] = $dateTo;
        $types .= "s";
    }

    if ($paymentMethod !== '') {

        $conditions[] = "s.payment_method = ?";
        $params[] = $paymentMethod;
        $types .= "s";
    }


    $where = "";

    if (!empty($conditions)) {
        $where = "WHERE " . implode(" AND ", $conditions);
    }


    $sql = "
        SELECT
            s.sale_id,
            s.sale_date,
            u.fullname AS cashier,
            s.total_amount,
            s.payment_method,
            COALESCE(SUM(si.quantity), 0) AS total_quantity

        FROM sales s

        INNER JOIN users u
            ON s.cashier_id = u.user_id

        LEFT JOIN sale_items si
            ON s.sale_id = si.sale_id

        $where

        GROUP BY
            s.sale_id,
            s.sale_date,
            u.fullname,
            s.total_amount,
            s.payment_method

        ORDER BY s.sale_date DESC
    ";


    $stmt = $conn->prepare($sql);


    if (!empty($params)) {

        $stmt->bind_param(
            $types,
            ...$params
        );
    }


    $stmt->execute();

    $result = $stmt->get_result();


    /*
     * Tell browser this is a CSV file.
     */

    header('Content-Type: text/csv; charset=utf-8');

    header(
        'Content-Disposition: attachment; filename="sales_report.csv"'
    );


    $output = fopen('php://output', 'w');


    /*
     * CSV headings.
     */

    fputcsv($output, [
        'Invoice No.',
        'Date & Time',
        'Cashier',
        'Total Items',
        'Total Quantity',
        'Total Amount',
        'Payment Method',
        'Status'
    ]);


    while ($row = $result->fetch_assoc()) {

        $invoice =
            'INV-' .
            date('Y', strtotime($row['sale_date'])) .
            '-' .
            str_pad(
                $row['sale_id'],
                4,
                '0',
                STR_PAD_LEFT
            );


        /*
         * Count unique products in this sale.
         */

        $countStmt = $conn->prepare("
            SELECT COUNT(*) AS total_items
            FROM sale_items
            WHERE sale_id = ?
        ");

        $countStmt->bind_param(
            "i",
            $row['sale_id']
        );

        $countStmt->execute();

        $countResult =
            $countStmt->get_result()->fetch_assoc();

        $countStmt->close();


        fputcsv($output, [
            $invoice,
            date(
                'M d, Y h:i A',
                strtotime($row['sale_date'])
            ),
            $row['cashier'],
            $countResult['total_items'],
            $row['total_quantity'],
            number_format(
                $row['total_amount'],
                2,
                '.',
                ''
            ),
            $row['payment_method'],
            'Completed'
        ]);
    }


    fclose($output);

    exit;
}


/*
|--------------------------------------------------------------------------
| Build Sales History Query
|--------------------------------------------------------------------------
*/

$conditions = [];
$params = [];
$types = "";


if ($dateFrom !== '') {

    $conditions[] = "DATE(s.sale_date) >= ?";
    $params[] = $dateFrom;
    $types .= "s";
}


if ($dateTo !== '') {

    $conditions[] = "DATE(s.sale_date) <= ?";
    $params[] = $dateTo;
    $types .= "s";
}


if ($paymentMethod !== '') {

    $conditions[] = "s.payment_method = ?";
    $params[] = $paymentMethod;
    $types .= "s";
}


$where = "";

if (!empty($conditions)) {

    $where =
        "WHERE " .
        implode(" AND ", $conditions);
}


/*
|--------------------------------------------------------------------------
| Get Sales
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        s.sale_id,
        s.sale_date,
        u.fullname AS cashier,
        s.total_amount,
        s.payment_method,

        COUNT(si.sale_item_id) AS total_items,

        COALESCE(
            SUM(si.quantity),
            0
        ) AS total_quantity

    FROM sales s

    INNER JOIN users u
        ON s.cashier_id = u.user_id

    LEFT JOIN sale_items si
        ON s.sale_id = si.sale_id

    $where

    GROUP BY
        s.sale_id,
        s.sale_date,
        u.fullname,
        s.total_amount,
        s.payment_method

    ORDER BY s.sale_date DESC
";


$stmt = $conn->prepare($sql);


if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );
}


$stmt->execute();

$sales = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Selected Sale Details
|--------------------------------------------------------------------------
*/

$selectedSale = null;
$saleItems = [];


if ($viewSaleId > 0) {

    /*
     * Get sale header.
     */

    $stmt = $conn->prepare("
        SELECT
            s.sale_id,
            s.sale_date,
            s.total_amount,
            s.payment_method,
            s.amount_paid,
            s.change_amount,
            u.fullname AS cashier

        FROM sales s

        INNER JOIN users u
            ON s.cashier_id = u.user_id

        WHERE s.sale_id = ?
    ");

    $stmt->bind_param(
        "i",
        $viewSaleId
    );

    $stmt->execute();

    $selectedSale =
        $stmt->get_result()->fetch_assoc();

    $stmt->close();


    /*
     * Get products in the sale.
     */

    if ($selectedSale) {

        $stmt = $conn->prepare("
            SELECT
                si.sale_item_id,
                si.quantity,
                si.unit_price,
                si.subtotal,
                m.name,
                m.category

            FROM sale_items si

            INNER JOIN medicines m
                ON si.medicine_id = m.medicine_id

            WHERE si.sale_id = ?

            ORDER BY si.sale_item_id ASC
        ");

        $stmt->bind_param(
            "i",
            $viewSaleId
        );

        $stmt->execute();

        $result = $stmt->get_result();


        while ($item = $result->fetch_assoc()) {

            $saleItems[] = $item;
        }

        $stmt->close();
    }
}


/*
|--------------------------------------------------------------------------
| Selected Sale Calculations
|--------------------------------------------------------------------------
*/

$selectedTotalQuantity = 0;

foreach ($saleItems as $item) {

    $selectedTotalQuantity +=
        (int)$item['quantity'];
}


?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Sales History | ValueMeds</title>


<link
rel="stylesheet"
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


/* =========================
   CONTAINERS
========================= */

.container{
background:white;
padding:25px;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
margin-bottom:20px;
}


/* =========================
   FILTERS
========================= */

.top{
display:grid;
grid-template-columns:1fr 1fr 1fr auto auto;
gap:20px;
align-items:end;
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
background:white;
}


/* =========================
   BUTTONS
========================= */

.filter-btn{
background:#16246D;
color:white;
border:none;
padding:12px 20px;
border-radius:10px;
cursor:pointer;
font-weight:600;
}

.filter-btn:hover{
background:#2b45b5;
}

.clear-btn{
background:#eee;
color:#16246D;
padding:12px 20px;
border-radius:10px;
text-decoration:none;
font-weight:600;
}

.clear-btn:hover{
background:#ddd;
}

.export{
color:#16246D;
font-weight:bold;
cursor:pointer;
text-decoration:none;
padding:12px 5px;
}

.export:hover{
text-decoration:underline;
}


/* =========================
   TABLE
========================= */

.table-container{
overflow-x:auto;
}

table{
width:100%;
border-collapse:collapse;
}

th{
background:#eef4ff;
color:#16246D;
padding:14px;
text-align:left;
white-space:nowrap;
}

td{
padding:14px;
border-bottom:1px solid #eee;
white-space:nowrap;
}

.view{
background:#16246D;
color:white;
border:none;
padding:8px 15px;
border-radius:8px;
cursor:pointer;
text-decoration:none;
display:inline-block;
}

.view:hover{
background:#2b45b5;
}

.status{
color:#16833a;
font-weight:600;
}


/* =========================
   SALE DETAILS
========================= */

.details{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
}

.info p{
margin:10px 0;
}

.product-box{
background:#eef4ff;
padding:20px;
border-radius:12px;
overflow-x:auto;
}

.product-box table{
background:white;
border-radius:10px;
overflow:hidden;
}

.product-box th{
padding:12px;
}

.product-box td{
padding:12px;
}

.no-sale{
text-align:center;
padding:35px;
color:#777;
}

.sale-heading{
color:#16246D;
margin-bottom:20px;
display:flex;
justify-content:space-between;
align-items:center;
}

.sale-total{
font-size:20px;
font-weight:bold;
color:#16246D;
}


/* =========================
   RESPONSIVE
========================= */

@media(max-width:1100px){

.top{
grid-template-columns:1fr 1fr;
}

.details{
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

.header h1{
font-size:24px;
}

.container{
padding:18px;
}

.top{
grid-template-columns:1fr;
}

.details{
grid-template-columns:1fr;
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

    <a href="pos.php">
        <i class="fas fa-cash-register"></i>
        Point of Sale
    </a>

    <a href="sales_history.php" class="active">
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
Sales History
</h1>


<div class="admin">

<i class="fas fa-user"></i>

<?php
echo htmlspecialchars($fullname);
?>

</div>

</div>


<!-- =========================================================
     FILTER
========================================================= -->

<div class="container">

<form
method="GET"
class="top">


<div class="group">

<label>
Date From
</label>

<input
type="date"
name="date_from"
value="<?php
echo htmlspecialchars($dateFrom);
?>">

</div>


<div class="group">

<label>
Date To
</label>

<input
type="date"
name="date_to"
value="<?php
echo htmlspecialchars($dateTo);
?>">

</div>


<div class="group">

<label>
Payment Method
</label>

<select name="payment_method">

<option value="">
All Payment Methods
</option>

<option
value="Cash"
<?php
if ($paymentMethod === 'Cash') {
    echo 'selected';
}
?>>

Cash

</option>

<option
value="GCash"
<?php
if ($paymentMethod === 'GCash') {
    echo 'selected';
}
?>>

GCash

</option>

</select>

</div>


<button
type="submit"
class="filter-btn">

<i class="fas fa-filter"></i>

Filter

</button>


<a
href="sales_history.php"
class="clear-btn">

Clear

</a>


</form>


<div style="margin-top:15px;">

<a
class="export"
href="sales_history.php?export=1<?php

if ($dateFrom !== '') {
    echo '&date_from=' . urlencode($dateFrom);
}

if ($dateTo !== '') {
    echo '&date_to=' . urlencode($dateTo);
}

if ($paymentMethod !== '') {
    echo '&payment_method=' .
         urlencode($paymentMethod);
}

?>">

<i class="fas fa-file-export"></i>

Export Report

</a>

</div>

</div>


<!-- =========================================================
     SALES TABLE
========================================================= -->

<div class="container">

<div class="table-container">

<table>

<thead>

<tr>

<th>
Invoice No.
</th>

<th>
Date & Time
</th>

<th>
Cashier
</th>

<th>
Total Items
</th>

<th>
Total Amount
</th>

<th>
Payment Method
</th>

<th>
Status
</th>

<th>
Action
</th>

</tr>

</thead>


<tbody>


<?php if ($sales->num_rows > 0): ?>


<?php while ($sale = $sales->fetch_assoc()): ?>


<?php

$invoice =
    'INV-' .
    date(
        'Y',
        strtotime($sale['sale_date'])
    ) .
    '-' .
    str_pad(
        $sale['sale_id'],
        4,
        '0',
        STR_PAD_LEFT
    );

?>


<tr>


<td>

<strong>

<?php
echo htmlspecialchars($invoice);
?>

</strong>

</td>


<td>

<?php

echo date(
    'M d, Y h:i A',
    strtotime($sale['sale_date'])
);

?>

</td>


<td>

<?php
echo htmlspecialchars(
    $sale['cashier']
);
?>

</td>


<td>

<?php
echo (int)$sale['total_items'];
?>

</td>


<td>

₱<?php

echo number_format(
    $sale['total_amount'],
    2
);

?>

</td>


<td>

<?php
echo htmlspecialchars(
    $sale['payment_method']
);
?>

</td>


<td>

<span class="status">

Completed

</span>

</td>


<td>

<a
class="view"
href="sales_history.php?view=<?php

echo $sale['sale_id'];

if ($dateFrom !== '') {
    echo '&date_from=' .
         urlencode($dateFrom);
}

if ($dateTo !== '') {
    echo '&date_to=' .
         urlencode($dateTo);
}

if ($paymentMethod !== '') {
    echo '&payment_method=' .
         urlencode($paymentMethod);
}

?>">

<i class="fas fa-eye"></i>

View

</a>

</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td
colspan="8"
class="no-sale">

<i class="fas fa-receipt fa-2x"></i>

<br><br>

No sales transactions found.

</td>

</tr>


<?php endif; ?>


</tbody>

</table>

</div>

</div>


<!-- =========================================================
     SALE DETAILS
========================================================= -->

<div class="container">


<?php if ($selectedSale): ?>


<?php

$invoice =
    'INV-' .
    date(
        'Y',
        strtotime(
            $selectedSale['sale_date']
        )
    ) .
    '-' .
    str_pad(
        $selectedSale['sale_id'],
        4,
        '0',
        STR_PAD_LEFT
    );

?>


<div class="sale-heading">

<h3>
Sale Details
</h3>


<span class="sale-total">

₱<?php

echo number_format(
    $selectedSale['total_amount'],
    2
);

?>

</span>

</div>


<div class="details">


<!-- SALE INFORMATION -->

<div class="info">


<p>

<b>
Invoice No.:
</b>

<?php
echo htmlspecialchars($invoice);
?>

</p>


<p>

<b>
Date & Time:
</b>

<?php

echo date(
    'M d, Y | h:i A',
    strtotime(
        $selectedSale['sale_date']
    )
);

?>

</p>


<p>

<b>
Cashier:
</b>

<?php
echo htmlspecialchars(
    $selectedSale['cashier']
);
?>

</p>


<p>

<b>
Payment Method:
</b>

<?php
echo htmlspecialchars(
    $selectedSale['payment_method']
);
?>

</p>


<p>

<b>
Total Items:
</b>

<?php
echo count($saleItems);
?>

</p>


<p>

<b>
Total Quantity:
</b>

<?php
echo $selectedTotalQuantity;
?>

</p>


<p>

<b>
Amount Paid:
</b>

₱<?php

echo number_format(
    $selectedSale['amount_paid'],
    2
);

?>

</p>


<p>

<b>
Change:
</b>

₱<?php

echo number_format(
    $selectedSale['change_amount'],
    2
);

?>

</p>


<p>

<b>
Status:
</b>

<span class="status">
Completed
</span>

</p>


</div>


<!-- PRODUCT DETAILS -->

<div class="product-box">


<b>
Product Details
</b>


<br><br>


<?php if (!empty($saleItems)): ?>


<table>

<thead>

<tr>

<th>
Product
</th>

<th>
Price
</th>

<th>
Qty
</th>

<th>
Subtotal
</th>

</tr>

</thead>


<tbody>


<?php foreach ($saleItems as $item): ?>


<tr>


<td>

<?php

echo htmlspecialchars(
    $item['name']
);

?>

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

₱<?php

echo number_format(
    $item['unit_price'],
    2
);

?>

</td>


<td>

<?php

echo (int)$item['quantity'];

?>

</td>


<td>

₱<?php

echo number_format(
    $item['subtotal'],
    2
);

?>

</td>


</tr>


<?php endforeach; ?>


</tbody>

</table>


<?php else: ?>


<div class="no-sale">

No product details found.

</div>


<?php endif; ?>


</div>


</div>


<?php else: ?>


<div class="no-sale">

<i class="fas fa-receipt fa-2x"></i>

<br><br>

Select a transaction above to view its details.

</div>


<?php endif; ?>


</div>


</div>


</body>

</html>