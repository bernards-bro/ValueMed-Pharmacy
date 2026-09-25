<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Z Sales | ValueMeds</title>

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
    min-height:100vh;
    background:#16246D;
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

h1{
    color:#16246D;
    margin-bottom:5px;
}

.subtitle{
    color:#555;
    margin-bottom:25px;
}

.card{
    background:white;
    border-radius:15px;
    padding:20px;
    margin-bottom:20px;
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

.box h3{
    color:#16246D;
    font-size:14px;
}

.box p{
    font-size:28px;
    color:#16246D;
    font-weight:bold;
    margin-top:8px;
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

button{
    width:100%;
    padding:12px;
    margin-top:20px;
    background:#16246D;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

button:hover{
    background:#1d2f88;
}

@media print{
    .sidebar,
    button{
        display:none;
    }

    body{
        background:white;
        display:block;
    }

    .main{
        padding:0;
    }
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
    <a href="#" class="active"><i class="fa-solid fa-receipt"></i> Z Sales</a>
    <a href="Refunds.php"><i class="fa-solid fa-arrow-rotate-left"></i> Refunds</a>
    <a href="#"><i class="fa-solid fa-chart-column"></i> Reports</a>
    <a href="#"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

</div>

<div class="main">

    <h1>Z Sales</h1>
    <p class="subtitle">End of Day Sales Report</p>

    <div class="card">

        <h2>Today's Summary</h2>

        <div class="summary">

            <div class="box">
                <h3>Total Transactions</h3>
                <p>65</p>
            </div>

            <div class="box">
                <h3>Total Sales</h3>
                <p>₱12,500</p>
            </div>

            <div class="box">
                <h3>Refunded</h3>
                <p>₱290</p>
            </div>

            <div class="box">
                <h3>Net Sales</h3>
                <p>₱12,210</p>
            </div>

        </div>

    </div>

    <div class="card">

        <h2>Sales Breakdown</h2>

        <table>

            <tr>
                <th>Payment Type</th>
                <th>Amount</th>
            </tr>

            <tr>
                <td>Cash</td>
                <td>₱8,000</td>
            </tr>


            <tr>
                <td><strong>Total Sales</strong></td>
                <td><strong>₱12,500</strong></td>
            </tr>

            <tr>
                <td>Less: Refunds</td>
                <td>-₱290</td>
            </tr>

            <tr>
                <td><strong>Net Sales</strong></td>
                <td><strong>₱12,210</strong></td>
            </tr>

        </table>

        <button onclick="window.print()">
            <i class="fa-solid fa-print"></i> Print Z Sales
        </button>

    </div>

</div>

</body>
</html>
