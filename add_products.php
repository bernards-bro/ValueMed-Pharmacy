<?php
<<<<<<< HEAD
require 'connection.php';
session_start();
=======

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST"){

    $medicine_name = $_POST ["medicine_name"];
    $description = $_POST ["description"];
    $category = $_POST ["category"];
    $selling_price = $_POST ["selling_price"];
    $stock_quantity = $_POST ["stock_quantity"];
    $expiration_date = $_POST ["expiration_date"];

        $sql = "INSERT INTO medicines (
        medicine_name, description, category, selling_price, stock_quantity, expiration_date)
        VALUES (
        '$medicine_name', '$description', '$category', '$selling_price', '$stock_quantity', '$expiration_date')";

    if ($conn->query($sql) === TRUE){

       header("Location: products.php");
       exit();

    } else {
        echo "Error: " . $conn->error;
    }
}

>>>>>>> 74d98675e6971956b3e8c17de842d5fdc39e27d7
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Product</title>

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
padding:20px 25px 8px;
font-size:13px;
opacity:.7;
}

.sidebar a{
display:block;
padding:15px 25px;
color:white;
text-decoration:none;
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

.form-box{
background:white;
padding:30px;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
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
.group select,
.group textarea{

padding:12px;
border:1px solid #ccc;
border-radius:10px;
outline:none;
font-size:15px;

}

.group textarea{
resize:none;
height:100px;
}

.buttons{

display:flex;
justify-content:flex-end;
gap:15px;
margin-top:25px;

}

.cancel{

background:#ccc;
padding:12px 25px;
border:none;
border-radius:10px;
cursor:pointer;

}

.save{

background:#16246D;
color:white;
padding:12px 25px;
border:none;
border-radius:10px;
cursor:pointer;

}

.save:hover{
background:#2743b8;
}

.cancel:hover{
background:#b3b3b3;
}

.note{
margin-top:20px;
color:#777;
font-size:14px;
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

<h1>Add Products</h1>

<div class="admin">

<i class="fas fa-user"></i> Admin

</div>

</div>

<div class="form-box">

<form action="" method="POST">

<div class="row">

<div class="group">

<label>Medicine Name</label>

<input
type="text"
name="medicine_name"
placeholder="Enter medicine name"
required>

</div>

<div class="group">
    <label>Medicine Description </label>

<input type="text"
name="description"
placeholder="Enter Description"
required>

</div>

</div>

<div class="row">

<div class="group">

<label>Category</label>

<select name="category" required>

<option value="">Select Category</option>
<option>Tablet</option>
<option>Capsule</option>
<option>Syrup</option>
<option>Injection</option>
<option>Vitamin</option>

</select>

</div>

</div>

<div class="group">

<label>Selling Price</label>

<input type="number"
step="0.01"
name="selling_price"
placeholder="Enter price"
required>

</div>
</div>

<br>

<div class="row">

<div class="group">

<label>Stock Quantity</label>

<input
type="number"
name="stock_quantity"
placeholder="Enter Stock Quantity"
required>

</div>

<div class="group">

<label>Expiration Date</label>

<input
type="date"
name="expiration_date"
required>

</div>

</div>


<p class="note">
QR Code will be generated automatically after saving.
</p>

<div class="buttons">

<button
type="reset"
class="cancel">
Cancel
</button>

<button
type="submit"
class="save">
Save Product
</button>

</div>

</form>

</div>

</div>

</body>
</html>