<?php

$products = [

["P001","Paracetamol","Tablet",5.50,250,"2027-05-20"],
["P002","Biogesic","Tablet",6.00,180,"2027-08-10"],
["P003","Amoxicillin","Capsule",12.50,80,"2026-11-15"],
["P004","Vitamin C","Tablet",7.50,120,"2027-02-18"],
["P005","Cetirizine","Tablet",8.25,60,"2026-10-01"]

];

?>

<!DOCTYPE html>
<html>

<head>

<title>Products</title>

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
position:fixed;
color:white;
}

.logo{
padding:25px;
font-size:25px;
font-weight:bold;
text-align:center;
}

.menu-title{
padding:20px 25px 8px;
font-size:13px;
opacity:.7;
}

.sidebar a{
display:block;
padding:15px 25px;
text-decoration:none;
color:white;
}

.sidebar a:hover,
.active{
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

<div class="menu-title">INVENTORY</div>

<a href="products.php" class="active">
<i class="fas fa-pills"></i> Products
</a>

<a href="add_products.php">
<i class="fas fa-plus-circle"></i> Add Products
</a>

<a href="stock_in.php">
<i class="fas fa-box-open"></i> Stock In
</a>

<a href="stock_alerts.php">
<i class="fas fa-triangle-exclamation"></i> Stock Alerts
</a>

<div class="menu-title">SALES</div>

<a href="pos.php">
<i class="fas fa-cash-register"></i> Point of Sales
</a>

<a href="sales_history.php">
<i class="fas fa-clock"></i> Sales History
</a>

<a href="reports.php">
<i class="fas fa-chart-column"></i> Reports
</a>

</div>

<div class="main">

<div class="header">

<h1>Products</h1>

<div class="admin">
<i class="fas fa-user"></i> Admin
</div>

</div>

<div class="top">

<input
type="text"
class="search"
placeholder="Search products...">

<button class="add-btn">
<i class="fas fa-plus"></i>
ADD PRODUCT
</button>

</div>

<table>

<tr>

<th>ID</th>
<th>Name</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Expiry Date</th>
<th>Actions</th>

</tr>

<?php

foreach($products as $p){

echo "

<tr>

<td>$p[0]</td>

<td>$p[1]</td>

<td>$p[2]</td>

<td>₱".number_format($p[3],2)."</td>

<td>$p[4]</td>

<td>$p[5]</td>

<td>

<a href='#' class='edit'>
<i class='fas fa-edit'></i>
</a>

<a href='#' class='delete'>
<i class='fas fa-trash'></i>
</a>

</td>

</tr>

";

}

?>

</table>

</div>

</body>

</html>