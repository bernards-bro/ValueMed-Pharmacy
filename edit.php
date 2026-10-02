<?php

require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Get Product ID
|--------------------------------------------------------------------------
*/

if (isset($_GET['id'])) {

    $medicine_id = intval($_GET['id']);

} elseif (isset($_POST['medicine_id'])) {

    $medicine_id = intval($_POST['medicine_id']);

} else {

    header("Location: products.php");
    exit();

}


/*
|--------------------------------------------------------------------------
| Update Product
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? '');
    $description = trim($_POST["description"] ?? '');
    $category = trim($_POST["category"] ?? '');

    $cost_price = floatval($_POST["cost_price"] ?? 0);
    $price = floatval($_POST["price"] ?? 0);

    $stock = intval($_POST["stock"] ?? 0);

    $expiry_date = $_POST["expiry_date"] ?? '';


    /*
     * Basic validation
     */

    if ($name === '') {

        $error = "Product name is required.";

    } elseif ($price < 0 || $cost_price < 0) {

        $error = "Price cannot be negative.";

    } elseif ($stock < 0) {

        $error = "Stock cannot be negative.";

    } elseif ($expiry_date === '') {

        $error = "Expiration date is required.";

    } else {

        /*
         * Update the selected medicine.
         */

        $stmt = $conn->prepare("
            UPDATE medicines
            SET
                name = ?,
                description = ?,
                category = ?,
                price = ?,
                cost_price = ?,
                stock = ?,
                expiry_date = ?
            WHERE medicine_id = ?
        ");

        $stmt->bind_param(
            "sssddisi",
            $name,
            $description,
            $category,
            $price,
            $cost_price,
            $stock,
            $expiry_date,
            $medicine_id
        );


        if ($stmt->execute()) {

            $stmt->close();

            echo "<script>
                    alert('Product updated successfully!');
                    window.location.href = 'products.php';
                  </script>";

            exit();

        } else {

            $error = "Error updating product: " . $stmt->error;

            $stmt->close();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Get Existing Product
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        medicine_id,
        name,
        description,
        category,
        price,
        cost_price,
        stock,
        expiry_date
    FROM medicines
    WHERE medicine_id = ?
");

$stmt->bind_param("i", $medicine_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    echo "Product not found.";
    exit();

}


$medicine = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Product</title>

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


/* =========================
   MAIN
========================= */

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


/* =========================
   FORM
========================= */

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
    resize:vertical;
    min-height:100px;
}

.group input:focus,
.group select:focus,
.group textarea:focus{
    border-color:#16246D;
}


/* =========================
   ERROR
========================= */

.error{
    background:#fde2e2;
    color:#a51d1d;
    padding:12px 15px;
    border-radius:10px;
    margin-bottom:20px;
}


/* =========================
   BUTTONS
========================= */

.buttons{
    display:flex;
    justify-content:flex-end;
    gap:15px;
    margin-top:25px;
}

.cancel{
    background:#ccc;
    color:#333;
    padding:12px 25px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    text-decoration:none;
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
    }

    .row{
        grid-template-columns:1fr;
    }

}


@media(max-width:650px){

    .sidebar{
        display:none;
    }

    .main{
        margin-left:0;
        width:100%;
        padding:15px;
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

    <a href="products.php" class="active">
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
</div>


<!-- =========================
     MAIN
========================= -->

<div class="main">


    <div class="header">

        <h1>
            Edit Product
        </h1>


        <div class="admin">

            <i class="fas fa-user"></i>

            <?= htmlspecialchars(
                $_SESSION['fullname'] ?? 'Admin'
            ); ?>

        </div>

    </div>


    <div class="form-box">


        <?php if (isset($error)): ?>

            <div class="error">

                <i class="fas fa-circle-exclamation"></i>

                <?= htmlspecialchars($error); ?>

            </div>

        <?php endif; ?>


        <form method="POST">


            <input
                type="hidden"
                name="medicine_id"
                value="<?= htmlspecialchars(
                    $medicine['medicine_id']
                ); ?>"
            >


            <!-- PRODUCT NAME + DESCRIPTION -->

            <div class="row">


                <div class="group">

                    <label>
                        Product Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars(
                            $medicine['name']
                        ); ?>"
                        required
                    >

                </div>


                <div class="group">

                    <label>
                        Product Description
                    </label>

                    <input
                        type="text"
                        name="description"
                        value="<?= htmlspecialchars(
                            $medicine['description'] ?? ''
                        ); ?>"
                    >

                </div>


            </div>


            <!-- CATEGORY + COST PRICE -->

            <div class="row">


                <div class="group">

                    <label>
                        Category
                    </label>

                    <select
                        name="category"
                        required
                    >

                        <option value="">
                            Select Category
                        </option>


                        <option
                            value="Tablet"
                            <?= $medicine['category'] === 'Tablet'
                                ? 'selected'
                                : ''; ?>
                        >
                            Tablet
                        </option>


                        <option
                            value="Capsule"
                            <?= $medicine['category'] === 'Capsule'
                                ? 'selected'
                                : ''; ?>
                        >
                            Capsule
                        </option>


                        <option
                            value="Syrup"
                            <?= $medicine['category'] === 'Syrup'
                                ? 'selected'
                                : ''; ?>
                        >
                            Syrup
                        </option>


                        <option
                            value="Injection"
                            <?= $medicine['category'] === 'Injection'
                                ? 'selected'
                                : ''; ?>
                        >
                            Injection
                        </option>


                        <option
                            value="Vitamin"
                            <?= $medicine['category'] === 'Vitamin'
                                ? 'selected'
                                : ''; ?>
                        >
                            Vitamin
                        </option>


                    </select>

                </div>


                <div class="group">

                    <label>
                        Cost Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="cost_price"
                        value="<?= htmlspecialchars(
                            $medicine['cost_price']
                        ); ?>"
                        required
                    >

                </div>


            </div>


            <!-- SELLING PRICE + STOCK -->

            <div class="row">


                <div class="group">

                    <label>
                        Selling Price
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="price"
                        value="<?= htmlspecialchars(
                            $medicine['price']
                        ); ?>"
                        required
                    >

                </div>


                <div class="group">

                    <label>
                        Stock
                    </label>

                    <input
                        type="number"
                        min="0"
                        name="stock"
                        value="<?= htmlspecialchars(
                            $medicine['stock']
                        ); ?>"
                        required
                    >

                </div>


            </div>


            <!-- EXPIRATION DATE -->

            <div class="row">


                <div class="group">

                    <label>
                        Expiration Date
                    </label>

                    <input
                        type="date"
                        name="expiry_date"
                        value="<?= htmlspecialchars(
                            $medicine['expiry_date'] ?? ''
                        ); ?>"
                        required
                    >

                </div>


            </div>


            <!-- BUTTONS -->

            <div class="buttons">


                <a
                    href="products.php"
                    class="cancel"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="save"
                >

                    <i class="fas fa-save"></i>

                    Update Product

                </button>


            </div>


        </form>


    </div>


</div>


</body>

</html>