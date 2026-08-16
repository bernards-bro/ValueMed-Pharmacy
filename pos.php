<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Point of Sale</title>

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

.header h1{
color:#16246D;
}

.admin{
background:white;
padding:10px 20px;
border-radius:30px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

/* Main POS */

.pos-top{
display:grid;
grid-template-columns:1fr 2fr;
gap:20px;
}

.container{
background:white;
padding:25px;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.section-title{
font-size:18px;
font-weight:bold;
color:#16246D;
margin-bottom:20px;
}

/* QR */

.scan-box{

height:180px;
border:2px dashed #16246D;
border-radius:15px;

display:flex;
justify-content:center;
align-items:center;

color:#16246D;

margin-bottom:15px;

}

.search{
width:100%;
padding:12px;
border:1px solid #ccc;
border-radius:10px;
outline:none;
}

/* Product */

.product-grid{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
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

.buttons{
display:flex;
justify-content:flex-end;
gap:15px;
margin-top:20px;
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

/* Bottom */

.pos-bottom{
display:grid;
grid-template-columns:1fr 1fr;
gap:20px;
margin-top:20px;
}

/* Cart */

.cart-table{
width:100%;
border-collapse:collapse;
}

.cart-table th{
background:#eef4ff;
color:#16246D;
padding:12px;
text-align:left;
}

.cart-table td{
padding:12px;
border-bottom:1px solid #eee;
}

.remove{
border:none;
background:#eee;
color:#16246D;
padding:7px 10px;
border-radius:7px;
cursor:pointer;
}

/* Payment */

.summary-row{
display:flex;
justify-content:space-between;
padding:12px 0;
border-bottom:1px solid #eee;
}

.total{
font-size:20px;
font-weight:bold;
color:#16246D;
}

.checkout{
width:100%;
margin-top:20px;
padding:14px;
border:none;
border-radius:10px;
background:#16246D;
color:white;
font-size:16px;
font-weight:bold;
cursor:pointer;
}

.checkout:hover{
background:#2b45b5;
}

@media(max-width:900px){

.sidebar{
width:200px;
}

.main{
margin-left:200px;
width:calc(100% - 200px);
}

.pos-top,
.pos-bottom{
grid-template-columns:1fr;
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

<a href="add_products.php">
<i class="fas fa-plus-circle"></i> Add Products
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

<a href="pos.php" class="active">
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

<h1>Point of Sale</h1>

<div class="admin">
<i class="fas fa-user"></i>
Admin
</div>

</div>

<div class="pos-top">

<div class="container">

<div class="section-title">
Scan Product QR Code / Barcode
</div>

<div class="scan-box">
<i class="fas fa-qrcode fa-3x"></i>
</div>

<input
class="search"
type="text"
placeholder="🔍 Search Product">

</div>

<div class="container">

<div class="section-title">
Product Information
</div>

<div class="product-grid">

<div class="group">

<label>Product Name</label>

<input
type="text"
placeholder="Name"
readonly>

</div>

<div class="group">

<label>Category</label>

<input
type="text"
placeholder="Category"
readonly>

</div>

<div class="group">

<label>Current Stock</label>

<input
type="number"
placeholder="Current Stock"
readonly>

</div>

<div class="group">

<label>Quantity</label>

<input
type="number"
value="1"
min="1">

</div>

</div>

<div class="buttons">

<button class="cancel">
Cancel
</button>

<button class="confirm">
Add to Cart
</button>

</div>

</div>

</div>

<div class="pos-bottom">

<div class="container">

<div class="section-title">
Cart Items
</div>

<table class="cart-table">

<thead>

<tr>
<th>Product</th>
<th>Qty</th>
<th>Price</th>
<th>Subtotal</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<tr>

<td>Paracetamol</td>

<td>2</td>

<td>₱5.00</td>

<td>₱10.00</td>

<td>
<button class="remove">
<i class="fas fa-trash"></i>
</button>
</td>

</tr>

</tbody>

</table>

</div>

<div class="container">

<div class="section-title">
Payment Summary
</div>

<div class="summary-row">

<span>Total Items</span>

<b>1</b>

</div>

<div class="summary-row">

<span>Total Quantity</span>

<b>2</b>

</div>

<div class="summary-row">

<span>Total Amount</span>

<b>₱10.00</b>

</div>

<div class="group" style="margin-top:15px;">

<label>Payment Method</label>

<select>

<option>Cash</option>
<option>GCash</option>

</select>

</div>

<div class="group" style="margin-top:15px;">

<label>Amount Tendered</label>

<input type="number" placeholder="Enter amount">

</div>

<div class="summary-row total">

<span>Change</span>

<span>₱0.00</span>

</div>

<button class="checkout">
Complete Sale
</button>

</div>

</div>

</div>

</body>
</html>