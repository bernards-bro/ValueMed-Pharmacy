<?php

require 'connection.php';


$result = $conn->query("

    SELECT

        medicine_id,
        name,
        price,
        stock

    FROM medicines

    WHERE stock > 0

    ORDER BY name ASC

");


$medicines = [];


while($row = $result->fetch_assoc()){

    $medicines[] = $row;

}



echo json_encode([

    "success" => true,

    "medicines" => $medicines

]);


?>