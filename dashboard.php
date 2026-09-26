<?php

require 'connection.php';


$fullname = $_SESSION['fullname'] ?? 'Admin';


/*
|--------------------------------------------------------------------------
| TOTAL PRODUCTS
|--------------------------------------------------------------------------
*/

$totalProducts = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM medicines
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalProducts = (int)$row['total'];

}


/*
|--------------------------------------------------------------------------
| STOCK ALERTS
|--------------------------------------------------------------------------
|
| Same threshold used by stock_alerts.php:
|
| 0       = Out of Stock
| 1 - 5   = Critical
| 6 - 20  = Low Stock
|
*/

$stockAlert = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM medicines
    WHERE stock <= 20
");

if ($result) {

    $row = $result->fetch_assoc();

    $stockAlert = (int)$row['total'];

}


/*
|--------------------------------------------------------------------------
| TODAY'S SALES
|--------------------------------------------------------------------------
*/

$todaysSale = 0;

$result = $conn->query("
    SELECT
        COALESCE(SUM(total_amount), 0) AS total
    FROM sales
    WHERE DATE(sale_date) = CURDATE()
");

if ($result) {

    $row = $result->fetch_assoc();

    $todaysSale = (float)$row['total'];

}


/*
|--------------------------------------------------------------------------
| TOTAL PROFIT
|--------------------------------------------------------------------------
|
| Profit:
|
| (Actual Selling Price - Cost Price) × Quantity
|
| unit_price is used instead of medicines.price because the unit price
| recorded in sale_items represents the price used when the sale happened.
|
*/

$totalProfit = 0;

$result = $conn->query("
    SELECT
        COALESCE(
            SUM(
                (si.unit_price - m.cost_price)
                * si.quantity
            ),
            0
        ) AS profit

    FROM sale_items si

    INNER JOIN medicines m
        ON si.medicine_id = m.medicine_id
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalProfit = (float)$row['profit'];

}


/*
|--------------------------------------------------------------------------
| RECENT SALES
|--------------------------------------------------------------------------
|
| Show actual sales rather than individual sale items.
|
*/

$recentSales = $conn->query("
    SELECT

        s.sale_id,
        s.total_amount,
        s.sale_date

    FROM sales s

    ORDER BY s.sale_date DESC

    LIMIT 5
");


/*
|--------------------------------------------------------------------------
| 7-DAY SALES OVERVIEW
|--------------------------------------------------------------------------
*/

$chartLabels = [];
$chartValues = [];

/*
| Create the previous 6 days plus today.
*/

for ($i = 6; $i >= 0; $i--) {

    $date = date(
        'Y-m-d',
        strtotime("-$i days")
    );

    $chartLabels[] = date(
        'M d',
        strtotime($date)
    );

    $chartValues[$date] = 0;

}


/*
| Get actual sales.
*/

$result = $conn->query("
    SELECT

        DATE(sale_date) AS sale_day,

        COALESCE(
            SUM(total_amount),
            0
        ) AS daily_sales

    FROM sales

    WHERE sale_date >= DATE_SUB(
        CURDATE(),
        INTERVAL 6 DAY
    )

    GROUP BY DATE(sale_date)

    ORDER BY sale_day ASC
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $date = $row['sale_day'];

        if (isset($chartValues[$date])) {

            $chartValues[$date] =
                (float)$row['daily_sales'];

        }

    }

}


/*
|--------------------------------------------------------------------------
| Convert chart values to normal indexed array
|--------------------------------------------------------------------------
*/

$chartData = array_values($chartValues);


/*
|--------------------------------------------------------------------------
| TODAY'S TRANSACTION COUNT
|--------------------------------------------------------------------------
*/

$todayTransactions = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM sales
    WHERE DATE(sale_date) = CURDATE()
");

if ($result) {

    $row = $result->fetch_assoc();

    $todayTransactions = (int)$row['total'];

}


/*
|--------------------------------------------------------------------------
| OUT OF STOCK COUNT
|--------------------------------------------------------------------------
*/

$outOfStock = 0;

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM medicines
    WHERE stock = 0
");

if ($result) {

    $row = $result->fetch_assoc();

    $outOfStock = (int)$row['total'];

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1">

<title>Dashboard | ValueMeds</title>


<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


<script
src="https://cdn.jsdelivr.net/npm/chart.js">
</script>


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

    border-bottom:1px solid
    rgba(255,255,255,.15);

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


/* =========================
   MAIN
========================= */

.main{

    margin-left:250px;

    width:calc(100% - 250px);

    padding:30px;

}


/* =========================
   HEADER
========================= */

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

    box-shadow:
    0 5px 15px rgba(0,0,0,.08);

    font-weight:600;

}


/* =========================
   WELCOME
========================= */

.welcome{

    background:white;

    padding:25px;

    border-radius:18px;

    box-shadow:
    0 5px 15px rgba(0,0,0,.08);

    margin-bottom:25px;

}

.welcome h2{

    color:#16246D;

    margin-bottom:8px;

}

.welcome p{

    color:#666;

}


/* =========================
   CARDS
========================= */

.cards{

    display:grid;

    grid-template-columns:
    repeat(4,1fr);

    gap:20px;

    margin-bottom:25px;

}

.card{

    background:white;

    padding:25px;

    border-radius:18px;

    box-shadow:
    0 5px 15px rgba(0,0,0,.08);

    position:relative;

}

.card h4{

    color:#666;

    margin-bottom:12px;

}

.card h2{

    color:#16246D;

    font-size:28px;

}

.card i{

    position:absolute;

    right:22px;

    top:22px;

    font-size:32px;

    color:#16246D;

    opacity:.2;

}


/* =========================
   DASHBOARD GRID
========================= */

.dashboard{

    display:grid;

    grid-template-columns:
    2fr 1fr;

    gap:20px;

}


/* =========================
   PANELS
========================= */

.panel{

    background:white;

    padding:20px;

    border-radius:18px;

    box-shadow:
    0 5px 15px rgba(0,0,0,.08);

}

.panel h3{

    margin-bottom:20px;

    color:#16246D;

}


/* =========================
   CHART
========================= */

.chart{

    height:320px;

    position:relative;

}


/* =========================
   TRANSACTIONS
========================= */

.transaction{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding:14px 0;

    border-bottom:1px solid #eee;

}

.transaction:last-child{

    border:none;

}

.transaction-info{

    display:flex;

    align-items:center;

    gap:12px;

}

.transaction-icon{

    width:38px;

    height:38px;

    border-radius:10px;

    background:#eef4ff;

    color:#16246D;

    display:flex;

    align-items:center;

    justify-content:center;

}

.transaction-id{

    font-weight:600;

    color:#333;

}

.transaction-date{

    font-size:12px;

    color:#888;

    margin-top:3px;

}

.price{

    font-weight:bold;

    color:#16246D;

}


/* =========================
   QUICK INFO
========================= */

.info-grid{

    display:grid;

    grid-template-columns:
    1fr 1fr;

    gap:15px;

    margin-top:20px;

}

.info-box{

    background:#F5F7FC;

    padding:18px;

    border-radius:12px;

}

.info-box span{

    display:block;

    color:#777;

    font-size:13px;

    margin-bottom:6px;

}

.info-box strong{

    color:#16246D;

    font-size:22px;

}


/* =========================
   RESPONSIVE
========================= */

@media(max-width:1100px){

    .cards{

        grid-template-columns:
        1fr 1fr;

    }

    .dashboard{

        grid-template-columns:
        1fr;

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


@media(max-width:650px){

    .main{

        padding:15px;

    }

    .header{

        align-items:flex-start;

        gap:15px;

        flex-direction:column;

    }

    .cards{

        grid-template-columns:
        1fr;

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

    <a href="dashboard.php"  class="active">
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

    <a href="stock_alerts.php">
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
 <a href="refund.php">
        <i class="fa-solid fa-arrow-right-arrow-left"></i>
        Refund
    </a>

		<a href="z_reading.php">
    <i class="fas fa-file-invoice"></i>
    Z Reading
</a>

</div>


<!-- =========================
     MAIN
========================= -->

<div class="main">


<!-- HEADER -->

<div class="header">


<h1>

Dashboard

</h1>


<div class="admin">

<i class="fas fa-user"></i>

<?= htmlspecialchars($fullname); ?>

</div>


</div>


<!-- WELCOME -->

<div class="welcome">

<h2>

Welcome back,
<?= htmlspecialchars($fullname); ?>!

</h2>

<p>

Here's what's happening in your pharmacy today.

</p>

</div>


<!-- =========================
     SUMMARY CARDS
========================= -->

<div class="cards">


<!-- TOTAL PROFIT -->

<div class="card">

<i class="fas fa-sack-dollar"></i>

<h4>

Total Profit

</h4>

<h2>

₱<?= number_format(
    $totalProfit,
    2
); ?>

</h2>

</div>


<!-- TOTAL PRODUCTS -->

<div class="card">

<i class="fas fa-capsules"></i>

<h4>

Total Products

</h4>

<h2>

<?= $totalProducts; ?>

</h2>

</div>


<!-- STOCK ALERT -->

<div class="card">

<i class="fas fa-triangle-exclamation"></i>

<h4>

Stock Alert

</h4>

<h2>

<?= $stockAlert; ?>

</h2>

</div>


<!-- TODAY'S SALE -->

<div class="card">

<i class="fas fa-receipt"></i>

<h4>

Today's Sale

</h4>

<h2>

₱<?= number_format(
    $todaysSale,
    2
); ?>

</h2>

</div>


</div>


<!-- =========================
     DASHBOARD
========================= -->

<div class="dashboard">


<!-- SALES OVERVIEW -->

<div class="panel">

<h3>

<i class="fas fa-chart-line"></i>

Sales Overview

</h3>


<div class="chart">

<canvas id="salesChart"></canvas>

</div>


</div>


<!-- RECENT TRANSACTIONS -->

<div class="panel">

<h3>

<i class="fas fa-clock"></i>

Recent Transactions

</h3>


<?php if (
    $recentSales &&
    $recentSales->num_rows > 0
): ?>


<?php while (
    $sale =
    $recentSales->fetch_assoc()
): ?>


<div class="transaction">


<div class="transaction-info">


<div class="transaction-icon">

<i class="fas fa-receipt"></i>

</div>


<div>

<div class="transaction-id">

Sale #<?= (int)$sale['sale_id']; ?>

</div>


<div class="transaction-date">

<?= date(
    'M d, Y h:i A',
    strtotime($sale['sale_date'])
); ?>

</div>

</div>


</div>


<div class="price">

₱<?= number_format(
    (float)$sale['total_amount'],
    2
); ?>

</div>


</div>


<?php endwhile; ?>


<?php else: ?>


<div class="transaction">

<span>

No transactions yet.

</span>

</div>


<?php endif; ?>


<!-- TODAY INFO -->

<div class="info-grid">


<div class="info-box">

<span>

Today's Transactions

</span>

<strong>

<?= $todayTransactions; ?>

</strong>

</div>


<div class="info-box">

<span>

Out of Stock

</span>

<strong>

<?= $outOfStock; ?>

</strong>

</div>


</div>


</div>


</div>


</div>


<!-- =========================
     CHART
========================= -->

<script>

const chartLabels =
<?= json_encode($chartLabels); ?>;

const chartData =
<?= json_encode($chartData); ?>;


const ctx =
document.getElementById(
    'salesChart'
);


new Chart(
    ctx,
    {

        type:'line',

        data:{

            labels:chartLabels,

            datasets:[{

                label:'Daily Sales',

                data:chartData,

                borderWidth:3,

                fill:false,

                tension:0.3,

                pointRadius:5

            }]

        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            plugins:{

                legend:{

                    display:true

                }

            },

            scales:{

                y:{

                    beginAtZero:true,

                    ticks:{

                        callback:function(value){

                            return '₱' +
                            Number(value)
                            .toLocaleString();

                        }

                    }

                }

            }

        }

    }
);

</script>


</body>

</html>