<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Refunds | ValueMeds</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:"Segoe UI",sans-serif;
    background:#F5F7FC;
    display:flex;
}
.sidebar{
    width:250px;
    background:#16246D;
    min-height:100vh;
    color:white;
    padding:20px;
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
h1{color:#16246D;margin-bottom:5px;}
.subtitle{color:#555;margin-bottom:25px;}
.card{
    background:white;
    border-radius:15px;
    padding:20px;
    margin-bottom:20px;
}
input,textarea{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
}
textarea{
    resize:none;
    height:90px;
}
button{
    width:100%;
    padding:12px;
    background:#16246D;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
button:hover{
    background:#1d2f88;
}
table{
    width:100%;
    border-collapse:collapse;
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
.status{
    background:#FDECEC;
    color:#D62828;
    padding:5px 10px;
    border-radius:20px;
    font-size:13px;
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
.box h3{color:#16246D;font-size:14px;}
.box p{
    font-size:28px;
    color:#16246D;
    font-weight:bold;
    margin-top:8px;
}
</style>
</head>
<body>

<div class="sidebar">
    <div class="logo">ValueMeds</div>

    <a href="#"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="#"><i class="fa-solid fa-cart-shopping"></i> Sales</a>
    <a href="#"><i class="fa-solid fa-pills"></i> Products</a>
    <a href="#"><i class="fa-solid fa-warehouse"></i> Inventory</a>
    <a href="#"><i class="fa-solid fa-clock-rotate-left"></i> Sales History</a>
    <a href="Z_Sales.php"><i class="fa-solid fa-receipt"></i> Z Sales</a>
    <a href="#" class="active"><i class="fa-solid fa-arrow-rotate-left"></i> Refunds</a>
    <a href="#"><i class="fa-solid fa-chart-column"></i> Reports</a>
    <a href="#"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
</div>

<div class="main">

<h1>Refunds</h1>
<p class="subtitle">Process returned items and view refund history.</p>

<div class="card">
<h2><i class="fa-solid fa-arrow-rotate-left"></i> Process Refund</h2>

<input type="number" placeholder="Enter Sale ID">

<textarea placeholder="Reason for refund"></textarea>

<button>
<i class="fa-solid fa-check"></i> Process Refund
</button>
</div>

<div class="card">
<h2>Refund Summary</h2>

<div class="summary">
<div class="box">
<h3>Total Refunded Today</h3>
<p>₱290</p>
</div>

<div class="box">
<h3>Items Returned</h3>
<p>3</p>
</div>
</div>
</div>

<div class="card">

<h2>Refund History</h2>

<table>

<tr>
<th>Sale ID</th>
<th>Medicine</th>
<th>Qty</th>
<th>Amount</th>
<th>Status</th>
</tr>

<tr>
<td>15</td>
<td>Paracetamol</td>
<td>2</td>
<td>₱40</td>
<td><span class="status">Refunded</span></td>
</tr>

<tr>
<td>18</td>
<td>Bioflu</td>
<td>1</td>
<td>₱12</td>
<td><span class="status">Refunded</span></td>
</tr>

<tr>
<td>22</td>
<td>Vitamin C</td>
<td>1</td>
<td>₱25</td>
<td><span class="status">Refunded</span></td>
</tr>

</table>

</div>

</div>

</body>
</html>
