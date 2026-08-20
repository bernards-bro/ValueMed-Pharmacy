<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Stock Alerts</title>

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
text-align:center;
font-size:24px;
font-weight:bold;
}

.menu-title{
padding:20px 25px 10px;
font-size:13px;
opacity:.7;
}

.sidebar a{
display:block;
padding:15px 25px;
text-decoration:none;
color:white;
transition:.3s;
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
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.topbar{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

.filters{
display:flex;
gap:10px;
}

.filters button{
padding:10px 18px;
border:none;
border-radius:30px;
cursor:pointer;
background:white;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.filters button:hover,
.filters .active-filter{
background:#16246D;
color:white;
}

.export{
background:#16246D;
color:white;
padding:12px 20px;
border:none;
border-radius:30px;
cursor:pointer;
}

.export:hover{
background:#2743b8;
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
background:#F7F9FC;
}

.low{
color:#ff9800;
font-weight:bold;
}

.expiring{
color:#e67e22;
font-weight:bold;
}

.expired{
color:red;
font-weight:bold;
}

.view{
background:#16246D;
color:white;
padding:8px 12px;
border-radius:8px;
text-decoration:none;
}

</style>

</head>

<body>

<div class="sidebar">

<div class="logo">ValueMeds</div>

<a href="dashboard.php">
<i class="fas fa-home"></i> Dashboard
</a>

<div class="menu-title">INVENTORY</div>

<a href="products.php">
<i class="fas fa-pills"></i> Products
</a>

<a href="stock_in.php">
<i class="fas fa-box-open"></i> Stock In
</a>

<a href="stock_alerts.php" class="active">
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

<h1>Stock Alerts</h1>

<div class="admin">
<i class="fas fa-user"></i> Admin
</div>

</div>

<div class="topbar">

<div class="filters">

<button class="active-filter">Low Stock</button>

<button>Expiring</button>

<button>Expired</button>

</div>

<button class="export">

<i class="fas fa-file-export"></i>

Export Report

</button>

</div>

<table>

<tr>

<th>Product Name</th>
<th>Category</th>
<th>Stock</th>
<th>Expiry Date</th>
<th>Status</th>
<th>Action</th>

</tr>

<?php

$alerts=[

["Paracetamol","Tablet",8,"2027-05-15","Low"],
["Amoxicillin","Capsule",5,"2026-09-12","Critical"],
["Vitamin C","Tablet",25,"2026-08-02","Expiring"],
["Ibuprofen","Capsule",0,"2025-11-20","Expired"]

];

foreach($alerts as $row){

$statusClass="";

if($row[4]=="Low") $statusClass="low";
if($row[4]=="Critical") $statusClass="expired";
if($row[4]=="Expiring") $statusClass="expiring";
if($row[4]=="Expired") $statusClass="expired";

echo "

<tr>

<td>$row[0]</td>

<td>$row[1]</td>

<td>$row[2]</td>

<td>$row[3]</td>

<td class='$statusClass'>$row[4]</td>

<td>

<a href='#' class='view'>
View
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