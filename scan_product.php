<?php

require_once "connection.php";

$result = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $qr_value = trim($_POST["qr_value"] ?? "");

    if ($qr_value === "") {
        $error = "Please enter a QR code value.";
    } elseif (!preg_match('/^VM-MED-(\d+)$/', $qr_value, $matches)) {
        $error = "Invalid ValueMeds QR code.";
    } else {

        $medicine_id = (int)$matches[1];

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

        $query_result = $stmt->get_result();

        if ($query_result->num_rows === 1) {
            $result = $query_result->fetch_assoc();
        } else {
            $error = "Product not found.";
        }

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
        content="width=device-width, initial-scale=1.0"
    >

    <title>Scan Product - ValueMeds</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fb;
            color: #222;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            color: #16246D;
        }

        .subtitle {
            color: #666;
            margin-bottom: 25px;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        .input-group input {
            flex: 1;
            padding: 13px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        .scan-button {
            background: #16246D;
            color: white;
            border: none;
            padding: 13px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        .scan-button:hover {
            background: #101b55;
        }

        .error {
            margin-top: 20px;
            padding: 12px;
            background: #ffe5e5;
            color: #b00020;
            border-radius: 6px;
        }

        .product {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 25px;
        }

        .product h2 {
            margin-top: 0;
            color: #16246D;
        }

        .product-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .label {
            font-weight: bold;
        }

        .stock {
            font-weight: bold;
        }

        @media (max-width: 600px) {

            .container {
                margin: 20px auto;
            }

            .card {
                padding: 20px;
            }

            .input-group {
                flex-direction: column;
            }

            .scan-button {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>
            <i class="fas fa-qrcode"></i>
            Product QR Lookup
        </h1>

        <p class="subtitle">
            Enter a ValueMeds QR code to find the corresponding product.
        </p>

        <form method="POST">

            <div class="input-group">

                <input
                    type="text"
                    name="qr_value"
                    placeholder="Example: VM-MED-11"
                    autocomplete="off"
                    autofocus
                >

                <button
                    type="submit"
                    class="scan-button"
                >
                    <i class="fas fa-search"></i>
                    Find Product
                </button>

            </div>

        </form>

        <?php if ($error !== ""): ?>

            <div class="error">
                <i class="fas fa-circle-exclamation"></i>
                <?= htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <?php if ($result): ?>

            <div class="product">

                <h2>
                    <?= htmlspecialchars($result["name"]); ?>
                </h2>

                <div class="product-row">
                    <span class="label">Medicine ID</span>
                    <span>
                        <?= (int)$result["medicine_id"]; ?>
                    </span>
                </div>

                <div class="product-row">
                    <span class="label">QR Code</span>
                    <span>
                        VM-MED-<?= (int)$result["medicine_id"]; ?>
                    </span>
                </div>

                <div class="product-row">
                    <span class="label">Category</span>
                    <span>
                        <?= htmlspecialchars($result["category"]); ?>
                    </span>
                </div>

                <div class="product-row">
                    <span class="label">Description</span>
                    <span>
                        <?= htmlspecialchars($result["description"]); ?>
                    </span>
                </div>

                <div class="product-row">
                    <span class="label">Selling Price</span>
                    <span>
                        ₱<?= number_format($result["price"], 2); ?>
                    </span>
                </div>

                <div class="product-row">
                    <span class="label">Stock</span>

                    <span class="stock">
                        <?= (int)$result["stock"]; ?>
                    </span>
                </div>

                <div class="product-row">
                    <span class="label">Expiry Date</span>
                    <span>
                        <?= htmlspecialchars($result["expiry_date"]); ?>
                    </span>
                </div>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>