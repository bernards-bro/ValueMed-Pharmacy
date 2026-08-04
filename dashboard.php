<?php
session_start();

// Sample Data palang hahah 
$totalProfit = 12000;
$totalProducts = 300;
$stockAlert = 28;
$todaysSale = 65;
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Dashboard | ValueMeds</title>

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

/* Sidebar */

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

/* Header */

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

/* Welcome eme eme */

.welcome{
background:white;
padding:25px;
border-radius:18px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
margin-bottom:25px;
}

.welcome h2{
color:#16246D;
margin-bottom:8px;
}

.welcome p{
color:#666;
}

/* Cards */

.cards{
display:grid;
grid-template-columns:repeat(4,1fr);
gap:20px;
margin-bottom:25px;
}

.card{
background:white;
padding:25px;
border-radius:18px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.card h4{
color:#666;
margin-bottom:12px;
}

.card h2{
color:#16246D;
font-size:30px;
}

.card i{
float:right;
font-size:32px;
color:#16246D;
opacity:.2;
}

/* Dashboard */

.dashboard{
display:grid;
grid-template-columns:2fr 1fr;
gap:20px;
}

.panel{
background:white;
padding:20px;
border-radius:18px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.panel h3{
margin-bottom:20px;
color:#16246D;
}

.chart{
height:320px;
display:flex;
align-items:center;
justify-content:center;
border:2px dashed #ddd;
border-radius:15px;
color:#999;
}

.transaction{
display:flex;
justify-content:space-between;
padding:12px 0;
border-bottom:1px solid #eee;
}

.transaction:last-child{
border:none;
}

.price{
font-weight:bold;
color:green;
}

</style>

</head>

<body>

<!-- Sidebar -->

<div class="sidebar">

<div class="logo">
ValueMeds
</div>

<a href="dashboard.php" class="active">
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

<a href="add_products.php">
<i class="fas fa-plus-circle"></i>
Add Products
</a>

<a href="stock_in.php">
<i class="fas fa-box-open"></i>
Stock In
</a>

<a href="stock_alerts.php">
<i class="fas fa-triangle-exclamation"></i>
Stock Alerts
</a>

<div class="menu-title">
SALES
</div>

<a href="#">
<i class="fas fa-cash-register"></i>
Point of Sales
</a>

<a href="#">
<i class="fas fa-clock-rotate-left"></i>
Sales History
</a>

<a href="#">
<i class="fas fa-chart-column"></i>
Reports
</a>

</div>

<!-- Main -->

<div class="main">

<div class="header">

<h1>Dashboard</h1>

<div class="admin">
<i class="fas fa-user"></i>
Admin
</div>

</div>

<div class="welcome">

<h2>Welcome back!</h2>

<p>Here's what's happening in your pharmacy today.</p>

</div>

<div class="cards">

<div class="card">
<i class="fas fa-sack-dollar"></i>
<h4>Total Profit</h4>
<h2>₱<?= number_format($totalProfit) ?></h2>
</div>

<div class="card">
<i class="fas fa-capsules"></i>
<h4>Total Products</h4>
<h2><?= $totalProducts ?></h2>
</div>

<div class="card">
<i class="fas fa-triangle-exclamation"></i>
<h4>Stock Alert</h4>
<h2><?= $stockAlert ?></h2>
</div>

<div class="card">
<i class="fas fa-receipt"></i>
<h4>Today's Sale</h4>
<h2><?= $todaysSale ?></h2>
</div>

</div>

<div class="dashboard">

<div class="panel">

<h3>Sales Overview</h3>

<div class="chart">

Chart.js will be placed here

</div>

</div>

<div class="panel">

<h3>Recent Transactions</h3>

<div class="transaction">
<span>Paracetamol</span>
<span class="price">₱120</span>
</div>

<div class="transaction">
<span>Biogesic</span>
<span class="price">₱95</span>
</div>

<div class="transaction">
<span>Vitamin C</span>
<span class="price">₱250</span>
</div>

<div class="transaction">
<span>Amoxicillin</span>
<span class="price">₱180</span>
</div>

<div class="transaction">
<span>Cetirizine</span>
<span class="price">₱140</span>
</div>

</div>

</div>

</div>

</body>
</html>