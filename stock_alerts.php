<?php

require 'connection.php';

$fullname = $_SESSION['fullname'] ?? 'Admin';

/*
|--------------------------------------------------------------------------
| Search and Filter
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? 'all';

/*
|--------------------------------------------------------------------------
| Build Query
|--------------------------------------------------------------------------
*/

$conditions = [];
$params = [];
$types = "";

/*
| Only products with stock <= 20 are considered alerts.
*/

$conditions[] = "m.stock <= 20";

/*
| Search
*/

if ($search !== '') {

    $conditions[] = "
        (
            m.name LIKE ?
            OR m.category LIKE ?
        )
    ";

    $searchParam = "%{$search}%";

    $params[] = $searchParam;
    $params[] = $searchParam;

    $types .= "ss";
}

/*
| Alert Filter
*/

if ($filter === 'out') {

    $conditions[] = "m.stock = 0";

}

elseif ($filter === 'critical') {

    $conditions[] = "m.stock > 0 AND m.stock <= 5";

}

elseif ($filter === 'low') {

    $conditions[] = "m.stock > 5 AND m.stock <= 20";

}


$where = "";

if (!empty($conditions)) {

    $where = "WHERE " . implode(" AND ", $conditions);

}


/*
|--------------------------------------------------------------------------
| Get Alert Products
|--------------------------------------------------------------------------
*/

$products = [];

$sql = "
    SELECT

        m.medicine_id,
        m.name,
        m.category,
        m.stock,
        m.expiry_date

    FROM medicines m

    $where

    ORDER BY
        m.stock ASC,
        m.expiry_date ASC,
        m.name ASC
";

$stmt = $conn->prepare($sql);

if (!empty($params)) {

    $stmt->bind_param(
        $types,
        ...$params
    );

}

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $products[] = $row;

}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Alert Counts
|--------------------------------------------------------------------------
*/

$outOfStock = 0;
$criticalStock = 0;
$lowStock = 0;

/*
| Out of Stock
*/

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM medicines
    WHERE stock = 0
");

if ($result) {

    $row = $result->fetch_assoc();

    $outOfStock = (int)$row['total'];

}


/*
| Critical Stock
*/

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM medicines
    WHERE stock > 0
    AND stock <= 5
");

if ($result) {

    $row = $result->fetch_assoc();

    $criticalStock = (int)$row['total'];

}


/*
| Low Stock
*/

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM medicines
    WHERE stock > 5
    AND stock <= 20
");

if ($result) {

    $row = $result->fetch_assoc();

    $lowStock = (int)$row['total'];

}


$totalAlerts =
    $outOfStock +
    $criticalStock +
    $lowStock;

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Stock Alerts - ValueMeds</title>

<link
rel="stylesheet"
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

/* =========================
   SIDEBAR
========================= */

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


/*
|--------------------------------------------------------------------------
| Main
|--------------------------------------------------------------------------
*/

.main{
margin-left:250px;
width:calc(100% - 250px);
padding:30px;
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Cards
|--------------------------------------------------------------------------
*/

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
font-size:28px;
font-weight:bold;
color:#16246D;
}


/*
|--------------------------------------------------------------------------
| Container
|--------------------------------------------------------------------------
*/

.container{
background:white;
padding:25px;
border-radius:20px;
box-shadow:0 5px 15px rgba(0,0,0,.08);
margin-bottom:20px;
}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

.search-form{
display:grid;
grid-template-columns:1fr 220px auto auto;
gap:12px;
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
background:white;
}

.group input:focus,
.group select:focus{
border-color:#16246D;
}

.search-btn{
background:#16246D;
color:white;
border:none;
padding:12px 22px;
border-radius:10px;
cursor:pointer;
font-weight:600;
}

.search-btn:hover{
background:#2b45b5;
}

.clear-btn{
background:#eee;
color:#333;
padding:12px 20px;
border-radius:10px;
text-decoration:none;
font-weight:600;
}


/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

.table-wrapper{
overflow-x:auto;
}

.alert-table{
width:100%;
border-collapse:collapse;
}

.alert-table th{
background:#eef4ff;
color:#16246D;
padding:14px;
text-align:left;
white-space:nowrap;
}

.alert-table td{
padding:14px;
border-bottom:1px solid #eee;
}

.alert-table tr:hover{
background:#fafbff;
}


/*
|--------------------------------------------------------------------------
| Stock Badges
|--------------------------------------------------------------------------
*/

.badge{
display:inline-block;
padding:6px 12px;
border-radius:20px;
font-size:13px;
font-weight:600;
}

.badge-out{
background:#ffe1e1;
color:#b00020;
}

.badge-critical{
background:#fff0d5;
color:#a85d00;
}

.badge-low{
background:#fff9d8;
color:#806900;
}

.stock-number{
font-weight:bold;
}


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

.action-btn{
display:inline-block;
padding:8px 12px;
border-radius:8px;
background:#eef4ff;
color:#16246D;
text-decoration:none;
font-weight:600;
font-size:13px;
}

.action-btn:hover{
background:#dce8ff;
}


/*
|--------------------------------------------------------------------------
| Empty
|--------------------------------------------------------------------------
*/

.empty{
text-align:center;
padding:40px;
color:#777;
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media(max-width:1100px){

.cards{
grid-template-columns:1fr 1fr;
}

.search-form{
grid-template-columns:1fr 1fr;
}

}

@media(max-width:900px){

.sidebar{
width:200px;
}

.main{
margin-left:200px;
width:calc(100% - 200px);
}

}

</style>

</head>

<body>


<!-- =========================
     SIDEBAR
========================= -->
<div class="sidebar">
    <div class="logo">
        ValueMeds
    </div>

    <a href="dashboard.php">
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

    <a href="inventory.php">
        <i class="fas fa-box-open"></i>
        Inventory
    </a>

    <a href="stock_alerts.php"  class="active">
        <i class="fas fa-triangle-exclamation"></i>
        Stock Alerts
    </a>

    <div class="menu-title">
    SALES
    </div>

    <a href="pos.php">
        <i class="fas fa-cash-register"></i>
        Point of Sale
    </a>

    <a href="sales_history.php">
        <i class="fas fa-clock-rotate-left"></i>
        Sales History
    </a>

    <a href="reports.php">
        <i class="fas fa-chart-column"></i>
        Reports
    </a>
</div>


<!-- MAIN -->

<div class="main">


<!-- HEADER -->

<div class="header">

<h1>Stock Alerts</h1>

<div class="admin">

<i class="fas fa-user"></i>

<?= htmlspecialchars($fullname); ?>

</div>

</div>


<!-- SUMMARY -->

<div class="cards">


<div class="card">

<div class="card-title">
Total Alerts
</div>

<div class="card-value">
<?= $totalAlerts; ?>
</div>

</div>


<div class="card">

<div class="card-title">
Out of Stock
</div>

<div class="card-value">
<?= $outOfStock; ?>
</div>

</div>


<div class="card">

<div class="card-title">
Critical Stock
</div>

<div class="card-value">
<?= $criticalStock; ?>
</div>

</div>


<div class="card">

<div class="card-title">
Low Stock
</div>

<div class="card-value">
<?= $lowStock; ?>
</div>

</div>

</div>


<!-- SEARCH / FILTER -->

<div class="container">

<form method="GET" class="search-form">


<div class="group">

<label>
Search Product
</label>

<input
type="text"
name="search"
placeholder="Search product or category..."
value="<?= htmlspecialchars($search); ?>">

</div>


<div class="group">

<label>
Alert Level
</label>

<select name="filter">

<option
value="all"
<?= $filter === 'all' ? 'selected' : ''; ?>>

All Alerts

</option>

<option
value="out"
<?= $filter === 'out' ? 'selected' : ''; ?>>

Out of Stock

</option>

<option
value="critical"
<?= $filter === 'critical' ? 'selected' : ''; ?>>

Critical Stock

</option>

<option
value="low"
<?= $filter === 'low' ? 'selected' : ''; ?>>

Low Stock

</option>

</select>

</div>


<button
type="submit"
class="search-btn">

<i class="fas fa-search"></i>

Search

</button>


<a
href="stock_alerts.php"
class="clear-btn">

Clear

</a>

</form>

</div>


<!-- ALERT TABLE -->

<div class="container">

<h3 style="
color:#16246D;
margin-bottom:20px;
">

<i class="fas fa-triangle-exclamation"></i>

Products Requiring Attention

</h3>


<div class="table-wrapper">

<table class="alert-table">

<thead>

<tr>

<th>Product Name</th>

<th>Category</th>

<th>Current Stock</th>

<th>Alert Level</th>

<th>Expiry Date</th>

<th>Action</th>

</tr>

</thead>


<tbody>


<?php if (empty($products)): ?>

<tr>

<td
colspan="6"
class="empty">

<i
class="fas fa-circle-check"
style="font-size:30px;margin-bottom:10px;">
</i>

<br>

No stock alerts found.

</td>

</tr>


<?php else: ?>


<?php foreach ($products as $product): ?>

<?php

$stock = (int)$product['stock'];

if ($stock === 0) {

    $alertText = 'Out of Stock';
    $alertClass = 'badge-out';

}
elseif ($stock <= 5) {

    $alertText = 'Critical';
    $alertClass = 'badge-critical';

}
else {

    $alertText = 'Low Stock';
    $alertClass = 'badge-low';

}

?>


<tr>


<td>

<strong>

<?= htmlspecialchars($product['name']); ?>

</strong>

</td>


<td>

<?= htmlspecialchars(
    $product['category'] ?? 'Uncategorized'
); ?>

</td>


<td>

<span class="stock-number">

<?= $stock; ?>

</span>

</td>


<td>

<span class="badge <?= $alertClass; ?>">

<?= $alertText; ?>

</span>

</td>


<td>

<?php if (!empty($product['expiry_date'])): ?>

<?= date(
    'M d, Y',
    strtotime($product['expiry_date'])
); ?>

<?php else: ?>

N/A

<?php endif; ?>

</td>


<td>

<a
href="edit.php?id=<?= (int)$product['medicine_id']; ?>"
class="action-btn">

<i class="fas fa-pen"></i>

Manage Stock

</a>

</td>


</tr>

<?php endforeach; ?>


<?php endif; ?>


</tbody>

</table>

</div>

</div>

</div>

</body>

</html>

