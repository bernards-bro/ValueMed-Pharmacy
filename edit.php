<?php

require 'db.php';

/* Get product ID */
if (isset($_GET['id'])) {

    $product_id = $_GET['id'];

} elseif (isset($_POST['product_id'])) {

    $product_id = $_POST['product_id'];

} else {

    header("Location: products.php");
    exit();

}


/* Update product */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $product_name = $_POST["product_name"];
    $product_code = $_POST["product_code"];
    $category = $_POST["category"];
    $price = $_POST["price"];
    $stock = $_POST["stock"];
    $expiry_date = $_POST["expiry_date"];

    $stmt = $conn->prepare("
        UPDATE products SET
            product_name = ?,
            product_code = ?,
            category = ?,
            price = ?,
            stock = ?,
            expiry_date = ?
        WHERE product_id = ?
    ");

    $stmt->bind_param(
        "sssdiss",
        $product_name,
        $product_code,
        $category,
        $price,
        $stock,
        $expiry_date,
        $product_id
    );

    if ($stmt->execute()) {

        echo "<script>
                alert('Product updated successfully!');
                window.location.href = 'products.php';
              </script>";
        exit();

    } else {

        echo "Error updating product: " . $stmt->error;

    }

}


/* Get existing product */
$stmt = $conn->prepare("
    SELECT *
    FROM products
    WHERE product_id = ?
");

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    echo "Product not found.";
    exit();

}

$product = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Product</title>

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
.group select{
    padding:12px;
    border:1px solid #ccc;
    border-radius:10px;
    outline:none;
    font-size:15px;
}

.group input:focus,
.group select:focus{
    border-color:#16246D;
}

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

    <div class="menu-title">INVENTORY</div>

    <a href="products.php" class="active">
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

        <h1>Edit Product</h1>

        <div class="admin">
            <i class="fas fa-user"></i> Admin
        </div>

    </div>


    <div class="form-box">

        <form method="POST">

            <input
                type="hidden"
                name="product_id"
                value="<?= htmlspecialchars($product['product_id']); ?>"
            >


            <div class="row">

                <div class="group">

                    <label>Product Name</label>

                    <input
                        type="text"
                        name="product_name"
                        value="<?= htmlspecialchars($product['product_name']); ?>"
                        required
                    >

                </div>


                <div class="group">

                    <label>Product Code</label>

                    <input
                        type="text"
                        name="product_code"
                        value="<?= htmlspecialchars($product['product_code']); ?>"
                        required
                    >

                </div>

            </div>


            <div class="row">

                <div class="group">

                    <label>Category</label>

                    <select name="category" required>

                        <option value="">Select Category</option>

                        <option value="Tablet"
                            <?= $product['category'] == 'Tablet' ? 'selected' : ''; ?>>
                            Tablet
                        </option>

                        <option value="Capsule"
                            <?= $product['category'] == 'Capsule' ? 'selected' : ''; ?>>
                            Capsule
                        </option>

                        <option value="Syrup"
                            <?= $product['category'] == 'Syrup' ? 'selected' : ''; ?>>
                            Syrup
                        </option>

                        <option value="Injection"
                            <?= $product['category'] == 'Injection' ? 'selected' : ''; ?>>
                            Injection
                        </option>

                        <option value="Vitamin"
                            <?= $product['category'] == 'Vitamin' ? 'selected' : ''; ?>>
                            Vitamin
                        </option>

                    </select>

                </div>


                <div class="group">

                    <label>Price</label>

                    <input
                        type="number"
                        step="0.01"
                        name="price"
                        value="<?= htmlspecialchars($product['price']); ?>"
                        required
                    >

                </div>

            </div>


            <div class="row">

                <div class="group">

                    <label>Stock</label>

                    <input
                        type="number"
                        name="stock"
                        value="<?= htmlspecialchars($product['stock']); ?>"
                        required
                    >

                </div>


                <div class="group">

                    <label>Expiration Date</label>

                    <input
                        type="date"
                        name="expiry_date"
                        value="<?= htmlspecialchars($product['expiry_date']); ?>"
                        required
                    >

                </div>

            </div>


            <div class="buttons">

                <a href="products.php" class="cancel">
                    Cancel
                </a>

                <button type="submit" class="save">
                    <i class="fas fa-save"></i>
                    Update Product
                </button>

            </div>

        </form>

    </div>

</div>

</body>

</html>