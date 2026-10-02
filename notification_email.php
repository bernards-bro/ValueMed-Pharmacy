<?php

/*
|--------------------------------------------------------------------------
| ValueMeds Email Notification Helper
|--------------------------------------------------------------------------
|
| One Gmail account is used as the SYSTEM SENDER.
| Registered user emails in users.email are used as RECIPIENTS.
|
| Stock alerts are sent to active Admin and Manager accounts.
|
| IMPORTANT:
| - Use a Gmail App Password, NOT your normal Gmail password.
| - Keep this file private.
|
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;


/*
|--------------------------------------------------------------------------
| Gmail SMTP Configuration
|--------------------------------------------------------------------------
*/

const VM_MAIL_GMAIL = 'valuemed575@gmail.com';
const VM_MAIL_APP_PASSWORD = 'gdwbgcyypclcdbvp';

const VM_MAIL_SMTP_HOST = 'smtp.gmail.com';
const VM_MAIL_SMTP_PORT = 587;


/*
|--------------------------------------------------------------------------
| Stock Alert Settings
|--------------------------------------------------------------------------
*/

const VM_STOCK_LOW_LIMIT = 20;
const VM_STOCK_CRITICAL_LIMIT = 5;


/*
|--------------------------------------------------------------------------
| Get Stock Alert Level
|--------------------------------------------------------------------------
*/

function getStockAlertLevel(int $stock): ?string
{
    if ($stock <= 0) {
        return 'Out of Stock';
    }

    if ($stock <= VM_STOCK_CRITICAL_LIMIT) {
        return 'Critical Stock';
    }

    if ($stock <= VM_STOCK_LOW_LIMIT) {
        return 'Low Stock';
    }

    return null;
}


/*
|--------------------------------------------------------------------------
| Get Notification Recipients
|--------------------------------------------------------------------------
|
| By default:
| - Admin accounts receive notifications.
| - Manager accounts receive notifications.
| - Cashiers do not receive stock-alert emails.
|
| The email addresses come directly from users.email.
|
*/

function getNotificationRecipients(
    mysqli $conn,
    array $roles = ['Admin', 'Manager']
): array {

    $recipients = [];

    $types = str_repeat('s', count($roles));

    $sql = "
        SELECT
            user_id,
            fullname,
            email
        FROM users
        WHERE LOWER(status) = 'active'
        AND email IS NOT NULL
        AND TRIM(email) <> ''
        AND LOWER(role) IN (" .
        implode(
            ',',
            array_fill(0, count($roles), 'LOWER(?)')
        ) .
        ")
        ORDER BY fullname ASC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log(
            'ValueMeds email: failed to prepare recipient query: ' .
            $conn->error
        );

        return [];
    }

    $bindValues = [];

    foreach ($roles as $index => $role) {
        $bindValues[$index] = $role;
    }

    $bindParams = [$types];

    foreach ($bindValues as &$value) {
        $bindParams[] = &$value;
    }

    call_user_func_array(
        [$stmt, 'bind_param'],
        $bindParams
    );

    $stmt->execute();

    $result = $stmt->get_result();

    /*
    |--------------------------------------------------------------------------
    | Prevent duplicate emails
    |--------------------------------------------------------------------------
    |
    | Multiple users may have the same email address.
    | Only send one email per unique address.
    |
    */

    $uniqueRecipients = [];

    while ($row = $result->fetch_assoc()) {

        $email = trim($row['email']);

        if (
            $email !== '' &&
            filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {

            $emailKey = strtolower($email);

            if (!isset($uniqueRecipients[$emailKey])) {

                $uniqueRecipients[$emailKey] = [
                    'user_id' => (int)$row['user_id'],
                    'fullname' => $row['fullname'],
                    'email' => $email
                ];
            }
        }
    }

    $stmt->close();

    return array_values($uniqueRecipients);
}


/*
|--------------------------------------------------------------------------
| Reset Recovered Stock Alert States
|--------------------------------------------------------------------------
|
| If stock rises above 20, remove the old alert state.
| This allows a future drop to 20 or below to send a new alert.
|
*/

function resetRecoveredStockAlertStates(mysqli $conn): void
{
    $conn->query("
        DELETE sea
        FROM stock_email_alerts sea
        INNER JOIN medicines m
            ON m.medicine_id = sea.medicine_id
        WHERE m.stock > " . VM_STOCK_LOW_LIMIT . "
    ");
}


/*
|--------------------------------------------------------------------------
| Send Email
|--------------------------------------------------------------------------
*/

function sendValueMedsEmail(
    string $recipientEmail,
    string $recipientName,
    string $subject,
    string $htmlBody,
    string $plainBody
): bool {

    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();

        /*
        |--------------------------------------------------------------------------
        | SMTP Debugging
        |--------------------------------------------------------------------------
        */

        $mail->SMTPDebug = 0;

        $mail->Debugoutput = function ($str, $level) {
            error_log("PHPMailer [$level]: $str");
        };

        /*
        |--------------------------------------------------------------------------
        | Gmail SMTP
        |--------------------------------------------------------------------------
        */

        $mail->Host = VM_MAIL_SMTP_HOST;
        $mail->SMTPAuth = true;

        $mail->Username = VM_MAIL_GMAIL;

        $mail->Password = str_replace(
            ' ',
            '',
            VM_MAIL_APP_PASSWORD
        );

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = VM_MAIL_SMTP_PORT;

        /*
        |--------------------------------------------------------------------------
        | Connection Timeout
        |--------------------------------------------------------------------------
        */

        $mail->Timeout = 15;

        /*
        |--------------------------------------------------------------------------
        | Do not keep SMTP connections open
        |--------------------------------------------------------------------------
        */

        $mail->SMTPKeepAlive = false;

        /*
        |--------------------------------------------------------------------------
        | Email Encoding
        |--------------------------------------------------------------------------
        */

        $mail->CharSet = 'UTF-8';

        /*
        |--------------------------------------------------------------------------
        | Sender
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            VM_MAIL_GMAIL,
            'ValueMeds'
        );

        /*
        |--------------------------------------------------------------------------
        | Recipient
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            $recipientEmail,
            $recipientName
        );

        /*
        |--------------------------------------------------------------------------
        | Message
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = $plainBody;

        /*
        |--------------------------------------------------------------------------
        | Send
        |--------------------------------------------------------------------------
        */

        $mail->send();

        return true;

    } catch (PHPMailerException $e) {

        error_log(
            'ValueMeds email failed for ' .
            $recipientEmail .
            ': ' .
            $e->getMessage()
        );

        return false;
    }
}

function collectNewStockAlerts(mysqli $conn): array
{
    $alerts = [];

    $recipients = getNotificationRecipients($conn);

    if (empty($recipients)) {
        return [];
    }

    $result = $conn->query("
        SELECT
            m.medicine_id,
            m.name,
            m.category,
            m.stock,
            m.expiry_date
        FROM medicines m
        WHERE m.stock <= " . VM_STOCK_LOW_LIMIT . "
        ORDER BY
            m.stock ASC,
            m.name ASC
    ");

    if (!$result) {
        error_log(
            'ValueMeds email: failed to collect stock alerts: ' .
            $conn->error
        );

        return [];
    }

    while ($medicine = $result->fetch_assoc()) {

        $stock = (int)$medicine['stock'];

        $alertLevel = getStockAlertLevel($stock);

        if ($alertLevel === null) {
            continue;
        }

        foreach ($recipients as $recipient) {

            $stmt = $conn->prepare("
                SELECT alert_level
                FROM stock_email_alerts
                WHERE medicine_id = ?
                  AND recipient_email = ?
                LIMIT 1
            ");

            if (!$stmt) {
                error_log(
                    'ValueMeds email: failed to prepare alert-state query: ' .
                    $conn->error
                );

                continue;
            }

            $medicineId = (int)$medicine['medicine_id'];
            $recipientEmail = $recipient['email'];

            $stmt->bind_param(
                'is',
                $medicineId,
                $recipientEmail
            );

            $stmt->execute();

            $stateResult = $stmt->get_result();
            $state = $stateResult->fetch_assoc();

            $stmt->close();

            $previousLevel = $state['alert_level'] ?? null;

            if ($previousLevel === $alertLevel) {
                continue;
            }

            $medicineAlert = $medicine;

            $medicineAlert['alert_level'] = $alertLevel;
            $medicineAlert['recipient_email'] = $recipientEmail;
            $medicineAlert['recipient_name'] = $recipient['fullname'];

            $alerts[] = $medicineAlert;
        }
    }

    return $alerts;
}

function sendStockAlertSummaryEmail(
    mysqli $conn,
    array $alerts,
    string $source = 'ValueMeds'
): bool {

    if (empty($alerts)) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Group alerts by recipient
    |--------------------------------------------------------------------------
    */

    $alertsByRecipient = [];

    foreach ($alerts as $alert) {

        $email = strtolower(
            trim($alert['recipient_email'] ?? '')
        );

        if ($email === '') {
            continue;
        }

        if (!isset($alertsByRecipient[$email])) {
            $alertsByRecipient[$email] = [
                'name' => $alert['recipient_name'] ?? '',
                'alerts' => []
            ];
        }

        $alertsByRecipient[$email]['alerts'][] = $alert;
    }

    if (empty($alertsByRecipient)) {
        return false;
    }

    $allRecipientsSucceeded = true;

    /*
    |--------------------------------------------------------------------------
    | Send one summary email per recipient
    |--------------------------------------------------------------------------
    */

    foreach ($alertsByRecipient as $recipientEmail => $recipientData) {

        $recipientAlerts = $recipientData['alerts'];
        $recipientName = $recipientData['name'];

        $count = count($recipientAlerts);

        if ($count === 1) {

            $subject =
                'ValueMeds Stock Alert: ' .
                $recipientAlerts[0]['name'] .
                ' - ' .
                $recipientAlerts[0]['alert_level'];

        } else {

            $subject =
                'ValueMeds Stock Alerts: ' .
                $count .
                ' Products Require Attention';
        }

        /*
        |--------------------------------------------------------------------------
        | HTML email
        |--------------------------------------------------------------------------
        */

        $htmlBody = '
        <div style="
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: auto;
            color: #333;
        ">

            <h2 style="color:#16246D;">
                ValueMeds Stock Alert
            </h2>

            <p>
                The following medicine stock levels require attention:
            </p>

            <table
                cellpadding="8"
                cellspacing="0"
                border="1"
                style="
                    width:100%;
                    border-collapse:collapse;
                    border-color:#ddd;
                "
            >

                <thead>
                    <tr style="background:#16246D;color:white;">
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Alert Level</th>
                        <th>Expiry Date</th>
                    </tr>
                </thead>

                <tbody>
        ';

        foreach ($recipientAlerts as $alert) {

            $htmlBody .= '
                    <tr>
                        <td>' .
                            htmlspecialchars($alert['name']) .
                        '</td>

                        <td>' .
                            htmlspecialchars($alert['category']) .
                        '</td>

                        <td>' .
                            (int)$alert['stock'] .
                        '</td>

                        <td>' .
                            htmlspecialchars($alert['alert_level']) .
                        '</td>

                        <td>' .
                            htmlspecialchars($alert['expiry_date']) .
                        '</td>
                    </tr>
            ';
        }

        $htmlBody .= '
                </tbody>

            </table>

            <p style="margin-top:20px;">
                Please review the stock levels in the ValueMeds
                inventory system.
            </p>

            <p style="color:#777;font-size:12px;">
                This is an automated notification from ValueMeds.
            </p>

        </div>
        ';

        /*
        |--------------------------------------------------------------------------
        | Plain-text version
        |--------------------------------------------------------------------------
        */

        $plainBody =
            "ValueMeds Stock Alert\n\n" .
            "The following medicine stock levels require attention:\n\n";

        foreach ($recipientAlerts as $alert) {

            $plainBody .=
                "Product: " . $alert['name'] . "\n" .
                "Category: " . $alert['category'] . "\n" .
                "Stock: " . $alert['stock'] . "\n" .
                "Alert Level: " . $alert['alert_level'] . "\n" .
                "Expiry Date: " . $alert['expiry_date'] . "\n\n";
        }

        /*
        |--------------------------------------------------------------------------
        | Send
        |--------------------------------------------------------------------------
        */

        $sent = sendValueMedsEmail(
            $recipientEmail,
            $recipientName,
            $subject,
            $htmlBody,
            $plainBody
        );

        if ($sent) {

            /*
            |--------------------------------------------------------------------------
            | Only record the alert AFTER successful delivery
            |--------------------------------------------------------------------------
            */

            foreach ($recipientAlerts as $alert) {

                $stmt = $conn->prepare("
                    INSERT INTO stock_email_alerts
                    (
                        medicine_id,
                        recipient_email,
                        alert_level,
                        last_stock,
                        sent_at
                    )
                    VALUES
                    (
                        ?,
                        ?,
                        ?,
                        ?,
                        NOW()
                    )
                    ON DUPLICATE KEY UPDATE
                        alert_level = VALUES(alert_level),
                        last_stock = VALUES(last_stock),
                        sent_at = NOW()
                ");

                if (!$stmt) {

                    error_log(
                        'ValueMeds email: failed to prepare alert tracking query: ' .
                        $conn->error
                    );

                    $allRecipientsSucceeded = false;
                    continue;
                }

                $medicineId = (int)$alert['medicine_id'];
                $alertLevel = $alert['alert_level'];
                $stock = (int)$alert['stock'];

                $stmt->bind_param(
                    'issi',
                    $medicineId,
                    $recipientEmail,
                    $alertLevel,
                    $stock
                );

                if (!$stmt->execute()) {

                    error_log(
                        'ValueMeds email: failed to save alert state: ' .
                        $stmt->error
                    );

                    $allRecipientsSucceeded = false;
                }

                $stmt->close();
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | Do NOT record the alert.
            |
            | This allows it to be attempted again later.
            |--------------------------------------------------------------------------
            */

            $allRecipientsSucceeded = false;

            error_log(
                'ValueMeds email: failed to send stock summary to ' .
                $recipientEmail
            );
        }
    }

    return $allRecipientsSucceeded;
}