<?php
require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   DELETE MEDICINE
========================= */
if (isset($_POST['delete'])) {
    $id = intval($_POST['id']);

    $stmt = $conn->prepare("
        DELETE FROM medicines
        WHERE medicine_id = ?
    ");

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $message = "Medicine deleted successfully.";

    } else {
        $error = "Unable to delete medicine. It may already be used in a sale or stock record.";
    }
    $stmt->close();
}


/* =========================
   UPDATE STOCK
========================= */
if (isset($_POST['update_stock'])) {
    $id = intval($_POST['id']);
    $stock = intval($_POST['stock']);

    if ($stock < 0) {
        $error = "Stock cannot be negative.";

    } else {
        $stmt = $conn->prepare("
            UPDATE medicines
            SET stock = ?
            WHERE medicine_id = ?
        ");

        $stmt->bind_param("ii", $stock, $id);

        if ($stmt->execute()) {
            $message = "Stock updated successfully.";
        } else {
            $error = "Failed to update stock.";
        }
        $stmt->close();
    }
}

/* =========================
   SEARCH
========================= */
$search = "";
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
}

/* =========================
   GET MEDICINES
========================= */
if ($search !== "") {
    $stmt = $conn->prepare("
        SELECT
            medicine_id,
            name,
            category,
            stock,
            expiry_date
        FROM medicines
        WHERE name LIKE ?
        OR category LIKE ?
        ORDER BY name ASC
    ");

    $searchTerm = "%" . $search . "%";

    $stmt->bind_param(
        "ss",
        $searchTerm,
        $searchTerm
    );

    $stmt->execute();

    $medicines = $stmt->get_result();

} else {
    $medicines = $conn->query("
        SELECT
            medicine_id,
            name,
            category,
            stock,
            expiry_date
        FROM medicines
        ORDER BY name ASC
    ");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Inventory | ValueMeds</title>

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
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    font-weight:600;
}


/* =========================
   CONTAINER
========================= */

.container{
    background:white;
    padding:25px;
    border-radius:18px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}


/* =========================
   TOP SECTION
========================= */

.top-section{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.top-section h2{
    color:#16246D;
}


/* =========================
   SEARCH
========================= */

.search-form{
    display:flex;
    gap:10px;
}

.search-form input{
    width:300px;
    padding:12px 15px;
    border:1px solid #ccc;
    border-radius:10px;
    outline:none;
}

.search-form button{
    background:#16246D;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:10px;
    cursor:pointer;
}

.search-form button:hover{
    background:#2b45b5;
}


/* =========================
   MESSAGES
========================= */

.message{
    background:#dff5e1;
    color:#1b6b2a;
    padding:12px 15px;
    border-radius:10px;
    margin-bottom:20px;
}

.error{
    background:#ffe0e0;
    color:#a00000;
    padding:12px 15px;
    border-radius:10px;
    margin-bottom:20px;
}


/* =========================
   TABLE
========================= */

.table-wrapper{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#16246D;
    color:white;
    padding:15px;
    text-align:left;
}

td{
    padding:14px 15px;
    border-bottom:1px solid #eee;
}

tr:hover{
    background:#F5F7FC;
}


/* =========================
   STATUS
========================= */

.status{
    padding:6px 12px;
    border-radius:20px;
    font-size:13px;
    font-weight:bold;
}

.in-stock{
    background:#dff5e1;
    color:#1b6b2a;
}

.low-stock{
    background:#fff0c2;
    color:#856404;
}

.out-stock{
    background:#ffe0e0;
    color:#a00000;
}


/* =========================
   ACTION BUTTONS
========================= */

.actions{
    display:flex;
    gap:8px;
}

.edit-btn,
.delete-btn{
    border:none;
    padding:8px 12px;
    border-radius:8px;
    cursor:pointer;
}

.edit-btn{
    background:#e7efff;
    color:#16246D;
}

.delete-btn{
    background:#ffe0e0;
    color:#a00000;
}


/* =========================
   EDIT FORM
========================= */

.edit-form{
    display:flex;
    gap:5px;
    align-items:center;
}

.edit-form input{
    width:80px;
    padding:7px;
    border:1px solid #ccc;
    border-radius:7px;
}

.edit-form button{
    background:#16246D;
    color:white;
    border:none;
    padding:7px 10px;
    border-radius:7px;
    cursor:pointer;
}


/* =========================
   RESPONSIVE
========================= */
@media(max-width:900px){

    .sidebar{
        width:200px;
    }
    .main{
        margin-left:200px;
        width:calc(100% - 200px);
        padding:20px;
    }
    .top-section{
        flex-direction:column;
        align-items:flex-start;
        gap:15px;
    }
    .search-form input{
        width:250px;
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

    <a href="inventory.php" class="active">
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
</div>


<!-- =========================
     MAIN
========================= -->

<div class="main">
    <div class="header">
        <h1>INVENTORY</h1>
        <div class="admin">
            <i class="fas fa-user"></i>
            Admin
        </div>
    </div>

    <div class="container">
        <div class="top-section">
            <h2>
                <i class="fas fa-boxes"></i>
                Medicine Inventory
            </h2>


            <!-- SEARCH -->
            <form method="GET" class="search-form">

                <input
                    type="text"
                    name="search"
                    placeholder="Search medicine..."
                    value="<?= htmlspecialchars($search) ?>">

                <button type="submit">
                    <i class="fas fa-search"></i>
                    Search
                </button>
            </form>
        </div>


        <!-- MESSAGES -->
        <?php if(isset($message)): ?>
            <div class="message">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>


        <!-- =========================
             MEDICINE TABLE
        ========================= -->
        <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Medicine</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Expiration</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($medicines->num_rows > 0): 
                ?>
                    <?php 
                    while ($medicine = $medicines->fetch_assoc()): 
                    ?>
                        <?php
                        /* =========================
                           DETERMINE STOCK STATUS
                        ========================= */
                        $stock = (int)$medicine['stock'];

                        if ($stock == 0) {
                            $status = "Out of Stock";
                            $statusClass = "out-stock";
                        } 
                        
                        elseif ($stock <= 20) {
                            $status = "Low Stock";
                            $statusClass = "low-stock";
                        } 

                        else {
                            $status = "In Stock";
                            $statusClass = "in-stock";
                        }

                        /* =========================
                           EXPIRY DATE
                        ========================= */
                        if (!empty($medicine['expiry_date'])) {
                            $expiryDate = date(
                                "M d, Y",
                                strtotime($medicine['expiry_date'])
                            );
                        } 

                        else {
                            $expiryDate = "No expiry date";
                        }
                        ?>

                        <tr>
                            <!-- ID -->
                            <td>
                                <?= (int)$medicine['medicine_id'] ?>
                            </td>

                            <!-- MEDICINE NAME -->
                            <td>
                                <strong>
                                    <?= htmlspecialchars($medicine['name']) ?>
                                </strong>
                            </td>

                            <!-- CATEGORY -->
                            <td>
                                <?= htmlspecialchars($medicine['category'] ?? 'N/A') ?>
                            </td>

                            <!-- STOCK -->
                            <td>
                                <form method="POST" class="edit-form">

                                    <input
                                        type="number"
                                        name="stock"
                                        min="0"
                                        value="<?= $stock ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int)$medicine['medicine_id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="update_stock"
                                        title="Update Stock"
                                    >
                                        <i class="fas fa-save"></i>
                                    </button>
                                </form>
                            </td>

                            <!-- STATUS -->
                            <td>
                                <span class="status <?= $statusClass ?>">
                                    <?= $status ?>
                                </span>
                            </td>

                            <!-- EXPIRATION -->
                            <td>
                                <?= htmlspecialchars($expiryDate) ?>
                            </td>

                            <!-- ACTIONS -->
                            <td>
                                <div class="actions">

                                    <!-- DELETE -->
                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this medicine?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$medicine['medicine_id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            name="delete"
                                            class="delete-btn"
                                            title="Delete Medicine"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php
                    endwhile; 
                    ?>
                <?php 
                else: 
                ?>
                    <tr>
                        <td
                           colspan="7"
                            style="text-align:center;padding:30px;"
                        >
                            No medicines found.
                        </td>
                    </tr>
                <?php
                endif;
                ?>
                </tbody>
        </table>
        </div>
    </div>
</div>
</body>
</html>