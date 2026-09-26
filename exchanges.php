<?php

require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['id'])) {

    header("Location: login.php");
    exit;

}


$cashierId = $_SESSION['id'];

$message = "";
$error = "";


$saleId = intval($_GET['sale_id'] ?? 0);

$selectedSale = null;
$saleItems = [];



/*
|--------------------------------------------------------------------------
| Load Sale
|--------------------------------------------------------------------------
*/


if ($saleId > 0) {


    $stmt = $conn->prepare("
        SELECT
            s.sale_id,
            s.sale_date,
            s.total_amount
        FROM sales s
        WHERE s.sale_id = ?
    ");


    $stmt->bind_param(
        "i",
        $saleId
    );


    $stmt->execute();


    $selectedSale =
        $stmt->get_result()
        ->fetch_assoc();


    $stmt->close();



    if ($selectedSale) {


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



        while($row = $result->fetch_assoc()) {

            $saleItems[] = $row;

        }


        $stmt->close();

    }

}



/*
|--------------------------------------------------------------------------
| Complete Exchange
|--------------------------------------------------------------------------
*/


if(isset($_POST['complete_exchange'])) {


    $saleId =
        intval($_POST['sale_id']);


    $returnMedicine =
        intval($_POST['return_medicine']);


    $returnQty =
        intval($_POST['return_qty']);


    $replacementMedicine =
        intval($_POST['replacement_medicine']);


    $reason =
        trim($_POST['reason']);



    try {


        $conn->begin_transaction();



        /*
        Get returned item price
        */


        $stmt = $conn->prepare("
            SELECT
                price,
                stock

            FROM medicines

            WHERE medicine_id = ?

        ");


        $stmt->bind_param(
            "i",
            $returnMedicine
        );


        $stmt->execute();


        $returnedItem =
            $stmt->get_result()
            ->fetch_assoc();


        $stmt->close();



        /*
        Get replacement item
        */


        $stmt = $conn->prepare("
            SELECT
                price,
                stock

            FROM medicines

            WHERE medicine_id = ?

        ");


        $stmt->bind_param(
            "i",
            $replacementMedicine
        );


        $stmt->execute();


        $replacementItem =
            $stmt->get_result()
            ->fetch_assoc();


        $stmt->close();



        if(!$returnedItem || !$replacementItem){

            throw new Exception(
                "Invalid item selected."
            );

        }



        $returnedValue =
            $returnedItem['price']
            *
            $returnQty;



        $replacementValue =
            $replacementItem['price'];



        $additionalPayment = 0;


        if($replacementValue > $returnedValue){

            $additionalPayment =
                $replacementValue -
                $returnedValue;

        }



        /*
        Save exchange
        */


        $stmt = $conn->prepare("
            INSERT INTO exchanges
            (
                original_sale_id,
                cashier_id,
                returned_value,
                replacement_value,
                additional_payment,
                reason
            )

            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )

        ");


        $stmt->bind_param(
            "iiddis",
            $saleId,
            $cashierId,
            $returnedValue,
            $replacementValue,
            $additionalPayment,
            $reason
        );


        $stmt->execute();


        $exchangeId =
            $conn->insert_id;


        $stmt->close();



        /*
        Save returned item
        */


        $stmt = $conn->prepare("
            INSERT INTO exchange_items
            (
                exchange_id,
                medicine_id,
                quantity,
                item_type
            )

            VALUES
            (?,?,?,'Returned')

        ");


        $stmt->bind_param(
            "iii",
            $exchangeId,
            $returnMedicine,
            $returnQty
        );


        $stmt->execute();


        $stmt->close();



        /*
        Save replacement item
        */


        $stmt = $conn->prepare("
            INSERT INTO exchange_items
            (
                exchange_id,
                medicine_id,
                quantity,
                item_type
            )

            VALUES
            (?,?,1,'Replacement')

        ");


        $stmt->bind_param(
            "ii",
            $exchangeId,
            $replacementMedicine
        );


        $stmt->execute();


        $stmt->close();



        /*
        Inventory:
        Return item + stock
        */


        $stmt = $conn->prepare("
            UPDATE medicines

            SET stock = stock + ?

            WHERE medicine_id = ?

        ");


        $stmt->bind_param(
            "ii",
            $returnQty,
            $returnMedicine
        );


        $stmt->execute();


        $stmt->close();



        /*
        Replacement item - stock
        */


        $stmt = $conn->prepare("
            UPDATE medicines

            SET stock = stock - 1

            WHERE medicine_id = ?

        ");


        $stmt->bind_param(
            "i",
            $replacementMedicine
        );


        $stmt->execute();


        $stmt->close();



        $conn->commit();


        $message =
            "Exchange completed. Additional payment: ₱"
            .
            number_format(
                $additionalPayment,
                2
            );


    }


    catch(Exception $e){

        $conn->rollback();

        $error =
            $e->getMessage();

    }

}



?>


<!DOCTYPE html>

<html>

<head>

<title>
ValueMeds Exchange
</title>


<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


</head>


<body>


<h1>
Item Exchange
</h1>


<?php if($message): ?>

<p>
<?= $message ?>
</p>

<?php endif; ?>


<?php if($error): ?>

<p>
<?= $error ?>
</p>

<?php endif; ?>


<form method="GET">

<label>
Sale ID
</label>

<input
type="number"
name="sale_id">


<button>
Load Sale
</button>

</form>



<?php if($selectedSale): ?>


<h2>
Sale #<?= $selectedSale['sale_id']; ?>
</h2>


<form method="POST">


<input
type="hidden"
name="sale_id"
value="<?= $saleId ?>">



<h3>
Returned Item
</h3>


<select 
name="replacement_medicine"
id="replacement_medicine"
onchange="calculateExchange()">


<?php foreach($saleItems as $item): ?>

<option
value="<?= $item['medicine_id']; ?>">

<?= $item['name']; ?>

</option>

<?php endforeach; ?>


</select>


<input
type="number"
name="return_qty"
value="1"
min="1">



<h3>
Replacement Item
</h3>


<select name="replacement_medicine">


<?php

$result =
$conn->query("
SELECT medicine_id,name,price
FROM medicines
ORDER BY name
");


while($row=$result->fetch_assoc()):

?>


<option value="<?= $row['medicine_id']; ?>">

<?= $row['name']; ?>
-
₱<?= number_format($row['price'],2); ?>

</option>


<?php endwhile; ?>


</select>



<textarea
name="reason"
placeholder="Reason for exchange">
</textarea>


<button
name="complete_exchange">

Complete Exchange

</button>


</form>


<?php endif; ?>


</body>

</html>