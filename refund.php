<?php
require 'connection.php';

$message = "";

if(isset($_POST['refund'])){

    $sale_id = intval($_POST['sale_id']);
    $reason = trim($_POST['reason']);

    $conn->begin_transaction();

    try{

        $stmt = $conn->prepare("
            SELECT medicine_id, quantity, total_amount, status
            FROM sales
            WHERE sale_id=?
        ");
        $stmt->bind_param("i",$sale_id);
        $stmt->execute();

        $sale = $stmt->get_result()->fetch_assoc();

        if(!$sale){
            throw new Exception("Sale ID not found.");
        }

        if($sale['status']=="Refunded"){
            throw new Exception("This transaction is already refunded.");
        }

        $medicine_id = $sale['medicine_id'];
        $quantity = $sale['quantity'];
        $amount = $sale['total_amount'];

        $stmt = $conn->prepare("
            UPDATE medicines
            SET stock_quantity = stock_quantity + ?
            WHERE medicine_id = ?
        ");
        $stmt->bind_param("ii",$quantity,$medicine_id);
        $stmt->execute();

        $stmt = $conn->prepare("
            INSERT INTO refunds
            (sale_id, medicine_id, quantity, refund_amount, reason)
            VALUES(?,?,?,?,?)
        ");
        $stmt->bind_param("iiids",$sale_id,$medicine_id,$quantity,$amount,$reason);
        $stmt->execute();

        $stmt = $conn->prepare("
            UPDATE sales
            SET status='Refunded'
            WHERE sale_id=?
        ");
        $stmt->bind_param("i",$sale_id);
        $stmt->execute();

        $conn->commit();

        $message="Refund processed successfully.";

    }catch(Exception $e){

        $conn->rollback();
        $message=$e->getMessage();
    }
}

$history = $conn->query("
SELECT r.refund_id,
       r.sale_id,
       m.medicine_name,
       r.quantity,
       r.refund_amount,
       r.reason,
       r.refund_date
FROM refunds r
JOIN medicines m
ON r.medicine_id=m.medicine_id
ORDER BY r.refund_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Refunds | ValueMeds</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
font-family:"Segoe UI",sans-serif;
background:#F5F7FC;
display:flex;
}

.sidebar{
width:250px;
min-height:100vh;
background:#16246D;
padding:20px;
color:white;
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
color:#666;
margin-bottom:20px;
}

.card{
background:white;
padding:20px;
border-radius:15px;
margin-bottom:20px;
box-shadow:0 2px 8px rgba(0,0,0,.08);
}

input,
textarea{
width:100%;
padding:12px;
margin:10px 0;
border:1px solid #ccc;
border-radius:8px;
font-size:15px;
}

textarea{
height:80px;
resize:none;
}

button{
width:100%;
padding:12px;
background:#16246D;
color:white;
border:none;
border-radius:8px;
cursor:pointer;
font-size:15px;
}

button:hover{
background:#1d2f88;
}

.success{
background:#E8F5E9;
color:#2E7D32;
padding:12px;
border-radius:8px;
margin-bottom:20px;
}

.error{
background:#FDECEC;
color:#C62828;
padding:12px;
border-radius:8px;
margin-bottom:20px;
}

table{
width:100%;
border-collapse:collapse;
margin-top:15px;
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

</style>

</head>
<body>

<div class="sidebar">

<div class="logo">ValueMeds</div>

<a href="dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
<a href="sales.php"><i class="fa-solid fa-cart-shopping"></i> Sales</a>
<a href="products.php"><i class="fa-solid fa-pills"></i> Products</a>
<a href="inventory.php"><i class="fa-solid fa-warehouse"></i> Inventory</a>
<a href="sales_history.php"><i class="fa-solid fa-clock-rotate-left"></i> Sales History</a>
<a href="z_sales.php"><i class="fa-solid fa-receipt"></i> Z Sales</a>
<a href="refunds.php" class="active"><i class="fa-solid fa-arrow-rotate-left"></i> Refunds</a>
<a href="reports.php"><i class="fa-solid fa-chart-column"></i> Reports</a>
<a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>

</div>

<div class="main">

<h1>Refunds</h1>
<p class="subtitle">Process returned items and automatically restore inventory.</p>

<?php if($message!=""){ ?>

<div class="<?= strpos($message,'successfully')!==false ? 'success':'error' ?>">
<?= $message ?>
</div>

<?php } ?>

<div class="card">

<h2><i class="fa-solid fa-arrow-rotate-left"></i> Process Refund</h2>

<form method="POST">

<label>Sale ID</label>
<input type="number" name="sale_id" placeholder="Enter Sale ID" required>

<label>Reason</label>
<textarea name="reason" placeholder="Reason for refund" required></textarea>

<button name="refund">
<i class="fa-solid fa-check"></i> Process Refund
</button>

</form>

</div>

<div class="card">

<h2>Refund History</h2>

<table>

<tr>
<th>Sale ID</th>
<th>Medicine</th>
<th>Qty</th>
<th>Amount</th>
<th>Reason</th>
<th>Status</th>
<th>Date</th>
</tr>

<?php while($row=$history->fetch_assoc()){ ?>

<tr>

<td><?= $row['sale_id'] ?></td>

<td><?= $row['medicine_name'] ?></td>

<td><?= $row['quantity'] ?></td>

<td>₱<?= number_format($row['refund_amount'],2) ?></td>

<td><?= htmlspecialchars($row['reason']) ?></td>

<td><span class="status">Refunded</span></td>

<td><?= date("M d, Y h:i A",strtotime($row['refund_date'])) ?></td>

</tr>

<?php } ?>

</table>

</div>

</div>

</body>
</html>
