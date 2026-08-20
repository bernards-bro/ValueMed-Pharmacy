<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reports</title>

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

.sidebar{
width:250px;
height:100vh;
background:#16246D;
position:fixed;
color:white;
}

.logo{
padding:25px;
font-size:24px;
font-weight:bold;
text-align:center;
}

.menu-title{
padding:20px 25px 10px;
font-size:13px;
opacity:.7;
}

.sidebar a{
display:block;
padding:15px 25px;
color:white;
text-decoration:none;
transition:.3s;
}

.sidebar a:hover,
.active{
background:#8FB3E2;
color:#16246D;
}

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

.generate{
background:#16246D;
color:white;
border:none;
padding:12px 25px;
border-radius:10px;
cursor:pointer;
}

.generate:hover{
background:#2b45b5;
}

.export{
text-align:right;
margin-bottom:20px;
}

.export a{
color:#16246D;
font-weight:bold;
text-decoration:underline;
margin-left:15px;
cursor:pointer;
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

.chart{
height:280px;
background:#eef4ff;
border-radius:15px;
display:flex;
justify-content:center;
align-items:center;
color:#16246D;
font-size:25px;
font-weight:bold;
}

.chart i{
margin-right:10px;
}

/* Products */

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

@media(max-width:900px){

.sidebar{
width:200px;
}

.main{
margin-left:200px;
width:calc(100% - 200px);
}

.cards{
grid-template-columns:1fr 1fr;
}

}

</style>

</head>

<body>

<div class="sidebar">

<div class="logo">
ValueMeds
</div>

<a href="dashboard.php">
<i class="fas fa-home"></i> Dashboard
</a>

<div class="menu-title">
INVENTORY
</div>

<a href="products.php">
<i class="fas fa-pills"></i> Products
</a>

<a href="stock_in.php">
<i class="fas fa-box-open"></i> Stock In
</a>

<a href="stock_alerts.php">
<i class="fas fa-triangle-exclamation"></i> Stock Alerts
</a>

<div class="menu-title">
SALES
</div>

<a href="pos.php">
<i class="fas fa-cash-register"></i> Point of Sales
</a>

<a href="sales_history.php">
<i class="fas fa-clock"></i> Sales History
</a>

<a href="reports.php" class="active">
<i class="fas fa-chart-column"></i> Reports
</a>

</div>

<div class="main">

<div class="header">

<h1>Reports</h1>

<div class="admin">
<i class="fas fa-user"></i>
Admin
</div>

</div>

<div class="container">

<div class="filter">

<div class="group">

<label>Date Range</label>

<input type="date">

</div>

<div class="group">

<label>To</label>

<input type="date">

</div>

<button class="generate">
Generate Report
</button>

</div>

</div>

<div class="export">

<a>
Export PDF
</a>

<a>
Export Excel
</a>

</div>

<div class="cards">

<div class="card">

<div class="card-title">
Total Profit
</div>

<div class="card-value">
₱25,450.00
</div>

</div>

<div class="card">

<div class="card-title">
Total Products
</div>

<div class="card-value">
125
</div>

</div>

<div class="card">

<div class="card-title">
Stock Alert
</div>

<div class="card-value">
8
</div>

</div>

<div class="card">

<div class="card-title">
Today's Sale
</div>

<div class="card-value">
₱5,250.00
</div>

</div>

</div>

<div class="container">

<h3 style="color:#16246D;margin-bottom:20px;">
Sales Trend
</h3>

<div class="chart">

<i class="fas fa-chart-line"></i>
Sales Chart

</div>

</div>

<div class="container">

<h3 style="color:#16246D;margin-bottom:20px;">
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

<tr>

<td>Paracetamol</td>
<td>250</td>
<td>₱1,250.00</td>

</tr>

<tr>

<td>Vitamin C</td>
<td>180</td>
<td>₱1,800.00</td>

</tr>

<tr>

<td>Ibuprofen</td>
<td>120</td>
<td>₱1,440.00</td>

</tr>

</tbody>

</table>

</div>

</div>

</body>
</html>