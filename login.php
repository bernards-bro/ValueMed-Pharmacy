<?php

require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
    If already logged in, go directly to POS.
*/

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === "" || $password === "") {

        $error = "Please enter your username and password.";

    } else {

        $stmt = $conn->prepare("
            SELECT
                user_id,
                fullname,
                username,
                password,
                status
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $stmt->close();

        if (!$user) {

            $error = "Invalid username or password.";

        } elseif ($user['status'] !== 'Active') {

            $error = "This account is inactive.";

        } elseif (!password_verify($password, $user['password'])) {

            $error = "Invalid username or password.";

        } else {

            /*
                Login successful.
            */

            session_regenerate_id(true);

            $_SESSION['id'] = (int)$user['user_id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? '';

            header("Location: pos.php");
            exit;
        }
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

    <title>ValueMeds Login</title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f4f6fb;

            font-family: Arial, sans-serif;
        }

        .login-box {
            width: 100%;
            max-width: 400px;

            background: white;

            padding: 35px;

            border-radius: 14px;

            box-shadow:
                0 10px 30px rgba(0, 0, 0, 0.10);
        }

        .login-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .login-header i {
            font-size: 45px;
            color: #16246D;
            margin-bottom: 10px;
        }

        .login-header h1 {
            margin: 0;
            color: #16246D;
        }

        .login-header p {
            margin-top: 8px;
            color: #777;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;
            border-radius: 8px;

            font-size: 14px;

            outline: none;
        }

        .form-group input:focus {
            border-color: #16246D;

            box-shadow:
                0 0 0 2px rgba(22, 36, 109, 0.10);
        }

        .login-button {
            width: 100%;

            padding: 13px;

            border: none;
            border-radius: 8px;

            background: #16246D;
            color: white;

            font-size: 15px;
            font-weight: 600;

            cursor: pointer;
        }

        .login-button:hover {
            background: #2b45b5;
        }

        .error-message {
            background: #ffe8e8;
            color: #c62828;

            padding: 10px;

            border-radius: 8px;

            margin-bottom: 18px;

            text-align: center;

            font-size: 14px;

        }
        .register-button {

    display: block;

    width: 100%;

    margin-top: 12px;

    padding: 13px;

    border-radius: 8px;

    background: #EEF4FF;

    color: #16246D;

    text-align: center;

    text-decoration: none;

    font-size: 15px;

    font-weight: 600;

    transition: .3s;

}


.register-button:hover {

    background: #dce8ff;

}
    </style>

</head>

<body>

    <div class="login-box">

        <div class="login-header">

            <i class="fas fa-pills"></i>

            <h1>ValueMeds</h1>

            <p>Pharmacy Management System</p>

        </div>

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">

                <label for="username">
                    Username
                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter username"
                    autocomplete="username"
                    required>

            </div>

            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    autocomplete="current-password"
                    required>

            </div>

            <button
    type="submit"
    class="login-button">

    <i class="fas fa-sign-in-alt"></i>
    Login

</button>


<a
    href="register.php"
    class="register-button">

    <i class="fas fa-user-plus"></i>
    Create Account

</a>

        </form>

    </div>

</body>

</html>
