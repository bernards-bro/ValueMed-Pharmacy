<<<<<<< HEAD
=======
<?php

require 'db.php';

$sql = "SELECT * FROM medicines";
$result = $conn->query($sql);

?>

>>>>>>> 74d98675e6971956b3e8c17de842d5fdc39e27d7
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Stock In</title>

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

.admin{
background:white;
padding:10px 20px;
border-radius:30px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.container{
background:white;
padding:30px;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.section-title{
font-size:18px;
font-weight:bold;
color:#16246D;
margin-bottom:20px;
}

.scan-box{

height:120px;
border:2px dashed #16246D;
border-radius:15px;

display:flex;
justify-content:center;
align-items:center;

color:#16246D;

margin-bottom:25px;

}

.row{

display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
margin-bottom:20px;

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
.group textarea{

padding:12px;
border:1px solid #ccc;
border-radius:10px;
outline:none;

}

.group textarea{
height:80px;
resize:none;
}

.preview{

background:#eef4ff;
padding:15px;
border-radius:12px;
margin-top:20px;

}

.buttons{

display:flex;
justify-content:flex-end;
gap:15px;
margin-top:25px;

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

.cancel:hover{

background:#b8b8b8;

}

</style>

</head>

<body>

<!-- Sidebar -->

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

<<<<<<< HEAD
<a href="add_products.php">
<i class="fas fa-plus-circle"></i> Add Products
</a>

<a href="stock_in.php" class="active">
=======
<a href="stock_in.php">
>>>>>>> 74d98675e6971956b3e8c17de842d5fdc39e27d7
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

<a href="reports.php">
<i class="fas fa-chart-column"></i> Reports
</a>

</div>

<!-- Main -->

<div class="main">

<div class="header">

<h1>Stock In</h1>

<div class="admin">

<i class="fas fa-user"></i>
Admin

</div>

</div>

<div class="container">

<div class="section-title">

Scan Product QR Code

</div>

<div class="scan-box">

<i class="fas fa-qrcode fa-3x"></i>

</div>

<div class="section-title">

Product Information

</div>

<div class="row">

<div class="group">

<label>Product Name</label>

<input
type="text"
value="Paracetamol"
readonly>

</div>

<div class="group">

<label>Category</label>

<input
type="text"
value="Tablet"
readonly>

</div>

</div>

<div class="row">

<div class="group">

<label>Current Stock</label>

<input
type="number"
value="120"
readonly>

</div>

<div class="group">

<label>Expiry Date</label>

<input
type="date"
value="2027-05-15"
readonly>

</div>

</div>

<div class="row">

<div class="group">

<label>Quantity Received</label>

<input
type="number"
placeholder="Enter quantity">

</div>

<div class="group">

<label>Stock In Date</label>

<input
type="date">

</div>

</div>

<div class="group">

<label>Remarks (Optional)</label>

<textarea
placeholder="Enter remarks"></textarea>

</div>

<div class="preview">

<b>New Stock Preview</b>

<br><br>

Current Stock : <b>120</b>

<br>

Quantity Received : <b>0</b>

<br>

New Total Stock : <b>120</b>

</div>

<div class="buttons">

<button class="cancel">

Cancel

</button>

<button class="confirm">

Confirm

<<<<<<< HEAD
</button>

</div>

</div>
=======
<a href="add_products.php" class="add-btn">
<i class="fas fa-plus"></i>
ADD PRODUCT
</a>

</div>

<table>

<tr>

<th>ID</th>
<th>Name</th>
<th>Description</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Expiry Date</th>
<th>Actions</th>

</tr>

<?php

foreach($result as $p){

?>

<tr>

<td><?= $p['medicine_id'];?></td>

<td><?= $p['medicine_name']; ?></td>

<td><?= $p['description']; ?></td>

<td><?= $p['category']; ?></td>

<td><?= number_format($p['selling_price'],2); ?></td>

<td><?= $p['stock_quantity']; ?></td>

<td><?= $p['expiration_date']; ?></td>

<td>

<a href="edit.php?id=<?= $p['medicine_id']; ?>" class="edit">
<i class='fas fa-edit'></i>
</a>

<a href="delete.php?id=<?= $p['medicine_id']; ?>" class="delete">
<i class='fas fa-trash'></i>
</a>

</td>

</tr>

<?php }

?>

</table>
>>>>>>> 74d98675e6971956b3e8c17de842d5fdc39e27d7

</div>

</body>

</html>