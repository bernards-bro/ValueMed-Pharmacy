<?php

require 'connection.php';


$saleId = intval($_GET['sale_id'] ?? 0);


if($saleId <= 0){

    echo json_encode([
        "success"=>false,
        "message"=>"Invalid sale ID"
    ]);

    exit;

}



$stmt = $conn->prepare("

    SELECT

        si.sale_item_id,
        si.medicine_id,
        si.quantity,
        si.unit_price,
        si.subtotal,

        m.name

    FROM sale_items si

    INNER JOIN medicines m

        ON si.medicine_id = m.medicine_id

    WHERE si.sale_id = ?

");



$stmt->bind_param(
    "i",
    $saleId
);



$stmt->execute();



$result =
$stmt->get_result();



$items = [];



while($row = $result->fetch_assoc()){

    $items[] = $row;

}



$stmt->close();



if(empty($items)){


    echo json_encode([

        "success"=>false,

        "message"=>"No items found for this sale."

    ]);


}else{


    echo json_encode([

        "success"=>true,

        "items"=>$items

    ]);

}

?>