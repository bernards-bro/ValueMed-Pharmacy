<?php

require 'db.php';

    if (isset($_GET['id'])){
        $id = $_GET['id'];
        $sql = "DELETE FROM medicines WHERE medicine_id='$id'";

    if ($conn->query($sql)  === TRUE){

        echo "<script> alert('Product deleted successfully!'); 
        window.location.href = 'products.php';</script>";
        exit();
    } else {

        echo "Error deletinng product: " . $conn->error;

        }
    } else {
        header("Location: products.php");
        exit();
    }


?>