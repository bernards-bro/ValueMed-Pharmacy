<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sales History</title>

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

.top{
display:grid;
grid-template-columns:1fr 1fr 1fr auto;
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
}

.export{
color:#16246D;
font-weight:bold;
cursor:pointer;
text-decoration:underline;
}

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
}

td{
padding:14px;
border-bottom:1px solid #eee;
}

.view{
background:#16246D;
color:white;
border:none;
padding:8px 15px;
border-radius:8px;
cursor:pointer;
}

.view:hover{
background:#2b45b5;
}

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

<a href="sales_history.php" class="active">
<i class="fas fa-clock"></i> Sales History
</a>

<a href="reports.php">
<i class="fas fa-chart-column"></i> Reports
</a>

</div>

<div class="main">

<div class="header">

<h1>Sales History</h1>

<div class="admin">
<i class="fas fa-user"></i>
Admin
</div>

</div>

<div class="container">

<div class="top">

<div class="group">

<label>Date From</label>

<input type="date">

</div>

<div class="group">

<label>Date To</label>

<input type="date">

</div>

<div class="group">

<label>Payment Method</label>

<select>

<option>All</option>
<option>Cash</option>
<option>GCash</option>
<option>Card</option>

</select>

</div>

<div>

<a class="export">
Export Report
</a>

</div>

</div>

</div>

<div class="container">

<div class="table-container">

<table>

<thead>

<tr>

<th>Invoice No.</th>
<th>Date & Time</th>
<th>Cashier</th>
<th>Total Items</th>
<th>Total Amount</th>
<th>Payment Method</th>
<th>Status</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<tr>

<td>INV-2026-0001</td>

<td>May 16, 2026 10:30 AM</td>

<td>Admin</td>

<td>3</td>

<td>₱250.00</td>

<td>Cash</td>

<td>Completed</td>

<td>
<button class="view">
View
</button>
</td>

</tr>

<tr>

<td>INV-2026-0002</td>

<td>May 16, 2026 11:15 AM</td>

<td>Admin</td>

<td>2</td>

<td>₱180.00</td>

<td>GCash</td>

<td>Completed</td>

<td>
<button class="view">
View
</button>
</td>

</tr>

</tbody>

</table>

</div>

</div>

<div class="container">

<h3 style="color:#16246D;margin-bottom:20px;">
Sale Details
</h3>

<div class="details">

<div class="info">

<p>
<b>Invoice No.:</b> INV-2026-0001
</p>

<p>
<b>Date & Time:</b> May 16, 2026 | 10:30 AM
</p>

<p>
<b>Cashier:</b> Admin
</p>

<p>
<b>Payment Method:</b> Cash
</p>

<p>
<b>Total Items:</b> 3
</p>

<p>
<b>Total Quantity:</b> 6
</p>

</div>

<div class="product-box">

<b>Product Details</b>

<br><br>

<table>

<tr>
<th>Product</th>
<th>Price</th>
<th>Qty</th>
<th>Subtotal</th>
</tr>

<tr>
<td>Paracetamol</td>
<td>₱5.00</td>
<td>2</td>
<td>₱10.00</td>
</tr>

<tr>
<td>Vitamin C</td>
<td>₱10.00</td>
<td>4</td>
<td>₱40.00</td>
</tr>

</table>

</div>

</div>

</div>

</div>

</body>
</html>