<?php

require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$cashierId = (int) $_SESSION['id'];

$exchangeHistory = [];
$message = '';
$error = '';

/*
|--------------------------------------------------------------------------
| COMPLETE EXCHANGE
|--------------------------------------------------------------------------
*/
if (isset($_POST['complete_exchange'])) {

    $saleId = (int) ($_POST['sale_id'] ?? 0);
    $returnMedicine = (int) ($_POST['return_item'] ?? 0);
    $returnQty = (int) ($_POST['return_qty'] ?? 0);
    $replacementMedicine = (int) ($_POST['replacement_medicine'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');

    try {

        if ($saleId <= 0) {
            throw new Exception("Please enter a valid Sale ID.");
        }

        if ($returnMedicine <= 0) {
            throw new Exception("Please select the returned item.");
        }

        if ($returnQty <= 0) {
            throw new Exception("Return quantity must be at least 1.");
        }

        if ($replacementMedicine <= 0) {
            throw new Exception("Please select a replacement item.");
        }

        if ($returnMedicine === $replacementMedicine) {
            throw new Exception(
                "Returned item and replacement item cannot be the same."
            );
        }

        $conn->begin_transaction();

        /*
        |--------------------------------------------------------------------------
        | 1. Get original sale item
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT
                si.quantity,
                si.unit_price
            FROM sale_items si
            WHERE si.sale_id = ?
            AND si.medicine_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            throw new Exception("Unable to prepare sale item query.");
        }

        $stmt->bind_param(
            "ii",
            $saleId,
            $returnMedicine
        );

        $stmt->execute();

        $returnedData = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        if (!$returnedData) {
            throw new Exception(
                "Returned item was not found in this sale."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Check previously returned quantity
        |--------------------------------------------------------------------------
        */

        $alreadyReturned = 0;

        $stmt = $conn->prepare("
            SELECT
                COALESCE(SUM(ei.quantity), 0) AS returned_qty
            FROM exchange_items ei
            INNER JOIN exchanges e
                ON ei.exchange_id = e.exchange_id
            WHERE e.original_sale_id = ?
            AND ei.medicine_id = ?
            AND ei.item_type = 'Returned'
            AND e.status <> 'Cancelled'
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to check previous exchanges."
            );
        }

        $stmt->bind_param(
            "ii",
            $saleId,
            $returnMedicine
        );

        $stmt->execute();

        $previousData = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        $alreadyReturned =
            (int) ($previousData['returned_qty'] ?? 0);

        $purchasedQty =
            (int) $returnedData['quantity'];

        $availableReturnQty =
            $purchasedQty - $alreadyReturned;

        if ($availableReturnQty <= 0) {
            throw new Exception(
                "All purchased quantity for this item has already been exchanged."
            );
        }

        if ($returnQty > $availableReturnQty) {
            throw new Exception(
                "Only " .
                $availableReturnQty .
                " item(s) can be exchanged."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Get replacement medicine
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT
                price,
                stock,
                name
            FROM medicines
            WHERE medicine_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to prepare replacement query."
            );
        }

        $stmt->bind_param(
            "i",
            $replacementMedicine
        );

        $stmt->execute();

        $replacementData = $stmt
            ->get_result()
            ->fetch_assoc();

        $stmt->close();

        if (!$replacementData) {
            throw new Exception(
                "Replacement item not found."
            );
        }

        if ((int) $replacementData['stock'] < 1) {
            throw new Exception(
                "Replacement item is out of stock."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Calculate values
        |--------------------------------------------------------------------------
        */

        $returnedValue =
            (float) $returnedData['unit_price'] *
            $returnQty;

        /*
         * The system currently exchanges the returned quantity
         * for ONE replacement item.
         */
        $replacementValue =
            (float) $replacementData['price'];

        $additionalPayment = 0;

        if ($replacementValue > $returnedValue) {

            $additionalPayment =
                $replacementValue -
                $returnedValue;
        }

        $remainingCredit =
            max(
                0,
                $returnedValue -
                $replacementValue
            );

        /*
        |--------------------------------------------------------------------------
        | 5. Insert exchange header
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO exchanges
            (
                original_sale_id,
                cashier_id,
                returned_amount,
                replacement_amount,
                additional_payment,
                reason,
                status,
                exchange_date
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                'Completed',
                NOW()
            )
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to create exchange record."
            );
        }

        $stmt->bind_param(
            "iiddss",
            $saleId,
            $cashierId,
            $returnedValue,
            $replacementValue,
            $additionalPayment,
            $reason
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to save exchange."
            );
        }

        $exchangeId =
            $conn->insert_id;

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | 6. Save returned item
        |--------------------------------------------------------------------------
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

        if (!$stmt) {
            throw new Exception(
                "Unable to save returned item."
            );
        }

        $stmt->bind_param(
            "iii",
            $exchangeId,
            $returnMedicine,
            $returnQty
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to save returned item."
            );
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | 7. Save replacement item
        |--------------------------------------------------------------------------
        */

        $replacementQty = 1;

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
                'Replacement'
            )
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to save replacement item."
            );
        }

        $stmt->bind_param(
            "iii",
            $exchangeId,
            $replacementMedicine,
            $replacementQty
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to save replacement item."
            );
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | 8. Return stock
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE medicines
            SET stock = stock + ?
            WHERE medicine_id = ?
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to update returned stock."
            );
        }

        $stmt->bind_param(
            "ii",
            $returnQty,
            $returnMedicine
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to update returned stock."
            );
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | 9. Remove replacement stock
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE medicines
            SET stock = stock - ?
            WHERE medicine_id = ?
            AND stock >= ?
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to update replacement stock."
            );
        }

        $stmt->bind_param(
            "iii",
            $replacementQty,
            $replacementMedicine,
            $replacementQty
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to remove replacement stock."
            );
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception(
                "Replacement item is out of stock."
            );
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | 10. Audit log
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO exchange_logs
            (
                exchange_id,
                action,
                performed_by
            )
            VALUES
            (
                ?,
                'Exchange Completed',
                ?
            )
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $exchangeId,
                $cashierId
            );

            $stmt->execute();

            $stmt->close();
        }

        /*
        |--------------------------------------------------------------------------
        | Commit ONLY ONCE
        |--------------------------------------------------------------------------
        */

        $conn->commit();

        $_SESSION['last_exchange'] = [

            'exchange_id' =>
                $exchangeId,

            'sale_id' =>
                $saleId,

            'returned_item' =>
                $returnMedicine,

            'replacement_item' =>
                $replacementMedicine,

            'returned_value' =>
                $returnedValue,

            'replacement_value' =>
                $replacementValue,

            'additional_payment' =>
                $additionalPayment,

            'remaining_credit' =>
                $remainingCredit
        ];

        /*
         * Redirect so refreshing the page does not submit
         * the exchange form again.
         */
        header(
            "Location: " .
            $_SERVER['PHP_SELF'] .
            "?exchange=success"
        );

        exit;

    }
    catch (Exception $e) {

        if ($conn->errno === 0) {
            // Connection is still valid.
        }

        try {
            $conn->rollback();
        }
        catch (Throwable $rollbackError) {
            // Ignore rollback errors.
        }

        $error =
            $e->getMessage();
    }
}


/*
|--------------------------------------------------------------------------
| CANCEL EXCHANGE
|--------------------------------------------------------------------------
*/

if (isset($_POST['cancel_exchange'])) {

    $exchangeId =
        (int) ($_POST['cancel_exchange_id'] ?? 0);

    $reason =
        "Exchange cancelled";

    try {

        if ($exchangeId <= 0) {
            throw new Exception(
                "Invalid exchange ID."
            );
        }

        $conn->begin_transaction();

        /*
        |--------------------------------------------------------------------------
        | Make sure exchange is still completed
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT status
            FROM exchanges
            WHERE exchange_id = ?
            LIMIT 1
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to verify exchange."
            );
        }

        $stmt->bind_param(
            "i",
            $exchangeId
        );

        $stmt->execute();

        $exchangeData =
            $stmt
                ->get_result()
                ->fetch_assoc();

        $stmt->close();

        if (!$exchangeData) {
            throw new Exception(
                "Exchange not found."
            );
        }

        if ($exchangeData['status'] !== 'Completed') {
            throw new Exception(
                "This exchange has already been cancelled."
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Get exchange items
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT
                medicine_id,
                quantity,
                item_type
            FROM exchange_items
            WHERE exchange_id = ?
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to retrieve exchange items."
            );
        }

        $stmt->bind_param(
            "i",
            $exchangeId
        );

        $stmt->execute();

        $items =
            $stmt->get_result();

        /*
        |--------------------------------------------------------------------------
        | Reverse stock
        |--------------------------------------------------------------------------
        */

        while ($item = $items->fetch_assoc()) {

            $medicineId =
                (int) $item['medicine_id'];

            $quantity =
                (int) $item['quantity'];

            if ($quantity <= 0) {
                continue;
            }

            if ($item['item_type'] === 'Returned') {

                /*
                 * Original exchange added this item to stock.
                 * Cancellation removes it again.
                 */

                $stmtStock = $conn->prepare("
                    UPDATE medicines
                    SET stock = stock - ?
                    WHERE medicine_id = ?
                ");

            }
            else {

                /*
                 * Original exchange removed this item from stock.
                 * Cancellation adds it back.
                 */

                $stmtStock = $conn->prepare("
                    UPDATE medicines
                    SET stock = stock + ?
                    WHERE medicine_id = ?
                ");
            }

            if (!$stmtStock) {
                throw new Exception(
                    "Unable to reverse stock."
                );
            }

            $stmtStock->bind_param(
                "ii",
                $quantity,
                $medicineId
            );

            if (!$stmtStock->execute()) {
                throw new Exception(
                    "Unable to reverse stock."
                );
            }

            $stmtStock->close();
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | Update exchange status
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            UPDATE exchanges
            SET
                status = 'Cancelled',
                cancelled_by = ?,
                cancelled_date = NOW(),
                cancellation_reason = ?
            WHERE exchange_id = ?
            AND status = 'Completed'
        ");

        if (!$stmt) {
            throw new Exception(
                "Unable to cancel exchange."
            );
        }

        $stmt->bind_param(
            "isi",
            $cashierId,
            $reason,
            $exchangeId
        );

        if (!$stmt->execute()) {
            throw new Exception(
                "Unable to cancel exchange."
            );
        }

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | Save audit log
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
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

        if ($stmt) {

            $stmt->bind_param(
                "ii",
                $exchangeId,
                $cashierId
            );

            $stmt->execute();

            $stmt->close();
        }

        $conn->commit();

        $message =
            "Exchange cancelled successfully.";

    }
    catch (Exception $e) {

        try {
            $conn->rollback();
        }
        catch (Throwable $rollbackError) {
            // Ignore rollback errors.
        }

        $error =
            $e->getMessage();
    }
}


/*
|--------------------------------------------------------------------------
| LOAD EXCHANGE HISTORY
|--------------------------------------------------------------------------
*/

$sql = "

SELECT

    e.exchange_id,

    COALESCE(
        SUM(
            CASE
                WHEN ei.item_type = 'Returned'
                THEN ei.quantity
                ELSE 0
            END
        ),
        0
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
    ) AS returned_items,

    GROUP_CONCAT(
        CASE
            WHEN ei.item_type = 'Replacement'
            THEN CONCAT(
                m.name,
                ' x',
                ei.quantity
            )
        END
        SEPARATOR ', '
    ) AS replacement_items

FROM exchanges e

INNER JOIN exchange_items ei
    ON e.exchange_id = ei.exchange_id

INNER JOIN medicines m
    ON ei.medicine_id = m.medicine_id

GROUP BY
    e.exchange_id,
    e.original_sale_id,
    e.returned_amount,
    e.replacement_amount,
    e.additional_payment,
    e.status,
    e.exchange_date

ORDER BY
    e.exchange_date DESC

";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $exchangeHistory[] =
            $row;
    }
}


/*
|--------------------------------------------------------------------------
| TODAY'S SUMMARY
|--------------------------------------------------------------------------
*/

$totalReturnedItems = 0;
$totalExchanges = 0;
$totalAdditionalPayments = 0;

foreach ($exchangeHistory as $exchange) {

    if ($exchange['status'] !== 'Completed') {
        continue;
    }

    if (
        !empty($exchange['exchange_date']) &&
        date(
            'Y-m-d',
            strtotime($exchange['exchange_date'])
        ) === date('Y-m-d')
    ) {

        $totalExchanges++;

        $totalReturnedItems +=
            (int) $exchange['returned_qty'];

        $totalAdditionalPayments +=
            (float) $exchange['additional_payment'];
    }
}


/*
|--------------------------------------------------------------------------
| RECEIPT
|--------------------------------------------------------------------------
*/

$exchangeReceipt = null;

if (isset($_SESSION['last_exchange'])) {

    $exchangeReceipt =
        $_SESSION['last_exchange'];
}

$returnName = "";
$replacementName = "";

if ($exchangeReceipt) {

    $stmt = $conn->prepare("
        SELECT name
        FROM medicines
        WHERE medicine_id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $exchangeReceipt['returned_item']
        );

        $stmt->execute();

        $data =
            $stmt
                ->get_result()
                ->fetch_assoc();

        $returnName =
            $data['name'] ?? '';

        $stmt->close();
    }


    $stmt = $conn->prepare("
        SELECT name
        FROM medicines
        WHERE medicine_id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param(
            "i",
            $exchangeReceipt['replacement_item']
        );

        $stmt->execute();

        $data =
            $stmt
                ->get_result()
                ->fetch_assoc();

        $replacementName =
            $data['name'] ?? '';

        $stmt->close();
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
 name="viewport"
 content="width=device-width, initial-scale=1.0">

<title>
    Item Exchange | ValueMeds
</title>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<script
    src="https://cdn.jsdelivr.net/npm/qz-tray@2.3.0/qz-tray.js">
</script>

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
    min-height:100vh;
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

h1{
    color:#16246D;
    margin-bottom:5px;
}

.subtitle{
    color:#555;
    margin-bottom:25px;
}

.card{
    background:white;
    border-radius:15px;
    padding:20px;
    margin-bottom:20px;
}

input,
textarea,
select{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
    font-family:inherit;
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
    border-bottom:1px solid #ddd;
}

.status{
    padding:5px 10px;
    border-radius:20px;
    font-size:13px;
}

.status.completed{
    background:#DFF5E1;
    color:#176B2C;
}

.status.cancelled{
    background:#FDECEC;
    color:#D62828;
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

.box h3{
    color:#16246D;
    font-size:14px;
}

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
    max-height:90vh;
    overflow-y:auto;
    padding:25px;
    border-radius:15px;
}

.close-btn{
    float:right;
    border:none;
    background:#eee;
    color:#333;
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

.input-group button{
    margin-top:10px;
    height:44px;
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

.return-item label{
    cursor:pointer;
}

.return-item input[type="radio"]{
    width:auto;
    margin-right:8px;
}

.cancel-btn{
    background:#D62828;
    color:white;
    border:none;
    padding:8px 15px;
    border-radius:8px;
    cursor:pointer;
}

.cancel-btn:hover{
    background:#b71f1f;
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

.exchange-summary{
    background:#F5F7FC;
    padding:15px;
    margin-top:15px;
    border-radius:10px;
}

.exchange-summary p{
    margin:8px 0;
}

.message{
    padding:12px;
    border-radius:8px;
    margin:15px 0;
    background:#DFF5E1;
    color:#176B2C;
}

.error{
    padding:12px;
    border-radius:8px;
    margin:15px 0;
    background:#FDECEC;
    color:#D62828;
}

.modal-actions{
    display:flex;
    gap:10px;
    margin-top:15px;
}

.modal-actions button{
    flex:1;
}

@media(max-width:900px){

    .sidebar{
        width:210px;
    }

    .main{
        margin-left:210px;
        width:calc(100% - 210px);
    }

    .summary{
        grid-template-columns:1fr;
    }

    table{
        display:block;
        overflow-x:auto;
    }
}

</style>

</head>

<body>

<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

```
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
    Refund
</a>

<a href="z_reading.php">
    <i class="fas fa-file-invoice"></i>
    Z Reading
</a>


</div>

<div class="main">

<div class="header">

    <h1>
        ITEM EXCHANGE
    </h1>

    <div class="admin">

        <i class="fas fa-user"></i>

        <?= htmlspecialchars(
            $_SESSION['fullname'] ?? 'Admin'
        ); ?>

    </div>

</div>


<div class="container">

    <p class="subtitle">
        Process returned medicines and exchange items.
        No cash refund will be provided.
    </p>


    <?php if ($message): ?>

        <div class="message">
            <?= htmlspecialchars($message); ?>
        </div>

    <?php endif; ?>


    <?php if ($error): ?>

        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <button
        type="button"
        onclick="openExchangeModal()"
        class="exchange-btn">

        <i class="fa-solid fa-arrow-right-arrow-left"></i>

        Process Exchange

    </button>


    <div class="card">

        <h2>
            Exchange Summary
        </h2>


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

        <br>

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


            <?php if (!empty($exchangeHistory)): ?>

                <?php foreach ($exchangeHistory as $exchange): ?>

                    <tr>

                        <td>
                            EX-<?= (int) $exchange['exchange_id']; ?>
                        </td>

                        <td>
                            <?= (int) $exchange['original_sale_id']; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $exchange['returned_items'] ?? ''
                            ); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $exchange['replacement_items'] ?? ''
                            ); ?>
                        </td>

                        <td>
                            ₱<?= number_format(
                                (float) $exchange['additional_payment'],
                                2
                            ); ?>
                        </td>

                        <td>

                            <span class="status
                                <?= strtolower(
                                    htmlspecialchars(
                                        $exchange['status']
                                    )
                                ); ?>">

                                <?= htmlspecialchars(
                                    $exchange['status']
                                ); ?>

                            </span>

                        </td>

                        <td>

                            <?php if (
                                $exchange['status'] === 'Completed'
                            ): ?>

                                <form
                                    method="POST"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to cancel this exchange? Stock will be reversed.'
                                        );
                                    ">

                                    <input
                                        type="hidden"
                                        name="cancel_exchange_id"
                                        value="<?= (int) $exchange['exchange_id']; ?>">

                                    <button
                                        type="submit"
                                        name="cancel_exchange"
                                        class="cancel-btn">

                                        Cancel

                                    </button>

                                </form>

                            <?php else: ?>

                                <span style="color:#777;">
                                    Cancelled
                                </span>

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
```

</div>

<!-- =========================
     EXCHANGE MODAL
========================= -->

<div
    id="exchangeModal"
    class="modal">

```
<div class="modal-box">

    <button
        type="button"
        class="close-btn"
        onclick="closeExchangeModal()">

        ×

    </button>


    <h2>

        <i class="fa-solid fa-arrow-right-arrow-left"></i>

        Process Exchange

    </h2>


    <br>


    <form
        method="POST"
        onsubmit="return validateExchange();">


        <label>
            Original Sale ID
        </label>


        <div class="input-group">

            <input
                type="number"
                name="sale_id"
                id="sale_id"
                placeholder="Enter Sale ID"
                min="1"
                required>


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


        <br>


        <h3>
            Replacement Item
        </h3>


        <select
            name="replacement_medicine"
            id="replacement_medicine"
            onchange="calculateExchange()"
            required>

            <option value="">
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

            </p>


            <p>

                Remaining Credit:

                <strong id="remainingCredit">
                    ₱0.00
                </strong>

            </p>

        </div>


        <textarea
            name="reason"
            placeholder="Reason for exchange"></textarea>


        <button
            type="submit"
            name="complete_exchange"
            class="complete-btn">

            Complete Exchange

        </button>

    </form>

</div>
```

</div>

<script>

/*
|--------------------------------------------------------------------------
| OPEN EXCHANGE MODAL
|--------------------------------------------------------------------------
*/

function openExchangeModal(){

    const modal =
        document.getElementById(
            "exchangeModal"
        );

    modal.style.display = "flex";

    document.getElementById(
        "sale_id"
    ).value = "";

    document.getElementById(
        "saleItemsArea"
    ).innerHTML = `
        <p>Enter Sale ID first.</p>
    `;

    document.getElementById(
        "replacement_medicine"
    ).innerHTML = `
        <option value="">
            Select replacement
        </option>
    `;

    resetExchangeCalculation();
}


/*
|--------------------------------------------------------------------------
| CLOSE EXCHANGE MODAL
|--------------------------------------------------------------------------
*/

function closeExchangeModal(){

    document.getElementById(
        "exchangeModal"
    ).style.display = "none";

}


/*
|--------------------------------------------------------------------------
| RESET CALCULATION
|--------------------------------------------------------------------------
*/

function resetExchangeCalculation(){

    document.getElementById(
        "returnedValue"
    ).innerHTML = "₱0.00";

    document.getElementById(
        "replacementValue"
    ).innerHTML = "₱0.00";

    document.getElementById(
        "additionalPayment"
    ).innerHTML = "₱0.00";

    document.getElementById(
        "remainingCredit"
    ).innerHTML = "₱0.00";

}


/*
|--------------------------------------------------------------------------
| LOAD SALE
|--------------------------------------------------------------------------
*/

async function loadSale(){

    const saleId =
        document.getElementById(
            "sale_id"
        ).value.trim();

    const area =
        document.getElementById(
            "saleItemsArea"
        );


    if (!saleId) {

        alert(
            "Enter Sale ID first."
        );

        return;
    }


    area.innerHTML = `
        <p>Loading sale...</p>
    `;


    try {

        const response =
            await fetch(
                "get_sale_items.php?sale_id=" +
                encodeURIComponent(saleId)
            );


        if (!response.ok) {

            throw new Error(
                "Server returned an error."
            );
        }


        const data =
            await response.json();


        if (!data.success) {

            area.innerHTML = `
                <div class="error">
                    ${escapeHtml(
                        data.message ||
                        "Sale not found."
                    )}
                </div>
            `;

            return;
        }


        if (
            !data.items ||
            data.items.length === 0
        ) {

            area.innerHTML = `
                <div class="error">
                    No items were found in this sale.
                </div>
            `;

            return;
        }


        let html = `

            <h3>
                Returned Item
            </h3>

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
                required>

        `;


        data.items.forEach(
            function(item) {

                html += `

                    <div class="return-item">

                        <label>

                            <input
                                type="radio"
                                name="return_item"
                                value="${item.medicine_id}"
                                data-price="${item.unit_price}"
                                data-max="${item.quantity}"
                                onchange="calculateExchange()"
                                required>

                            <strong>
                                ${escapeHtml(
                                    item.name
                                )}
                            </strong>

                        </label>

                        <br>

                        Purchased:
                        ${item.quantity}

                        <br>

                        Price:
                        ₱${parseFloat(
                            item.unit_price
                        ).toFixed(2)}

                    </div>

                `;
            }
        );


        area.innerHTML =
            html;


        await loadReplacementItems();

    }
    catch (error) {

        console.error(error);

        area.innerHTML = `
            <div class="error">
                Unable to load sale.
                Please check the Sale ID.
            </div>
        `;
    }
}


/*
|--------------------------------------------------------------------------
| LOAD REPLACEMENT MEDICINES
|--------------------------------------------------------------------------
*/

async function loadReplacementItems(){

    const select =
        document.getElementById(
            "replacement_medicine"
        );


    try {

        const response =
            await fetch(
                "get_medicines.php"
            );


        if (!response.ok) {

            throw new Error(
                "Unable to load medicines."
            );
        }


        const data =
            await response.json();


        select.innerHTML = `
            <option value="">
                Select replacement item
            </option>
        `;


        if (
            !data.medicines ||
            !Array.isArray(data.medicines)
        ) {

            return;
        }


        data.medicines.forEach(
            function(item) {

                const option =
                    document.createElement(
                        "option"
                    );


                option.value =
                    item.medicine_id;


                option.dataset.price =
                    item.price;


                option.textContent =
                    item.name +
                    " - ₱" +
                    parseFloat(
                        item.price
                    ).toFixed(2);


                select.appendChild(
                    option
                );

            }
        );

    }
    catch (error) {

        console.error(error);

        select.innerHTML = `
            <option value="">
                Unable to load replacement items
            </option>
        `;
    }
}


/*
|--------------------------------------------------------------------------
| CALCULATE EXCHANGE
|--------------------------------------------------------------------------
*/

function calculateExchange(){

    const returned =
        document.querySelector(
            'input[name="return_item"]:checked'
        );


    const replacement =
        document.getElementById(
            "replacement_medicine"
        );


    if (
        !returned ||
        !replacement ||
        !replacement.value
    ) {

        resetExchangeCalculation();

        return;
    }


    const qtyInput =
        document.getElementById(
            "return_qty"
        );


    let qty =
        parseInt(
            qtyInput.value
        ) || 1;


    const maxQty =
        parseInt(
            returned.dataset.max
        ) || 1;


    if (qty < 1) {

        qty = 1;

    }


    if (qty > maxQty) {

        qty = maxQty;

        qtyInput.value =
            maxQty;
    }


    const returnPrice =
        parseFloat(
            returned.dataset.price
        ) || 0;


    const selectedOption =
        replacement.options[
            replacement.selectedIndex
        ];


    const replacementPrice =
        parseFloat(
            selectedOption.dataset.price
        ) || 0;


    const returnedValue =
        returnPrice * qty;


    const difference =
        replacementPrice -
        returnedValue;


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


    if (difference > 0) {

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
    else {

        document.getElementById(
            "additionalPayment"
        ).innerHTML =
            "₱0.00";


        document.getElementById(
            "remainingCredit"
        ).innerHTML =
            "₱" +
            Math.abs(
                difference
            ).toFixed(2);
    }
}


/*
|--------------------------------------------------------------------------
| VALIDATE EXCHANGE
|--------------------------------------------------------------------------
*/

function validateExchange(){

    const saleId =
        document.getElementById(
            "sale_id"
        ).value.trim();


    const returned =
        document.querySelector(
            'input[name="return_item"]:checked'
        );


    const replacement =
        document.getElementById(
            "replacement_medicine"
        ).value;


    const qtyInput =
        document.getElementById(
            "return_qty"
        );


    const qty =
        parseInt(
            qtyInput?.value
        ) || 0;


    if (!saleId) {

        alert(
            "Please enter the Original Sale ID."
        );

        return false;
    }


    if (!returned) {

        alert(
            "Please select the item being returned."
        );

        return false;
    }


    const maxQty =
        parseInt(
            returned.dataset.max
        ) || 0;


    if (qty < 1) {

        alert(
            "Return quantity must be at least 1."
        );

        return false;
    }


    if (qty > maxQty) {

        alert(
            "Return quantity cannot exceed the purchased quantity."
        );

        return false;
    }


    if (!replacement) {

        alert(
            "Please select a replacement item."
        );

        return false;
    }


    if (
        returned.value ===
        replacement
    ) {

        alert(
            "Returned item and replacement item cannot be the same."
        );

        return false;
    }


    return confirm(
        "Are you sure you want to complete this exchange?"
    );
}


/*
|--------------------------------------------------------------------------
| RECEIPT
|--------------------------------------------------------------------------
*/

function closeExchangeReceipt(){

    const modal =
        document.getElementById(
            "exchangeReceiptModal"
        );


    if (modal) {

        modal.style.display =
            "none";
    }
}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value){

    const div =
        document.createElement(
            "div"
        );

    div.textContent =
        value ?? "";

    return div.innerHTML;
}


/*
|--------------------------------------------------------------------------
| PRINT RECEIPT
|--------------------------------------------------------------------------
*/

async function printExchangeReceipt(){

    if (typeof qz === "undefined") {

        alert("QZ Tray is not loaded.");
        return;
    }

    try {

        /*
        |--------------------------------------------------------------------------
        | CONNECT TO QZ TRAY
        |--------------------------------------------------------------------------
        */

        if (!qz.websocket.isActive()) {
            await qz.websocket.connect();
        }


        /*
        |--------------------------------------------------------------------------
        | PRINTER
        |--------------------------------------------------------------------------
        */

        const printerName = "UTAK007";


        const config = qz.configs.create(
            printerName,
            {
                encoding: "UTF-8"
            }
        );


        /*
        |--------------------------------------------------------------------------
        | ESC/POS
        |--------------------------------------------------------------------------
        */

        const ESC = "\x1B";


        /*
        |--------------------------------------------------------------------------
        | RECEIPT WIDTH
        |
        | Use 32 characters for safer printing.
        |
        | This prevents text from reaching the physical
        | edge of the thermal paper and being cut off.
        |--------------------------------------------------------------------------
        */

        const WIDTH = 42;

/*
|--------------------------------------------------------------------------
| CENTER
|--------------------------------------------------------------------------
*/

function center(text) {

    text = String(text || "").trim();

    if (text.length > WIDTH) {
        text = text.substring(0, WIDTH);
    }

    return text + "\n";
}


/*
|--------------------------------------------------------------------------
| LINE
|--------------------------------------------------------------------------
*/

function line() {

    return "-".repeat(WIDTH) + "\n";
}


/*
|--------------------------------------------------------------------------
| ROW
|--------------------------------------------------------------------------
*/

function row(left, right) {

    left = String(left || "");
    right = String(right || "");

    const available =
        WIDTH - right.length - 1;

    if (available <= 0) {
        return right.substring(0, WIDTH) + "\n";
    }

    if (left.length > available) {
        left = left.substring(0, available);
    }

    return (
        left +
        " ".repeat(
            WIDTH -
            left.length -
            right.length
        ) +
        right +
        "\n"
    );
}


/*
|--------------------------------------------------------------------------
| PRODUCT NAME WRAPPER
|--------------------------------------------------------------------------
*/

function productName(name) {

    name = String(name || "").trim();

    if (!name) {
        return "";
    }

    let output = "";

    while (name.length > WIDTH) {

        let breakPoint =
            name.lastIndexOf(" ", WIDTH);

        if (breakPoint <= 0) {
            breakPoint = WIDTH;
        }

        output +=
            name.substring(0, breakPoint).trim() +
            "\n";

        name =
            name.substring(breakPoint).trim();
    }

    if (name.length > 0) {
        output += name + "\n";
    }

    return output;
}



        /*
        |--------------------------------------------------------------------------
        | PHP DATA
        |--------------------------------------------------------------------------
        */

        const exchangeId =
            <?= json_encode(
                "EX-" .
                (int)(
                    $exchangeReceipt['exchange_id'] ?? 0
                )
            ); ?>;


        const saleId =
            <?= json_encode(
                "#" .
                (int)(
                    $exchangeReceipt['sale_id'] ?? 0
                )
            ); ?>;


        const returnedName =
            <?= json_encode(
                $returnName ?? ''
            ); ?>;


        const replacementName =
            <?= json_encode(
                $replacementName ?? ''
            ); ?>;


        const returnedValue =
            <?= json_encode(
                number_format(
                    (float)(
                        $exchangeReceipt['returned_value'] ?? 0
                    ),
                    2
                )
            ); ?>;


        const replacementValue =
            <?= json_encode(
                number_format(
                    (float)(
                        $exchangeReceipt['replacement_value'] ?? 0
                    ),
                    2
                )
            ); ?>;


        const additionalPayment =
            <?= json_encode(
                number_format(
                    (float)(
                        $exchangeReceipt['additional_payment'] ?? 0
                    ),
                    2
                )
            ); ?>;


        const remainingCredit =
            <?= json_encode(
                number_format(
                    (float)(
                        $exchangeReceipt['remaining_credit'] ?? 0
                    ),
                    2
                )
            ); ?>;


        /*
        |--------------------------------------------------------------------------
        | BUILD RECEIPT
        |--------------------------------------------------------------------------
        */

        let data = [];


        /*
         * Reset printer.
         */

        data.push(
            ESC + "@"
        );


        /*
         * Center alignment.
         */

        data.push(
            ESC + "a" + "\x01"
        );


        /*
         * Bold ON.
         */

        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            center("ValueMeds")
        );


        data.push(
            center("ITEM EXCHANGE RECEIPT")
        );


        /*
         * Bold OFF.
         */

        data.push(
            ESC + "E" + "\x00"
        );


        data.push("\n");


        /*
         * LEFT ALIGN.
         */

        data.push(
            ESC + "a" + "\x00"
        );


        data.push(
            line()
        );


        /*
         * EXCHANGE INFORMATION
         */

        data.push(
            row(
                "Exchange ID:",
                exchangeId
            )
        );


        data.push(
            row(
                "Original Sale:",
                saleId
            )
        );


        data.push(
            line()
        );


        /*
         * RETURNED ITEM
         */

        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            "RETURNED ITEM\n"
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push(
            productName(
                returnedName
            )
        );


        data.push(
            row(
                "Exchange Credit:",
                "PHP " +
                returnedValue
            )
        );


        data.push("\n");


        /*
         * REPLACEMENT ITEM
         */

        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            "REPLACEMENT ITEM\n"
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push(
            productName(
                replacementName
            )
        );


        data.push(
            row(
                "Replacement Value:",
                "PHP " +
                replacementValue
            )
        );


        data.push(
            row(
                "Customer Paid:",
                "PHP " +
                additionalPayment
            )
        );


        data.push(
            line()
        );


        /*
         * REMAINING CREDIT
         */

        data.push(
            row(
                "Remaining Credit:",
                "PHP " +
                remainingCredit
            )
        );


        data.push("\n");


        /*
         * COMPLETED
         */

        data.push(
            ESC + "a" + "\x01"
        );


        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            center("COMPLETED")
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push(
            center("Thank you!")
        );


        /*
        |--------------------------------------------------------------------------
        | PAPER FEED
        |
        | This only feeds the paper.
        | THERE IS NO CUT COMMAND.
        |--------------------------------------------------------------------------
        */

        data.push(
            "\n\n\n\n\n\n\n\n\n\n\n\n"
        );


        /*
        |--------------------------------------------------------------------------
        | PRINT
        |--------------------------------------------------------------------------
        */

        await qz.print(
            config,
            data
        );


        alert(
            "Exchange receipt printed successfully."
        );

    }
    catch (error) {

        console.error(
            "QZ Tray print error:",
            error
        );


        alert(
            "Unable to print exchange receipt.\n\n" +
            error.message
        );

    }
}
/*
|--------------------------------------------------------------------------
| MODAL CLICK / ESCAPE
|--------------------------------------------------------------------------
*/

window.addEventListener(
    "click",
    function(event){

        const exchangeModal =
            document.getElementById(
                "exchangeModal"
            );


        const receiptModal =
            document.getElementById(
                "exchangeReceiptModal"
            );


        if (
            event.target ===
            exchangeModal
        ) {

            closeExchangeModal();
        }


        if (
            event.target ===
            receiptModal
        ) {

            closeExchangeReceipt();
        }

    }
);


document.addEventListener(
    "keydown",
    function(event){

        if (
            event.key ===
            "Escape"
        ) {

            closeExchangeModal();
            closeExchangeReceipt();
        }

    }
);

</script>

<?php if ($exchangeReceipt): ?>

<div
    id="exchangeReceiptModal"
    class="modal"
    style="display:flex;">

```
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
            EX-<?= (int)
                $exchangeReceipt['exchange_id']; ?>
        </strong>
    </p>


    <p>
        Original Sale:

        <strong>
            #<?= (int)
                $exchangeReceipt['sale_id']; ?>
        </strong>
    </p>


    <hr>


    <p>

        Returned Item:

        <br>

        <strong>
            <?= htmlspecialchars(
                $returnName
            ); ?>
        </strong>

    </p>


    <p>

        Exchange Credit:

        <strong>

            ₱<?= number_format(
                $exchangeReceipt[
                    'returned_value'
                ],
                2
            ); ?>

        </strong>

    </p>


    <hr>


    <p>

        Replacement Item:

        <br>

        <strong>
            <?= htmlspecialchars(
                $replacementName
            ); ?>
        </strong>

    </p>


    <p>

        Replacement Value:

        <strong>

            ₱<?= number_format(
                $exchangeReceipt[
                    'replacement_value'
                ],
                2
            ); ?>

        </strong>

    </p>


    <p>

        Additional Payment:

        <strong>

            ₱<?= number_format(
                $exchangeReceipt[
                    'additional_payment'
                ],
                2
            ); ?>

        </strong>

    </p>


    <?php if (
        $exchangeReceipt[
            'remaining_credit'
        ] > 0
    ): ?>

        <p>

            Remaining Credit:

            <strong>

                ₱<?= number_format(
                    $exchangeReceipt[
                        'remaining_credit'
                    ],
                    2
                ); ?>

            </strong>

        </p>

    <?php endif; ?>


    <hr>


    <h3 style="text-align:center">

        Completed

    </h3>


    <div class="modal-actions">

        <button
            type="button"
            onclick="printExchangeReceipt()">

            <i class="fa-solid fa-print"></i>

            Print Receipt

        </button>


        <button
            type="button"
            onclick="closeExchangeReceipt()">

            Done

        </button>

    </div>

</div>
```

</div>

<?php endif; ?>

<?php
/*
 * Remove receipt data after it has been rendered.
 * The receipt remains visible for this page load,
 * but refreshing will not show it again.
 */
unset($_SESSION['last_exchange']);
?>

</body>

</html>
