<?php

require 'connection.php';

session_start();

$fullname = $_SESSION['fullname'] ?? 'Admin';

$selectedDate = $_GET['date'] ?? date('Y-m-d');

date_default_timezone_set('Asia/Manila');


/*
|--------------------------------------------------------------------------
| GET SALES FOR SELECTED DATE
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        sale_id,
        cashier_id,
        sale_date,
        total_amount,
        discount_type,
        discount_percentage,
        discount_amount,
        customer_name,
        customer_id_number,
        payment_method,
        amount_paid,
        change_amount
    FROM sales
    WHERE DATE(sale_date) = ?
    ORDER BY sale_id ASC
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "s",
    $selectedDate
);

$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| INITIAL VALUES
|--------------------------------------------------------------------------
*/

$totalTransactions = 0;

$grossSales = 0;
$totalDiscount = 0;
$netSales = 0;

$totalAmountPaid = 0;
$totalChange = 0;

$cashSales = 0;
$otherSales = 0;
$firstSaleId = null;
$lastSaleId = null;


/*
|--------------------------------------------------------------------------
| PROCESS SALES
|--------------------------------------------------------------------------
*/

while ($sale = $result->fetch_assoc()) {

    $totalTransactions++;

    $saleId = (int)$sale['sale_id'];

    $totalAmount =
        (float)($sale['total_amount'] ?? 0);

    $discount =
        (float)($sale['discount_amount'] ?? 0);

    $amountPaid =
        (float)($sale['amount_paid'] ?? 0);

    $changeAmount =
        (float)($sale['change_amount'] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | FIRST AND LAST TRANSACTION
    |--------------------------------------------------------------------------
    */

    if ($firstSaleId === null) {

        $firstSaleId = $saleId;

    }

    $lastSaleId = $saleId;


    /*
    |--------------------------------------------------------------------------
    | SALES TOTALS
    |--------------------------------------------------------------------------
    */

    $netSales += $totalAmount;

    $totalDiscount += $discount;

    $totalAmountPaid += $amountPaid;

    $totalChange += $changeAmount;


    /*
    |--------------------------------------------------------------------------
    | PAYMENT METHOD
    |--------------------------------------------------------------------------
    */

    $paymentMethod =
        strtolower(
            trim(
                $sale['payment_method'] ?? ''
            )
        );


    switch ($paymentMethod) {

        case 'cash':

            $cashSales += $totalAmount;

            break;


        default:

            $otherSales += $totalAmount;

            break;

    }

}


/*
|--------------------------------------------------------------------------
| GROSS SALES
|--------------------------------------------------------------------------
*/

$grossSales =
    $netSales + $totalDiscount;


/*
|--------------------------------------------------------------------------
| TRANSACTION NUMBERS
|--------------------------------------------------------------------------
*/

$firstTransaction =
    $firstSaleId !== null
        ? $firstSaleId
        : 'N/A';


$lastTransaction =
    $lastSaleId !== null
        ? $lastSaleId
        : 'N/A';


/*
|--------------------------------------------------------------------------
| FORMATTED DATE
|--------------------------------------------------------------------------
*/

$formattedDate =
    date(
        'F d, Y',
        strtotime($selectedDate)
    );

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Z-Reading | ValueMeds
    </title>


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >


<style>

/* =========================================================
   RESET
========================================================= */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;

    font-family:
        Segoe UI,
        Arial,
        sans-serif;

}


/* =========================================================
   BODY
========================================================= */

body {

    background: #F5F7FC;

    display: flex;

    min-height: 100vh;

}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    width: 250px;

    height: 100vh;

    background: #16246D;

    color: white;

    position: fixed;

    left: 0;

    top: 0;

    overflow-y: auto;

}


.logo {

    padding: 25px;

    font-size: 25px;

    font-weight: bold;

    text-align: center;

    border-bottom:
        1px solid
        rgba(255,255,255,.15);

}


.menu-title {

    padding:
        20px
        25px
        10px;

    font-size: 13px;

    opacity: .7;

    letter-spacing: 1px;

}


.sidebar a {

    display: block;

    padding:
        14px
        25px;

    color: white;

    text-decoration: none;

    transition: .3s;

}


.sidebar a i {

    width: 25px;

}


.sidebar a:hover {

    background: #8FB3E2;

    color: #16246D;

}


.sidebar a.active {

    background: #8FB3E2;

    color: #16246D;

}


/* =========================================================
   MAIN
========================================================= */

.main {

    margin-left: 250px;

    width:
        calc(100% - 250px);

    padding: 30px;

}


/* =========================================================
   HEADER
========================================================= */

.header {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 25px;

}


.header h1 {

    color: #16246D;

    font-size: 28px;

}


.admin {

    background: white;

    padding:
        10px
        20px;

    border-radius: 30px;

    box-shadow:
        0
        5px
        10px
        rgba(0,0,0,.08);

    color: #333;

}


.admin i {

    margin-right: 7px;

    color: #16246D;

}


/* =========================================================
   DATE FILTER
========================================================= */

.filter-box {

    background: white;

    padding: 20px;

    border-radius: 15px;

    box-shadow:
        0
        5px
        15px
        rgba(0,0,0,.08);

    margin-bottom: 20px;

    display: flex;

    align-items: end;

    gap: 15px;

}


.filter-group {

    display: flex;

    flex-direction: column;

    gap: 7px;

}


.filter-group label {

    font-size: 13px;

    font-weight: 600;

    color: #555;

}


.filter-group input {

    padding:
        11px
        14px;

    border:
        1px solid #ddd;

    border-radius: 8px;

    outline: none;

    font-size: 14px;

}


.filter-group input:focus {

    border-color: #16246D;

}


.filter-btn {

    padding:
        11px
        20px;

    background: #16246D;

    color: white;

    border: none;

    border-radius: 8px;

    cursor: pointer;

    font-weight: 600;

    font-size: 14px;

}


.filter-btn:hover {

    background: #233b9a;

}


/* =========================================================
   SUMMARY CARDS
========================================================= */

.summary-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 20px;

}


.summary-card {

    background: white;

    padding: 22px;

    border-radius: 15px;

    box-shadow:
        0
        5px
        15px
        rgba(0,0,0,.08);

}


.summary-card i {

    font-size: 25px;

    color: #16246D;

    margin-bottom: 12px;

}


.summary-card h3 {

    color: #666;

    font-size: 14px;

    margin-bottom: 7px;

}


.summary-card .value {

    font-size: 24px;

    font-weight: bold;

    color: #16246D;

}


/* =========================================================
   REPORT
========================================================= */

.report {

    background: white;

    border-radius: 15px;

    padding: 30px;

    box-shadow:
        0
        5px
        15px
        rgba(0,0,0,.08);

}


.report-header {

    text-align: center;

    border-bottom:
        2px solid #16246D;

    padding-bottom: 20px;

    margin-bottom: 25px;

}


.report-header h2 {

    color: #16246D;

    margin-bottom: 5px;

}


.report-header p {

    color: #666;

    margin-top: 4px;

}


/* =========================================================
   REPORT SECTIONS
========================================================= */

.report-section {

    margin-bottom: 25px;

}


.report-section h3 {

    color: #16246D;

    border-bottom:
        1px solid #ddd;

    padding-bottom: 8px;

    margin-bottom: 10px;

}


.report-row {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    padding: 10px 0;

    border-bottom:
        1px solid #eee;

    gap: 20px;

}


.report-row span:first-child {

    color: #555;

}


.report-row span:last-child {

    font-weight: 600;

    text-align: right;

}


.total-row {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    padding: 15px 0;

    font-size: 20px;

    font-weight: bold;

    color: #16246D;

    border-top:
        2px solid #16246D;

    gap: 20px;

}


.total-row span:last-child {

    text-align: right;

}


/* =========================================================
   PRINT BUTTON
========================================================= */

.print-container {

    display: flex;

    justify-content: flex-end;

    margin-top: 20px;

}


.print-btn {

    background: #16246D;

    color: white;

    border: none;

    padding:
        13px
        25px;

    border-radius: 8px;

    cursor: pointer;

    font-weight: 600;

    font-size: 14px;

}


.print-btn:hover {

    background: #233b9a;

}


.print-btn i {

    margin-right: 7px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:1000px) {

    .summary-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media(max-width:650px) {

    .sidebar {

        display: none;

    }


    .main {

        margin-left: 0;

        width: 100%;

        padding: 15px;

    }


    .summary-grid {

        grid-template-columns: 1fr;

    }


    .filter-box {

        flex-direction: column;

        align-items: stretch;

    }


    .header {

        gap: 15px;

        flex-direction: column;

        align-items: flex-start;

    }

}

</style>

</head>


<body>


<!-- =========================================================
     SIDEBAR
========================================================= -->

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


    <a href="refund.php">

        <i class="fa-solid fa-arrow-right-arrow-left"></i>

        Refund

    </a>


    <a
        href="z_reading.php"
        class="active"
    >

        <i class="fas fa-file-invoice-dollar"></i>

        Z-Reading

    </a>

</div>



<!-- =========================================================
     MAIN
========================================================= -->

<div class="main">


    <!-- HEADER -->

    <div class="header">

        <h1>
            Z-Reading
        </h1>


        <div class="admin">

            <i class="fas fa-user"></i>

            <?= htmlspecialchars(
                $fullname
            ); ?>

        </div>

    </div>



    <!-- =====================================================
         DATE FILTER
    ====================================================== -->

    <form
        method="GET"
        class="filter-box"
    >

        <div class="filter-group">

            <label>
                Select Date
            </label>


            <input
                type="date"
                name="date"
                value="<?= htmlspecialchars(
                    $selectedDate
                ); ?>"
                required
            >

        </div>


        <button
            type="submit"
            class="filter-btn"
        >

            <i class="fas fa-search"></i>

            Generate Z-Reading

        </button>

    </form>



    <!-- =====================================================
         SUMMARY CARDS
    ====================================================== -->

    <div class="summary-grid">


        <div class="summary-card">

            <i class="fas fa-receipt"></i>

            <h3>
                Total Transactions
            </h3>

            <div class="value">

                <?= $totalTransactions; ?>

            </div>

        </div>



        <div class="summary-card">

            <i class="fas fa-peso-sign"></i>

            <h3>
                Gross Sales
            </h3>

            <div class="value">

                ₱<?= number_format(
                    $grossSales,
                    2
                ); ?>

            </div>

        </div>



        <div class="summary-card">

            <i class="fas fa-tags"></i>

            <h3>
                Discounts
            </h3>

            <div class="value">

                ₱<?= number_format(
                    $totalDiscount,
                    2
                ); ?>

            </div>

        </div>



        <div class="summary-card">

            <i class="fas fa-chart-line"></i>

            <h3>
                Net Sales
            </h3>

            <div class="value">

                ₱<?= number_format(
                    $netSales,
                    2
                ); ?>

            </div>

        </div>

    </div>



    <!-- =====================================================
         Z-READING REPORT
    ====================================================== -->

    <div class="report">


        <!-- REPORT HEADER -->

        <div class="report-header">

            <h2>
                ValueMeds Pharmacy
            </h2>

            <p>
                Z-Reading / End-of-Day Sales Report
            </p>

            <p>

                Date:

                <strong>

                    <?= htmlspecialchars(
                        $formattedDate
                    ); ?>

                </strong>

            </p>

        </div>



        <!-- =================================================
             TRANSACTION INFORMATION
        ================================================== -->

        <div class="report-section">

            <h3>
                Transaction Information
            </h3>


            <div class="report-row">

                <span>
                    Beginning Transaction No.
                </span>

                <span>

                    <?= htmlspecialchars(
                        (string)$firstTransaction
                    ); ?>

                </span>

            </div>


            <div class="report-row">

                <span>
                    Ending Transaction No.
                </span>

                <span>

                    <?= htmlspecialchars(
                        (string)$lastTransaction
                    ); ?>

                </span>

            </div>


            <div class="report-row">

                <span>
                    Total Transactions
                </span>

                <span>
                    <?= $totalTransactions; ?>
                </span>

            </div>

        </div>



        <!-- =================================================
             SALES SUMMARY
        ================================================== -->

        <div class="report-section">

            <h3>
                Sales Summary
            </h3>


            <div class="report-row">

                <span>
                    Gross Sales
                </span>

                <span>

                    ₱<?= number_format(
                        $grossSales,
                        2
                    ); ?>

                </span>

            </div>


            <div class="report-row">

                <span>
                    Total Discounts
                </span>

                <span>

                    ₱<?= number_format(
                        $totalDiscount,
                        2
                    ); ?>

                </span>

            </div>


            <div class="total-row">

                <span>
                    NET SALES
                </span>

                <span>

                    ₱<?= number_format(
                        $netSales,
                        2
                    ); ?>

                </span>

            </div>

        </div>



        <!-- =================================================
             PAYMENT SUMMARY
        ================================================== -->

        <div class="report-section">

            <h3>
                Payment Summary
            </h3>


            <div class="report-row">

                <span>
                    Cash
                </span>

                <span>

                    ₱<?= number_format(
                        $cashSales,
                        2
                    ); ?>

                </span>

            </div>

            </div>


            <div class="report-row">

                <span>
                    Other
                </span>

                <span>

                    ₱<?= number_format(
                        $otherSales,
                        2
                    ); ?>

                </span>

            </div>

        </div>



        <!-- =================================================
             PAYMENT DETAILS
        ================================================== -->

        <div class="report-section">

            <h3>
                Payment Details
            </h3>


            <div class="report-row">

                <span>
                    Total Amount Paid
                </span>

                <span>

                    ₱<?= number_format(
                        $totalAmountPaid,
                        2
                    ); ?>

                </span>

            </div>


            <div class="report-row">

                <span>
                    Total Change Given
                </span>

                <span>

                    ₱<?= number_format(
                        $totalChange,
                        2
                    ); ?>

                </span>

            </div>

        </div>



        <!-- =================================================
             FINAL TOTAL
        ================================================== -->

        <div class="total-row">

            <span>
                TOTAL NET SALES
            </span>

            <span>

                ₱<?= number_format(
                    $netSales,
                    2
                ); ?>

            </span>

        </div>



        <!-- =================================================
             PRINT BUTTON
        ================================================== -->

        <div class="print-container">

            <button
                type="button"
                class="print-btn"
                onclick="printZReading()"
            >

                <i class="fas fa-print"></i>

                Print Z-Reading

            </button>

        </div>


    </div>


</div>



<!-- =========================================================
     QZ TRAY
     LOAD ONCE ONLY
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/qz-tray@2.3.0/qz-tray.js"></script>


<script>

/*
|--------------------------------------------------------------------------
| PRINT Z-READING USING QZ TRAY
|--------------------------------------------------------------------------
| Printer:
| UTAK007
|
| Paper:
| 80mm thermal
|
| Width:
| 48 characters
|--------------------------------------------------------------------------
*/

async function printZReading()
{

    if (typeof qz === "undefined") {

        alert(
            "QZ Tray library is not loaded.\n\n" +
            "Please make sure QZ Tray is running."
        );

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
        const GS = "\x1D";
        const WIDTH = 48;


        /*
        |--------------------------------------------------------------------------
        | CENTER
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | Do NOT manually add spaces here.
        | ESC/POS itself handles the centering.
        |--------------------------------------------------------------------------
        */

        function center(text)
        {

            return String(text) + "\n";

        }


        /*
        |--------------------------------------------------------------------------
        | LEFT / RIGHT ROW
        |--------------------------------------------------------------------------
        */

        function row(left, right)
        {

            left = String(left);
            right = String(right);


            if (right.length >= WIDTH) {

                right =
                    right.substring(
                        right.length - (WIDTH - 1)
                    );

            }


            const maxLeftLength =
                WIDTH -
                right.length -
                1;


            if (left.length > maxLeftLength) {

                left =
                    left.substring(
                        0,
                        maxLeftLength
                    );

            }


            const spaces =
                WIDTH -
                left.length -
                right.length;


            return (
                left +
                " ".repeat(
                    Math.max(1, spaces)
                ) +
                right +
                "\n"
            );

        }


        /*
        |--------------------------------------------------------------------------
        | DIVIDER
        |--------------------------------------------------------------------------
        */

        function divider()
        {

            return "-".repeat(WIDTH) + "\n";

        }


        /*
        |--------------------------------------------------------------------------
        | RECEIPT DATA
        |--------------------------------------------------------------------------
        */

        const data = [];


        /*
        |--------------------------------------------------------------------------
        | INITIALIZE
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "@"
        );


        /*
        |--------------------------------------------------------------------------
        | HEADER - CENTERED
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "a" + "\x01"
        );


        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            center("ValueMeds")
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push(
            center("PHARMACY")
        );


        data.push(
            center("Z-READING")
        );


        data.push(
            center("End-of-Day Sales Report")
        );


        data.push("\n");


        /*
        |--------------------------------------------------------------------------
        | LEFT ALIGN
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "a" + "\x00"
        );


        data.push(
            divider()
        );


        /*
        |--------------------------------------------------------------------------
        | DATE
        |--------------------------------------------------------------------------
        */

        data.push(
            row(
                "Date:",
                <?= json_encode(
                    date(
                        'M d, Y',
                        strtotime($selectedDate)
                    )
                ); ?>
            )
        );


        /*
        |--------------------------------------------------------------------------
        | TIME
        |--------------------------------------------------------------------------
        */

        data.push(
            row(
                "Time:",
                <?= json_encode(
                    date('h:i A')
                ); ?>
            )
        );


        data.push(
            divider()
        );


        /*
        |--------------------------------------------------------------------------
        | TRANSACTION SUMMARY
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            "TRANSACTION SUMMARY\n"
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push(
            row(
                "Beginning Transaction",
                <?= json_encode(
                    (string)$firstTransaction
                ); ?>
            )
        );


        data.push(
            row(
                "Ending Transaction",
                <?= json_encode(
                    (string)$lastTransaction
                ); ?>
            )
        );


        data.push(
            row(
                "Total Transactions",
                <?= json_encode(
                    (string)$totalTransactions
                ); ?>
            )
        );


        data.push(
            divider()
        );


        /*
        |--------------------------------------------------------------------------
        | SALES SUMMARY
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            "SALES SUMMARY\n"
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push(
            row(
                "Gross Sales",
                "PHP " +
                <?= json_encode(
                    number_format(
                        $grossSales,
                        2
                    )
                ); ?>
            )
        );


        data.push(
            row(
                "Discounts",
                "PHP " +
                <?= json_encode(
                    number_format(
                        $totalDiscount,
                        2
                    )
                ); ?>
            )
        );


        data.push(
            divider()
        );


        /*
        |--------------------------------------------------------------------------
        | NET SALES
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            row(
                "NET SALES",
                "PHP " +
                <?= json_encode(
                    number_format(
                        $netSales,
                        2
                    )
                ); ?>
            )
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push(
            divider()
        );


        /*
        |--------------------------------------------------------------------------
        | PAYMENT SUMMARY
        |--------------------------------------------------------------------------
        | GCash and Card REMOVED
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            "PAYMENT SUMMARY\n"
        );


        data.push(
            ESC + "E" + "\x00"
        );


        /*
        | CASH
        */

        data.push(
            row(
                "Cash",
                "PHP " +
                <?= json_encode(
                    number_format(
                        $cashSales,
                        2
                    )
                ); ?>
            )
        );


        /*
        | OTHER
        */

        data.push(
            row(
                "Other",
                "PHP " +
                <?= json_encode(
                    number_format(
                        $otherSales,
                        2
                    )
                ); ?>
            )
        );


        data.push(
            divider()
        );


        /*
        |--------------------------------------------------------------------------
        | PAYMENT DETAILS
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            "PAYMENT DETAILS\n"
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push(
            row(
                "Amount Paid",
                "PHP " +
                <?= json_encode(
                    number_format(
                        $totalAmountPaid,
                        2
                    )
                ); ?>
            )
        );


        data.push(
            row(
                "Change Given",
                "PHP " +
                <?= json_encode(
                    number_format(
                        $totalChange,
                        2
                    )
                ); ?>
            )
        );


        data.push(
            divider()
        );


        /*
        |--------------------------------------------------------------------------
        | FINAL TOTAL - CENTERED
        |--------------------------------------------------------------------------
        */

        data.push(
            ESC + "a" + "\x01"
        );


        data.push(
            ESC + "E" + "\x01"
        );


        data.push(
            center("TOTAL NET SALES")
        );


        data.push(
            center(
                "PHP " +
                <?= json_encode(
                    number_format(
                        $netSales,
                        2
                    )
                ); ?>
            )
        );


        data.push(
            ESC + "E" + "\x00"
        );


        data.push("\n");


        /*
        |--------------------------------------------------------------------------
        | FOOTER - CENTERED
        |--------------------------------------------------------------------------
        */

        data.push(
            center("Z-READING COMPLETED")
        );


        data.push(
            center("ValueMeds Pharmacy")
        );


        data.push(
            center("Thank you.")
        );


        /*
        |--------------------------------------------------------------------------
        | FEED PAPER
        |--------------------------------------------------------------------------
        */

        data.push(
            "\n\n\n\n\n"
        );


        /*
        |--------------------------------------------------------------------------
        | CUT PAPER
        |--------------------------------------------------------------------------
        */

        data.push(
            GS + "V" + "\x00"
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


        console.log(
            "Z-Reading printed successfully."
        );


        alert(
            "Z-Reading printed successfully."
        );


    }
    catch (error) {

        console.error(
            "QZ Tray Error:",
            error
        );


        let errorMessage =
            "Unknown QZ Tray error.";


        if (
            error &&
            error.message
        ) {

            errorMessage =
                error.message;

        }
        else if (error) {

            errorMessage =
                String(error);

        }


        alert(
            "QZ Tray Error:\n\n" +
            errorMessage +
            "\n\nPrinter: UTAK007"
        );

    }

}

</script>


</body>

</html>