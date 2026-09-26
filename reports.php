<?php

require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$fullname = $_SESSION['fullname'] ?? 'Admin';

/*
|--------------------------------------------------------------------------
| Date Filters
|--------------------------------------------------------------------------
*/

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo   = trim($_GET['date_to'] ?? '');

/*
|--------------------------------------------------------------------------
| Build Date Condition
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

$where = "";

if (!empty($conditions)) {

    $where = "WHERE " . implode(" AND ", $conditions);

}

/*
|--------------------------------------------------------------------------
| Export CSV
|--------------------------------------------------------------------------
*/

if (isset($_GET['export']) && $_GET['export'] === 'excel') {

    $sql = "
        SELECT
            DATE(s.sale_date) AS sale_day,
            COUNT(DISTINCT s.sale_id) AS transactions,
            COALESCE(SUM(s.total_amount), 0) AS revenue
        FROM sales s
        $where
        GROUP BY DATE(s.sale_date)
        ORDER BY sale_day ASC
    ";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();

    $result = $stmt->get_result();

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sales_report.csv"');

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'Date',
        'Transactions',
        'Revenue'
    ]);

    while ($row = $result->fetch_assoc()) {

        fputcsv($output, [
            $row['sale_day'],
            $row['transactions'],
            number_format((float)$row['revenue'], 2, '.', '')
        ]);

    }

    fclose($output);

    $stmt->close();

    exit;

}

/*
|--------------------------------------------------------------------------
| Total Sales
|--------------------------------------------------------------------------
*/

$totalSales = 0;

$sql = "
    SELECT
        COALESCE(SUM(s.total_amount), 0) AS total_sales
    FROM sales s
    $where
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$totalSales = (float)$row['total_sales'];

$stmt->close();


/*
|--------------------------------------------------------------------------
| Total Profit
|--------------------------------------------------------------------------
*/

$totalProfit = 0;

$sql = "
    SELECT
        COALESCE(
            SUM(
                (si.unit_price - m.cost_price)
                * si.quantity
            ),
            0
        ) AS total_profit

    FROM sale_items si

    INNER JOIN sales s
        ON si.sale_id = s.sale_id

    INNER JOIN medicines m
        ON si.medicine_id = m.medicine_id

    $where
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

$totalProfit = (float)$row['total_profit'];

$stmt->close();


/*
|--------------------------------------------------------------------------
| Total Products
|--------------------------------------------------------------------------
*/

$totalProducts = 0;

$sql = "
    SELECT COUNT(*) AS total_products
    FROM medicines
";

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $totalProducts = (int)$row['total_products'];

}


/*
|--------------------------------------------------------------------------
| Stock Alerts
|--------------------------------------------------------------------------
*/

$stockAlert = 0;

$sql = "
    SELECT COUNT(*) AS stock_alert
    FROM medicines
    WHERE stock <= 20
";

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $stockAlert = (int)$row['stock_alert'];

}


/*
|--------------------------------------------------------------------------
| Today's Sale
|--------------------------------------------------------------------------
*/

$todaySale = 0;

$sql = "
    SELECT
        COALESCE(SUM(total_amount), 0) AS today_sale
    FROM sales
    WHERE DATE(sale_date) = CURDATE()
";

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $todaySale = (float)$row['today_sale'];

}


/*
|--------------------------------------------------------------------------
| Sales Trend
|--------------------------------------------------------------------------
*/

$chartLabels = [];
$chartValues = [];

$sql = "
    SELECT
        DATE(s.sale_date) AS sale_day,
        COALESCE(SUM(s.total_amount), 0) AS daily_sales

    FROM sales s

    $where

    GROUP BY DATE(s.sale_date)

    ORDER BY sale_day ASC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $chartLabels[] = date(
        'M d',
        strtotime($row['sale_day'])
    );

    $chartValues[] = (float)$row['daily_sales'];

}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Top Selling Products
|--------------------------------------------------------------------------
*/

$topProducts = [];

$sql = "
    SELECT

        m.name AS product_name,

        COALESCE(SUM(si.quantity), 0) AS quantity_sold,

        COALESCE(SUM(si.subtotal), 0) AS revenue

    FROM sale_items si

    INNER JOIN sales s
        ON si.sale_id = s.sale_id

    INNER JOIN medicines m
        ON si.medicine_id = m.medicine_id

    $where

    GROUP BY
        m.medicine_id,
        m.name

    ORDER BY quantity_sold DESC

    LIMIT 10
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $topProducts[] = $row;

}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Detailed Daily Sales
|--------------------------------------------------------------------------
*/

$dailySales = [];

$sql = "
    SELECT

        DATE(s.sale_date) AS sale_day,

        COUNT(DISTINCT s.sale_id) AS transactions,

        COALESCE(SUM(s.total_amount), 0) AS revenue

    FROM sales s

    $where

    GROUP BY DATE(s.sale_date)

    ORDER BY sale_day DESC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $dailySales[] = $row;

}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Sales Reports - ValueMeds</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
/*============================================================
main
============================================================*/

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

.container{
background:white;
padding:25px;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
margin-bottom:20px;
}

.filter{
display:grid;
grid-template-columns:1fr 1fr auto;
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

.group input{
padding:12px;
border:1px solid #ccc;
border-radius:10px;
outline:none;
}

.group input:focus{
border-color:#16246D;
}

.generate{
background:#16246D;
color:white;
border:none;
padding:12px 25px;
border-radius:10px;
cursor:pointer;
font-weight:600;
}

.generate:hover{
background:#2b45b5;
}

.clear{
background:#eee;
color:#333;
padding:12px 20px;
border-radius:10px;
text-decoration:none;
font-weight:600;
}

.filter-buttons{
display:flex;
gap:10px;
}

/* Export */

.export{
display:flex;
justify-content:flex-end;
gap:15px;
margin-bottom:20px;
}

.export a{
color:#16246D;
font-weight:bold;
text-decoration:none;
padding:10px 15px;
border-radius:8px;
background:white;
box-shadow:0 3px 10px rgba(0,0,0,.06);
}

.export a:hover{
background:#eef4ff;
}

/* Cards */

.cards{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-bottom:20px;
}

.card{
background:white;
padding:25px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
border:1px solid #eee;
}

.card-title{
color:#666;
font-size:14px;
margin-bottom:15px;
}

.card-value{
font-size:25px;
font-weight:bold;
color:#16246D;
}

/* Chart */

.chart-container{
height:320px;
position:relative;
}

/* Tables */

.product-list{
width:100%;
border-collapse:collapse;
}

.product-list th{
background:#eef4ff;
color:#16246D;
padding:14px;
text-align:left;
}

.product-list td{
padding:14px;
border-bottom:1px solid #eee;
}

.product-list tr:hover{
background:#fafbff;
}

.empty{
text-align:center;
padding:30px;
color:#777;
}

/* Print */

@media print{

body{
background:white;
display:block;
}

.sidebar,
.header,
.filter,
.export{
display:none !important;
}

.main{
margin:0;
width:100%;
padding:0;
}

.container,
.card{
box-shadow:none;
border:1px solid #ddd;
}

.cards{
grid-template-columns:repeat(4,1fr);
}

}

@media(max-width:1100px){

.cards{
grid-template-columns:1fr 1fr;
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

.filter{
grid-template-columns:1fr;
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

    <a href="sales_history.php">
        <i class="fas fa-clock-rotate-left"></i>
        Sales History
    </a>

    <a href="reports.php" class="active">
        <i class="fas fa-chart-column"></i>
        Reports
    </a>
   <a href="refund.php">
        <i class="fa-solid fa-arrow-right-arrow-left"></i>
        Refund
    </a>

<a href="z_reading.php">
    <i class="fas fa-file-invoice"></i>
    Z Reading
</a>
</div>


<!-- MAIN -->

<div class="main">

<div class="header">

<h1>Sales Reports</h1>

<div class="admin">

<i class="fas fa-user"></i>

<?= htmlspecialchars($fullname); ?>

</div>

</div>


<!-- FILTER -->

<div class="container">

<form method="GET" class="filter">

<div class="group">

<label>Date From</label>

<input
type="date"
name="date_from"
value="<?= htmlspecialchars($dateFrom); ?>">

</div>


<div class="group">

<label>Date To</label>

<input
type="date"
name="date_to"
value="<?= htmlspecialchars($dateTo); ?>">

</div>


<div class="filter-buttons">

<button
type="submit"
class="generate">

<i class="fas fa-chart-column"></i>

Generate Report

</button>

<a
href="reports.php"
class="clear">

Clear

</a>

</div>

</form>

</div>


<!-- EXPORT -->

<div class="export">

<a
href="#"
onclick="window.print(); return false;">

<i class="fas fa-file-pdf"></i>

Export PDF

</a>


<a
href="reports.php?export=excel<?= $dateFrom !== '' ? '&date_from=' . urlencode($dateFrom) : ''; ?><?= $dateTo !== '' ? '&date_to=' . urlencode($dateTo) : ''; ?>">

<i class="fas fa-file-excel"></i>

Export Excel

</a>

</div>


<!-- SUMMARY CARDS -->

<div class="cards">


<div class="card">

<div class="card-title">
Total Sales
</div>

<div class="card-value">

₱<?= number_format($totalSales, 2); ?>

</div>

</div>


<div class="card">

<div class="card-title">
Total Profit
</div>

<div class="card-value">

₱<?= number_format($totalProfit, 2); ?>

</div>

</div>


<div class="card">

<div class="card-title">
Total Products
</div>

<div class="card-value">

<?= $totalProducts; ?>

</div>

</div>


<div class="card">

<div class="card-title">
Stock Alerts
</div>

<div class="card-value">

<?= $stockAlert; ?>

</div>

</div>

</div>


<!-- TODAY -->

<div class="container">

<h3 style="color:#16246D;margin-bottom:10px;">

Today's Sale

</h3>

<div style="
font-size:30px;
font-weight:bold;
color:#16246D;
">

₱<?= number_format($todaySale, 2); ?>

</div>

</div>


<!-- SALES TREND -->

<div class="container">

<h3 style="color:#16246D;margin-bottom:20px;">

<i class="fas fa-chart-line"></i>

Sales Trend

</h3>

<div class="chart-container">

<canvas id="salesChart"></canvas>

</div>

</div>


<!-- TOP PRODUCTS -->

<div class="container">

<h3 style="color:#16246D;margin-bottom:20px;">

<i class="fas fa-ranking-star"></i>

Top Selling Products

</h3>


<table class="product-list">

<thead>

<tr>

<th>Product Name</th>

<th>Quantity Sold</th>

<th>Revenue</th>

</tr>

</thead>


<tbody>

<?php if (empty($topProducts)): ?>

<tr>

<td
colspan="3"
class="empty">

No sales found for the selected period.

</td>

</tr>

<?php else: ?>

<?php foreach ($topProducts as $product): ?>

<tr>

<td>

<?= htmlspecialchars($product['product_name']); ?>

</td>

<td>

<?= (int)$product['quantity_sold']; ?>

</td>

<td>

₱<?= number_format(
    (float)$product['revenue'],
    2
); ?>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>


<!-- DAILY SALES -->

<div class="container">

<h3 style="color:#16246D;margin-bottom:20px;">

<i class="fas fa-table"></i>

Daily Sales Summary

</h3>


<table class="product-list">

<thead>

<tr>

<th>Date</th>

<th>Transactions</th>

<th>Revenue</th>

</tr>

</thead>


<tbody>

<?php if (empty($dailySales)): ?>

<tr>

<td
colspan="3"
class="empty">

No sales found for the selected period.

</td>

</tr>

<?php else: ?>

<?php foreach ($dailySales as $sale): ?>

<tr>

<td>

<?= date(
    'F d, Y',
    strtotime($sale['sale_day'])
); ?>

</td>

<td>

<?= (int)$sale['transactions']; ?>

</td>

<td>

₱<?= number_format(
    (float)$sale['revenue'],
    2
); ?>

</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>

</div>


<script>

const chartLabels =
<?= json_encode($chartLabels); ?>;

const chartValues =
<?= json_encode($chartValues); ?>;


const ctx =
document.getElementById('salesChart');


new Chart(ctx, {

type: 'line',

data: {

labels: chartLabels,

datasets: [{

label: 'Sales',

data: chartValues,

borderWidth: 3,

fill: false,

tension: 0.3,

pointRadius: 5

}]

},

options: {

responsive: true,

maintainAspectRatio: false,

plugins: {

legend: {

display: true

}

},

scales: {

y: {

beginAtZero: true,

ticks: {

callback: function(value) {

return '₱' +
Number(value).toLocaleString();

}

}

}

}

}

});

</script>

</body>

</html>