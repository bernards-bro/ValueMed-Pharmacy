<?php

require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $product_id = $_POST["product_id"];
    $product_name = $_POST ["product_name"];
    $product_code = $_POST ["product_code"];
    $category = $_POST ["category"];
    $price = $_POST ["price"];
    $stock = $_POST ["stock"];
    $expiry_date = $_POST ["expiry_date"];

    $sql = "UPDATE products SET 
        product_name='$product_name',
        product_code='$product_code',
        category='$category',
        price='$price',
        stock='$stock',
        expiry_date='$expiry_date'
        WHERE product_id='$product_id'";
    
    if ($conn->query($sql) === TRUE ){

        echo "<script> alert('Product updated successfully!');
        window.location.href = 'products.php';</script>";

        exit();

    } else {

        echo "Error updating product: " . $conn->error;

        }

    } else {
        header("Location: products.php");
        exit();
}
?>