<?php

require 'connection.php';


if(session_status() === PHP_SESSION_NONE){

    session_start();

}



if(!isset($_SESSION['id'])){

    header("Location: login.php");
    exit;

}



$cashierId = $_SESSION['id'];



$exchangeHistory = [];


$sql = "

SELECT

    e.exchange_id,

    SUM(
        CASE 
        WHEN ei.item_type='Returned'
        THEN ei.quantity
        ELSE 0
        END
    ) AS returned_qty,

    e.original_sale_id,
    e.returned_amount,
    e.replacement_amount,
    e.additional_payment,
    e.status,
    e.exchange_date,

    GROUP_CONCAT(
        CASE 
        WHEN ei.item_type = 'Returned'
        THEN CONCAT(
        m.name,
        ' x',
        ei.quantity
        )
        END
        SEPARATOR ', '
        ) 
        AS returned_items,

    GROUP_CONCAT(
        CASE 
            WHEN ei.item_type = 'Replacement'
            THEN m.name
        END
        SEPARATOR ', '
    ) AS replacement_items

FROM exchanges e

INNER JOIN exchange_items ei

ON e.exchange_id = ei.exchange_id

INNER JOIN medicines m

ON ei.medicine_id = m.medicine_id

GROUP BY e.exchange_id

ORDER BY e.exchange_date DESC

";

$result = $conn->query($sql);


while($row = $result->fetch_assoc()){

    $exchangeHistory[] = $row;

}
$totalReturnedItems = 0;

foreach($exchangeHistory as $exchange){

    $totalReturnedItems +=
        $exchange['returned_qty'];

}
    $totalExchanges =
        count($exchangeHistory);

        $totalAdditionalPayments = 0;

        foreach($exchangeHistory as $exchange){

            $totalAdditionalPayments +=
                $exchange['additional_payment'];

        }

        if(isset($_POST['complete_exchange'])){

    $saleId =
        intval($_POST['sale_id']);

    $returnMedicine =
        intval($_POST['return_item']);

    $returnQty =
        intval($_POST['return_qty']);

    $replacementMedicine =
        intval($_POST['replacement_medicine']);

    $reason =
        trim($_POST['reason'] ?? '');
        
    try{
        
        $conn->begin_transaction();

        $stmt = $conn->prepare("

            SELECT

                si.quantity,
                si.unit_price

            FROM sale_items si

            WHERE si.sale_id = ?

            AND si.medicine_id = ?

        ");

        $stmt->bind_param(
            "ii",
            $saleId,
            $returnMedicine
        );

        $stmt->execute();

        $returnedData =
            $stmt->get_result()
            ->fetch_assoc();

        $stmt->close();

        if(!$returnedData){

            throw new Exception(
                "Returned item was not found in this sale."
            );

        }

        $alreadyReturned = 0;

$stmt = $conn->prepare("

    SELECT
        SUM(ei.quantity) AS returned_qty

    FROM exchange_items ei

    INNER JOIN exchanges e

    ON ei.exchange_id = e.exchange_id

    WHERE e.original_sale_id = ?

    AND ei.medicine_id = ?

    AND ei.item_type = 'Returned'

");

$stmt->bind_param(
    "ii",
    $saleId,
    $returnMedicine
);

$stmt->execute();

$result =
$stmt->get_result()
->fetch_assoc();

$stmt->close();

if($result['returned_qty']){

    $alreadyReturned =
        $result['returned_qty'];

}

$availableReturnQty =
    $returnedData['quantity']
    -
    $alreadyReturned;

    if($returnMedicine == $replacementMedicine){

    throw new Exception(
        "Returned item and replacement item cannot be the same."
    );
    }
if($returnQty > $availableReturnQty){

    throw new Exception(
        "Only "
        .
        $availableReturnQty
        .
        " item(s) can be exchanged."
    );

}

        if($returnQty > $returnedData['quantity']){

            throw new Exception(
                "Return quantity exceeds purchased quantity."
            );

        }

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

        $replacementData =
            $stmt->get_result()
            ->fetch_assoc();


        $stmt->close();

        if(!$replacementData){

            throw new Exception(
                "Replacement item not found."
            );

        }

        if(
            $replacementData['stock'] < 1
        ){

            throw new Exception(
                "Replacement item is out of stock."
            );

        }

        /*
        3. Calculate values
        */

        $returnedValue =
            $returnedData['unit_price']
            *
            $returnQty;

        $replacementValue =
            $replacementData['price'];

        $additionalPayment = 0;

        if($replacementValue > $returnedValue){

            $additionalPayment =
                $replacementValue -
                $returnedValue;

        }

        /*
        4. Insert exchange header
        */

        $stmt = $conn->prepare("

            INSERT INTO exchanges
                (
                    original_sale_id,
                    cashier_id,
                    returned_amount,
                    replacement_amount,
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
            "iiddss",
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
        5. Save returned item
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
            (
                ?,
                ?,
                ?,
                'Returned'
            )

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
        6. Save replacement item
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
            (
                ?,
                ?,
                1,
                'Replacement'
            )

        ");

        $stmt->bind_param(
            "ii",
            $exchangeId,
            $replacementMedicine
        );

        $stmt->execute();

        $stmt->close();

        /*
        7. Return stock
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
        8. Remove replacement stock
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


$_SESSION['last_exchange'] = [

    'exchange_id'=>$exchangeId,

    'sale_id'=>$saleId,

    'returned_item'=>$returnMedicine,

    'replacement_item'=>$replacementMedicine,

    'returned_value'=>$returnedValue,

    'replacement_value'=>$replacementValue,

    'additional_payment'=>$additionalPayment,

    'remaining_credit'=>max(
        0,
        $returnedValue-$replacementValue
    )

];
        $conn->commit();
        $message =
        "Exchange completed. Customer additional payment: ₱"
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

    if(isset($_POST['cancel_exchange'])){
    $exchangeId =
        intval(
            $_POST['cancel_exchange_id']
        );


    $reason =
        "Exchange cancelled";



    try{


        $conn->begin_transaction();



        /*
        Get exchange items
        */


        $stmt=$conn->prepare("

            SELECT
                medicine_id,
                quantity,
                item_type

            FROM exchange_items

            WHERE exchange_id = ?

        ");


        $stmt->bind_param(
            "i",
            $exchangeId
        );


        $stmt->execute();


        $items =
            $stmt->get_result();


        $stmt->close();



        while($item=$items->fetch_assoc()){


            if(
                $item['item_type']
                ===
                'Returned'
            ){

                /*
                Undo returned stock

                Remove the stock
                that was added
                */

                $stmt=$conn->prepare("

                    UPDATE medicines

                    SET stock = stock - ?

                    WHERE medicine_id = ?

                ");


            }
            else{


                /*
                Undo replacement

                Add stock back
                */

                $stmt=$conn->prepare("

                    UPDATE medicines

                    SET stock = stock + ?

                    WHERE medicine_id = ?

                ");


            }



            $stmt->bind_param(
                "ii",
                $item['quantity'],
                $item['medicine_id']
            );


            $stmt->execute();


            $stmt->close();

        }



        /*
        Update status
        */


        $stmt=$conn->prepare("

            UPDATE exchanges

            SET
                status='Cancelled',
                cancelled_by=?,
                cancelled_date=NOW(),
                cancellation_reason=?

            WHERE exchange_id=?

        ");



        $stmt->bind_param(
            "isi",
            $cashierId,
            $reason,
            $exchangeId
        );



        $stmt->execute();


        $stmt->close();



        /*
        Save audit log
        */


        $stmt=$conn->prepare("

            INSERT INTO exchange_logs
            (
                exchange_id,
                action,
                performed_by
            )

            VALUES
            (
                ?,
                'Exchange Cancelled',
                ?
            )

        ");



        $stmt->bind_param(
            "ii",
            $exchangeId,
            $cashierId
        );


        $stmt->execute();


        $stmt->close();



        $conn->commit();


        $message =
        "Exchange cancelled successfully.";
    }
    catch(Exception $e){

        $conn->rollback();

        $error =
            $e->getMessage();

    }

}
$exchangeReceipt = null;


if(isset($_SESSION['last_exchange'])){


    $exchangeReceipt =
        $_SESSION['last_exchange'];



}

$returnName = "";
$replacementName = "";


if($exchangeReceipt){


$stmt=$conn->prepare("

SELECT name

FROM medicines

WHERE medicine_id=?

");


$stmt->bind_param(
    "i",
    $exchangeReceipt['returned_item']
);


$stmt->execute();


$returnName =
$stmt->get_result()
->fetch_assoc()['name'];


$stmt->close();



$stmt=$conn->prepare("

SELECT name

FROM medicines

WHERE medicine_id=?

");


$stmt->bind_param(
    "i",
    $exchangeReceipt['replacement_item']
);


$stmt->execute();


$replacementName =
$stmt->get_result()
->fetch_assoc()['name'];


$stmt->close();


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Item Exchange | ValueMeds</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.3.0/qz-tray.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{
    font-family:"Segoe UI",sans-serif;
    background:#F5F7FC;
    display:flex;

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
/*============================
            main        
=============================*/
.main{
    margin-left:250px;
    width:calc(100% - 250px);
    padding:30px;
}

h1{color:#16246D;margin-bottom:5px;}
.subtitle{color:#555;margin-bottom:25px;}
.card{
    background:white;
    border-radius:15px;
    padding:20px;
    margin-bottom:20px;
}
input,textarea{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
}
textarea{
    resize:none;
    height:90px;
}
button{
    padding:12px;
    background:#16246D;
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
button:hover{
    background:#1d2f88;
}
table{
    width:100%;
    border-collapse:collapse;
}
th{
    background:#16246D;
    color:white;
    padding:15px;
}
td{
    padding:14px 15px;
    border-bottom:1px solid #eee;
    border-bottom:1px solid #ddd;
}
.status{
    background:#DFF5E1;
    color:#176B2C;
    padding:5px 10px;
    border-radius:20px;
    font-size:13px;
    }
.summary{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:15px;
}
.box{
    background:#EEF4FF;
    padding:20px;
    border-radius:10px;
}
.box h3{color:#16246D;font-size:14px;}
.box p{
    font-size:28px;
    color:#16246D;
    font-weight:bold;
    margin-top:8px;
}

.exchange-btn{

background:#16246D;
color:white;
border:none;
padding:12px 20px;
border-radius:10px;
cursor:pointer;

}

.exchange-btn:hover{

background:#2b45b5;

}
.modal{

display:none;

position:fixed;

inset:0;

background:rgba(0,0,0,.5);

align-items:center;

justify-content:center;

z-index:9999;

}



.modal-box{

background:white;

width:500px;

max-width:90%;

padding:25px;

border-radius:15px;

}



.close-btn{

float:right;

border:none;

background:#eee;

font-size:20px;

cursor:pointer;

}



.input-group{

display:flex;

gap:10px;

}



.input-group input{

flex:1;

}



.complete-btn{

margin-top:20px;

background:#16246D;

color:white;

border:none;

padding:12px;

width:100%;

border-radius:8px;

}

.return-item{

background:#EEF4FF;

padding:12px;

border-radius:8px;

margin:10px 0;

}

.cancel-btn{
background:#D62828;
color:white;
border:none;
padding:8px 15px;
border-radius:8px;
cursor:pointer;
}

.status.completed{
background:#DFF5E1;
color:#176B2C;
}

.status.cancelled{
background:#FDECEC;
color:#D62828;
}

.container{
    background:white;
    padding:25px;
    border-radius:18px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.admin{
    background:white;
    padding:10px 18px;
    border-radius:30px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
    font-weight:600;
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
    <a href="refund.php" class="active">
        <i class="fa-solid fa-arrow-right-arrow-left"></i>
        refund
        </a>
</div>

<div class="main">


<div class="header">

    <h1>
        ITEM EXCHANGE
    </h1>


    <div class="admin">

        <i class="fas fa-user"></i>

        <?= htmlspecialchars($_SESSION['fullname'] ?? 'Admin'); ?>

    </div>

</div>



<div class="container">


<p class="subtitle">
Process returned medicines and exchange items.
No cash refund will be provided.
</p>


<button
onclick="openExchangeModal()"
class="exchange-btn">

<i class="fa-solid fa-arrow-right-arrow-left"></i>

Process Exchange

</button>

<div class="card">
<h2>Exchange Summary</h2>

<div class="summary">
<div class="box">

<h3>
Total Exchanges Today
</h3>

<p>
<?= $totalExchanges ?>
</p>

</div>


<div class="box">

<h3>
Additional Payments
</h3>

<p>
₱<?= number_format(
$totalAdditionalPayments,
2
); ?>
</p>

</div>

<div class="box">

<h3>
Items Returned
</h3>

<p>
<?= $totalReturnedItems ?>
</p>

</div>
</div>
</div>

<div class="card">

<h2>
Exchange History
</h2>


<table>

<tr>

<th>
Exchange ID
</th>

<th>
Sale ID
</th>

<th>
Returned Item
</th>

<th>
Replacement Item
</th>

<th>
Extra Paid
</th>

<th>
Status
</th>

<th>
Action
</th>
</tr>



<?php if(!empty($exchangeHistory)): ?>


<?php foreach($exchangeHistory as $exchange): ?>


<tr>


<td>

EX-<?= $exchange['exchange_id']; ?>

</td>


<td>

<?= $exchange['original_sale_id']; ?>

</td>


<td>

<?= htmlspecialchars(
    $exchange['returned_items']
); ?>

</td>


<td>

<?= htmlspecialchars(
    $exchange['replacement_items']
); ?>

</td>


<td>

₱<?= number_format(
    $exchange['additional_payment'],
    2
); ?>

</td>


<td>

<span class="
status 
<?= strtolower($exchange['status']); ?>
">

<?= htmlspecialchars($exchange['status']); ?>

</span>

</td>

<td>

<?php if(
$exchange['status'] === 'Completed'
): ?>

<form method="POST">

<input
type="hidden"
name="cancel_exchange_id"
value="<?= $exchange['exchange_id']; ?>">


<button
name="cancel_exchange"
class="cancel-btn">

Cancel

</button>

</form>

<?php else: ?>

Cancelled

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>

<td colspan="7">

No exchange transactions found.

</td>

</tr>


<?php endif; ?>


</table>


</div>

</div>

</div>
<div 
id="exchangeModal"
class="modal">


<div class="modal-box">


<button
class="close-btn"
onclick="closeExchangeModal()">

×
</button>



<h2>
<i class="fa-solid fa-arrow-right-arrow-left"></i>
Process Exchange
</h2>



<form method="POST">



<label>
Original Sale ID
</label>


<div class="input-group">

<input
type="number"
name="sale_id"
id="sale_id"
placeholder="Enter Sale ID">


<button
type="button"
onclick="loadSale()">

Load

</button>

</div>



<div id="saleItemsArea">


<p>
Enter Sale ID first.
</p>


</div>



<hr>



<h3>
Replacement Item
</h3>


<select
name="replacement_medicine"
id="replacement_medicine"
onchange="calculateExchange()">


<option>
Select replacement
</option>


</select>



<div class="exchange-summary">


<p>
Returned Value:

<strong id="returnedValue">

₱0.00

</strong>

</p>


<p>
Replacement Value:

<strong id="replacementValue">

₱0.00

</strong>

</p>


<p>
Customer Pays Extra:

<strong id="additionalPayment">

₱0.00

</strong>

<p>

Remaining Credit:

<strong id="remainingCredit">

₱0.00

</strong>

</p>

</p>


</div>



<textarea
name="reason"
placeholder="Reason for exchange">
</textarea>



<button
name="complete_exchange"
class="complete-btn">

Complete Exchange

</button>



</form>


</div>


</div>

<script>
async function printExchangeReceipt(){


    if(typeof qz === "undefined"){

        alert(
            "QZ Tray is not loaded."
        );

        return;

    }



    try{


        if(!qz.websocket.isActive()){

            await qz.websocket.connect();

        }



        const printerName =
            "UTAK007";



        const config =
            qz.configs.create(
                printerName,
                {
                    encoding:"UTF-8"
                }
            );



        const ESC =
            "\x1B";



        let data = [];



        function line(){

            return "-".repeat(48)+"\n";

        }



        function row(left,right){


            let width = 44;


            let gap =
                width -
                left.length -
                right.length;



            if(gap < 1)
                gap = 1;



            return (
                left +
                " ".repeat(gap) +
                right +
                "\n"
            );

        }




        data.push(
            ESC+"@"
        );


        // Center header

        data.push(
            ESC+"a"+"\x01"
        );


        data.push(
            "ValueMeds\n"
        );


        data.push(
            "ITEM EXCHANGE RECEIPT\n"
        );


        data.push(
            ESC+"a"+"\x00"
        );


        data.push(
            "\n"
        );



        data.push(
            line()
        );



        data.push(
            row(
                "Exchange ID:",
                "EX-" +
                <?= $exchangeReceipt['exchange_id'] ?? 0 ?>
            )
        );



        data.push(
            row(
                "Original Sale:",
                "#" +
                <?= $exchangeReceipt['sale_id'] ?? 0 ?>
            )
        );



        data.push(
            line()
        );



        data.push(
            "RETURNED ITEM\n"
        );


        data.push(
            "<?= addslashes($returnName ?? '') ?>\n"
        );


        data.push(
            row(
                "Credit:",
                "PHP " +
                "<?= number_format(
                    $exchangeReceipt['returned_value'] ?? 0,
                    2
                ) ?>"
            )
        );



        data.push(
            "\n"
        );



        data.push(
            "REPLACEMENT ITEM\n"
        );


        data.push(
            "<?= addslashes($replacementName ?? '') ?>\n"
        );


        data.push(
            row(
                "Value:",
                "PHP " +
                "<?= number_format(
                    $exchangeReceipt['replacement_value'] ?? 0,
                    2
                ) ?>"
            )
        );



        data.push(
            row(
                "Customer Paid:",
                "PHP " +
                "<?= number_format(
                    $exchangeReceipt['additional_payment'] ?? 0,
                    2
                ) ?>"
            )
        );



        data.push(
            line()
        );


        data.push(
            "\n"
        );


        data.push(
            ESC+"a"+"\x01"
        );


        data.push(
            "COMPLETED\n"
        );


        data.push(
            "Thank you!\n"
        );


        data.push(
            "\n\n\n"
        );

        data.push(
    row(
        "Remaining:",
        "PHP " +
        "<?= number_format(
            $exchangeReceipt['remaining_credit'] ?? 0,
            2
        ) ?>"
    )
);

        await qz.print(
            config,
            data

        );



        console.log(
            "Exchange receipt printed."
        );


    }


    catch(error){

        console.error(error);


        alert(
            "Unable to print exchange receipt."
        );

    }

}

function openExchangeModal(){

document.getElementById(
"exchangeModal"
)
.style.display="flex";

}



function closeExchangeModal(){

document.getElementById(
"exchangeModal"
)
.style.display="none";

}
function loadSale(){


    const saleId =
        document.getElementById(
            "sale_id"
        ).value;



    if(!saleId){

        alert(
            "Enter Sale ID first."
        );

        return;

    }



    fetch(
        "get_sale_items.php?sale_id=" + saleId
    )


    .then(response =>
        response.json()
    )


    .then(data => {



        const area =
            document.getElementById(
                "saleItemsArea"
            );



        if(!data.success){


            area.innerHTML =
            `
            <p>
            ${data.message}
            </p>
            `;


            return;

        }




        let html = `

        <h3>
        Returned Item
        </h3>
        html +=



<label>
Return Quantity
</label>


<input

type="number"

id="return_qty"

name="return_qty"

value="1"

min="1"

oninput="calculateExchange()"

>


;
        ;



        data.items.forEach(item=>{


            html +=

            `

            <div class="return-item">


            <input

            type="radio"

            name="return_item"

            value="${item.medicine_id}"

            data-price="${item.unit_price}"

            data-max="${item.quantity}"

            onchange="calculateExchange()"

            >


            <strong>

            ${item.name}

            </strong>


            <br>


            Purchased:

            ${item.quantity}


            <br>


            Price:

            ₱${parseFloat(item.unit_price).toFixed(2)}


            </div>


            `;


        });



        area.innerHTML = html;



        loadReplacementItems();


    })

    .catch(error=>{


        console.error(error);


        alert(
            "Unable to load sale."
        );


    });


}

function loadReplacementItems(){


    fetch(
        "get_medicines.php"
    )

    .then(response =>
        response.json()
    )

    .then(data=>{


        const select =
            document.getElementById(
                "replacement_medicine"
            );


        select.innerHTML =
        `
        <option value="">
        Select replacement item
        </option>
        `;



        data.medicines.forEach(item=>{


            select.innerHTML +=

            `

            <option

            value="${item.medicine_id}"

            data-price="${item.price}"

            >

            ${item.name}
            -
            ₱${parseFloat(item.price).toFixed(2)}

            </option>


            `;


        });



    });


}

function calculateExchange(){


    const returned =
        document.querySelector(
            'input[name="return_item"]:checked'
        );


    const replacement =
        document.getElementById(
            "replacement_medicine"
        );



    if(!returned || !replacement.value){

        document.getElementById(
            "returnedValue"
        ).innerHTML =
        "₱0.00";


        document.getElementById(
            "replacementValue"
        ).innerHTML =
        "₱0.00";


        document.getElementById(
            "additionalPayment"
        ).innerHTML =
        "₱0.00";


        return;

    }



    const qty =
        parseInt(
            document.getElementById(
                "return_qty"
            ).value
        ) || 1;



    const returnPrice =
        parseFloat(
            returned.dataset.price
        );



    const replacementPrice =
        parseFloat(
            replacement.options[
                replacement.selectedIndex
            ].dataset.price
        );



    const returnedValue =
        returnPrice * qty;



    const difference =
        replacementPrice - returnedValue;



    document.getElementById(
        "returnedValue"
    ).innerHTML =

    "₱" +
    returnedValue.toFixed(2);



    document.getElementById(
        "replacementValue"
    ).innerHTML =

    "₱" +
    replacementPrice.toFixed(2);



    if(difference > 0){


    document.getElementById(
        "additionalPayment"
    ).innerHTML =
        "₱" +
        difference.toFixed(2);


    document.getElementById(
        "remainingCredit"
    ).innerHTML =
        "₱0.00";


}
else{


    document.getElementById(
        "additionalPayment"
    ).innerHTML =
        "₱0.00";


    document.getElementById(
        "remainingCredit"
    ).innerHTML =
        "₱" +
        Math.abs(difference).toFixed(2);


}


function closeExchangeReceipt(){

document.getElementById(
"exchangeReceiptModal"
)
.style.display="none";

}


</script>
<?php if($exchangeReceipt): ?>


<div 
id="exchangeReceiptModal"
class="modal"
style="display:flex;">


<div class="modal-box">


<h2 style="text-align:center">

ValueMeds

</h2>


<h3 style="text-align:center">

Item Exchange Receipt

</h3>


<hr>


<p>

Exchange ID:

<strong>
EX-<?= $exchangeReceipt['exchange_id']; ?>
</strong>

</p>


<p>

Original Sale:

<strong>
#<?= $exchangeReceipt['sale_id']; ?>
</strong>

</p>


<hr>


<p>

Returned Item:

<br>

<strong>

<?= htmlspecialchars($returnName); ?>

</strong>

</p>


<p>

Exchange Credit:

<strong>

₱<?= number_format(
$exchangeReceipt['returned_value'],
2
); ?>

</strong>

</p>


<hr>


<p>

Replacement Item:

<br>

<strong>

<?= htmlspecialchars($replacementName); ?>

</strong>

</p>


<p>

Replacement Value:

<strong>

₱<?= number_format(
$exchangeReceipt['replacement_value'],
2
); ?>

</strong>

</p>
<p>

Additional Payment:

<strong>

₱<?= number_format(
$exchangeReceipt['additional_payment'],
2
); ?>

</strong>

</p>
<hr>

<h3 style="text-align:center">

Completed

</h3>
<?php if(
$exchangeReceipt['remaining_credit'] > 0
): ?>

<p>

Remaining Credit:

<strong>

₱<?= number_format(
$exchangeReceipt['remaining_credit'],
2
); ?>

</strong>

</p>

<?php endif; ?>

<button onclick="printExchangeReceipt()">

Print Receipt

</button>


<button onclick="closeExchangeReceipt()">

Done

</button>

</div>

</div>

<?php endif; ?>
<?php unset($_SESSION['last_exchange']); ?>
</body>
</html>