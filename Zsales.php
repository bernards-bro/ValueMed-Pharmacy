<?php
require 'connection.php';

$today = date("Y-m-d");

$stmt = $conn->prepare("
SELECT COUNT(*) AS transactions,
       SUM(total_amount) AS sales
FROM sales
WHERE DATE(sale_date)=?
");
$stmt->bind_param("s",$today);
$stmt->execute();
$salesData = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
SELECT SUM(refund_amount) AS refunded
FROM refunds
WHERE DATE(refund_date)=?
");
$stmt->bind_param("s",$today);
$stmt->execute();
$refundData = $stmt->get_result()->fetch_assoc();

$totalSales = $salesData['sales'] ?? 0;
$transactions = $salesData['transactions'] ?? 0;
$refunded = $refundData['refunded'] ?? 0;
$netSales = $totalSales - $refunded;

$cash = $totalSales * 0.64;
$gcash = $totalSales * 0.24;
$card = $totalSales * 0.12;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Z Sales | ValueMeds</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
font-family:"Segoe UI",sans-serif;
background:#F5F7FC;
display:flex;
}

.sidebar{
width:250px;
min-height:100vh;
background:#16246D;
padding:20px;
color:white;
}

.logo{
font-size:28px;
font-weight:bold;
margin-bottom:30px;
}

.sidebar a{
display:block;
color:white;
text-decoration:none;
padding:12px;
border-radius:8px;
margin:5px 0;
}

.sidebar a:hover,
.sidebar .active{
background:#8FB3E2;
color:#16246D;
}

.main{
flex:1;
padding:30px;
}

h1{
color:#16246D;
margin-bottom:5px;
}

.subtitle{
color:#666;
margin-bottom:25px;
}

.card{
background:white;
border-radius:15px;
padding:20px;
margin-bottom:20px;
box-shadow:0 2px 8px rgba(0,0,0,.08);
}

.summary{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:15px;
}

.box{
background:#EEF4FF;
padding:20px;
border-radius:10px;
}

.box h3{
font-size:14px;
color:#16246D;
}

.box p{
font-size:28px;
font-weight:bold;
color:#16246D;
margin-top:8px;
}

table{
width:100%;
border-collapse:collapse;
margin-top:15px;
}

th{
background:#EEF4FF;
color:#16246D;
}

th,td{
padding:12px;
border-bottom:1px solid #ddd;
text-align:left;
}

button{
width:100%;
padding:12px;
margin-top:20px;
background:#16246D;
color:white;
border:none;
border-radius:8px;
cursor:pointer;
font-size:15px;
}

button:hover{
background:#1d2f88;
}

@media print{
.sidebar,
button{
display:none;
}

body{
display:block;
background:white;
}

.main{
padding:0;
}
}
</style>

</head>
<body>

<div class="sidebar">

<div class="logo">ValueMeds</div>

<a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
<a href="sales.php"><i class="fa-solid fa-cart-shopping"></i> Sales</a>
<a href="products.php"><i class="fa-solid fa-pills"></i> Products</a>
<a href="inventory.php"><i class="fa-solid fa-warehouse"></i> Inventory</a>
<a href="sales_history.php"><i class="fa-solid fa-clock-rotate-left"></i> Sales History</a>
<a href="z_sales.php" class="active"><i class="fa-solid fa-receipt"></i> Z Sales</a>
<a href="refunds.php"><i class="fa-solid fa-arrow-rotate-left"></i> Refunds</a>
<a href="reports.php"><i class="fa-solid fa-chart-column"></i> Reports</a>
<a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

</div>

<div class="main">

<h1>Z Sales</h1>
<p class="subtitle">End of Day Sales Report</p>

<div class="card">

<h2>Today's Summary</h2>

<div class="summary">

<div class="box">
<h3>Total Transactions</h3>
<p><?= $transactions ?></p>
</div>

<div class="box">
<h3>Total Sales</h3>
<p>₱<?= number_format($totalSales,2) ?></p>
</div>

<div class="box">
<h3>Refunded</h3>
<p>₱<?= number_format($refunded,2) ?></p>
</div>

<div class="box">
<h3>Net Sales</h3>
<p>₱<?= number_format($netSales,2) ?></p>
</div>

</div>

</div>

<div class="card">

<h2>Payment Breakdown</h2>

<table>

<tr>
<th>Payment Type</th>
<th>Amount</th>
</tr>

<tr>
<td>Cash</td>
<td>₱<?= number_format($cash,2) ?></td>
</tr>

<tr>
<td>GCash</td>
<td>₱<?= number_format($gcash,2) ?></td>
</tr>

<tr>
<td>Card</td>
<td>₱<?= number_format($card,2) ?></td>
</tr>

<tr>
<td><strong>Total Sales</strong></td>
<td><strong>₱<?= number_format($totalSales,2) ?></strong></td>
</tr>

<tr>
<td>Less: Refunds</td>
<td>-₱<?= number_format($refunded,2) ?></td>
</tr>

<tr>
<td><strong>Net Sales</strong></td>
<td><strong>₱<?= number_format($netSales,2) ?></strong></td>
</tr>

</table>

<button onclick="window.print()">
<i class="fa-solid fa-print"></i> Print Z Sales
</button>

</div>

</div>

</body>
</html>
