<?php

require 'connection.php';

session_start();


/*
|--------------------------------------------------------------------------
| IF ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['id'])) {

    header("Location: register.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";


/*
|--------------------------------------------------------------------------
| REGISTER USER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
    |--------------------------------------------------------------------------
    | GET FORM DATA
    |--------------------------------------------------------------------------
    */

    $fullname = trim($_POST['fullname'] ?? '');

    $username = trim($_POST['username'] ?? '');

    $password = $_POST['password'] ?? '';

    $confirmPassword = $_POST['confirm_password'] ?? '';

    $role = trim($_POST['role'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $contactNo = trim($_POST['contact_no'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | VALIDATE REQUIRED FIELDS
    |--------------------------------------------------------------------------
    */

    if (
        $fullname === "" ||
        $username === "" ||
        $password === "" ||
        $confirmPassword === "" ||
        $role === ""
    ) {

        $error = "Please fill in all required fields.";

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE ROLE
    |--------------------------------------------------------------------------
    */

    elseif (
        $role !== "Cashier" &&
        $role !== "Pharmacist"
    ) {

        $error = "Invalid role selected.";

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE PASSWORD
    |--------------------------------------------------------------------------
    */

    elseif ($password !== $confirmPassword) {

        $error = "Passwords do not match.";

    }


    /*
    |--------------------------------------------------------------------------
    | PASSWORD LENGTH
    |--------------------------------------------------------------------------
    */

    elseif (strlen($password) < 6) {

        $error = "Password must be at least 6 characters.";

    }


    /*
    |--------------------------------------------------------------------------
    | CHECK USERNAME
    |--------------------------------------------------------------------------
    */

    else {

        $stmt = $conn->prepare("
            SELECT user_id
            FROM users
            WHERE username = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "s",
            $username
        );

        $stmt->execute();

        $result = $stmt->get_result();

        $existingUser = $result->fetch_assoc();

        $stmt->close();


        if ($existingUser) {

            $error = "Username already exists.";

        }

        else {


            /*
            |--------------------------------------------------------------------------
            | HASH PASSWORD
            |--------------------------------------------------------------------------
            */

            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            /*
            |--------------------------------------------------------------------------
            | INSERT USER
            |--------------------------------------------------------------------------
            */

            $stmt = $conn->prepare("
                INSERT INTO users
                (
                    fullname,
                    username,
                    password,
                    role,
                    email,
                    contact_no,
                    status
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    'Active'
                )
            ");


            $stmt->bind_param(
                "ssssss",
                $fullname,
                $username,
                $hashedPassword,
                $role,
                $email,
                $contactNo
            );


            if ($stmt->execute()) {

                $success =
                    "Account registered successfully.";

                /*
                |--------------------------------------------------------------------------
                | CLEAR FORM VALUES
                |--------------------------------------------------------------------------
                */

                $fullname = "";
                $username = "";
                $email = "";
                $contactNo = "";
                $role = "";

            }

            else {

                $error =
                    "Registration failed. Please try again.";

            }


            $stmt->close();

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
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        ValueMeds Registration
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

    box-sizing: border-box;

}


/* =========================================================
   BODY
========================================================= */

body {

    margin: 0;

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f4f6fb;

    font-family: Arial, sans-serif;

    padding: 20px;

}


/* =========================================================
   REGISTER BOX
========================================================= */

.register-box {

    width: 100%;

    max-width: 500px;

    background: white;

    padding: 35px;

    border-radius: 14px;

    box-shadow:
        0
        10px
        30px
        rgba(0, 0, 0, 0.10);

}


/* =========================================================
   HEADER
========================================================= */

.register-header {

    text-align: center;

    margin-bottom: 25px;

}


.register-header i {

    font-size: 45px;

    color: #16246D;

    margin-bottom: 10px;

}


.register-header h1 {

    margin: 0;

    color: #16246D;

}


.register-header p {

    margin-top: 8px;

    color: #777;

}


/* =========================================================
   FORM
========================================================= */

.form-row {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 15px;

}


.form-group {

    margin-bottom: 18px;

}


.form-group label {

    display: block;

    margin-bottom: 7px;

    font-weight: 600;

    color: #333;

}


.required {

    color: #c62828;

}


.form-group input,
.form-group select {

    width: 100%;

    padding: 12px;

    border:
        1px solid #ccc;

    border-radius: 8px;

    font-size: 14px;

    outline: none;

    background: white;

}


.form-group input:focus,
.form-group select:focus {

    border-color: #16246D;

    box-shadow:
        0
        0
        0
        2px
        rgba(22, 36, 109, 0.10);

}


/* =========================================================
   PASSWORD NOTE
========================================================= */

.password-note {

    font-size: 12px;

    color: #777;

    margin-top: 5px;

}


/* =========================================================
   BUTTON
========================================================= */

.register-button {

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


.register-button:hover {

    background: #2b45b5;

}


.register-button i {

    margin-right: 7px;

}


/* =========================================================
   ERROR
========================================================= */

.error-message {

    background: #ffe8e8;

    color: #c62828;

    padding: 10px;

    border-radius: 8px;

    margin-bottom: 18px;

    text-align: center;

    font-size: 14px;

}


/* =========================================================
   SUCCESS
========================================================= */

.success-message {

    background: #e8f5e9;

    color: #2e7d32;

    padding: 10px;

    border-radius: 8px;

    margin-bottom: 18px;

    text-align: center;

    font-size: 14px;

}


/* =========================================================
   LOGIN LINK
========================================================= */

.login-link {

    text-align: center;

    margin-top: 20px;

    font-size: 14px;

    color: #777;

}


.login-link a {

    color: #16246D;

    text-decoration: none;

    font-weight: 600;

}


.login-link a:hover {

    text-decoration: underline;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:600px) {

    .register-box {

        padding: 25px;

    }


    .form-row {

        grid-template-columns: 1fr;

        gap: 0;

    }

}

</style>

</head>


<body>


<div class="register-box">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="register-header">

        <i class="fas fa-user-plus"></i>

        <h1>
            ValueMeds
        </h1>

        <p>
            Create Staff Account
        </p>

    </div>



    <!-- =====================================================
         ERROR MESSAGE
    ====================================================== -->

    <?php if ($error !== ""): ?>

        <div class="error-message">

            <i class="fas fa-circle-exclamation"></i>

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         SUCCESS MESSAGE
    ====================================================== -->

    <?php if ($success !== ""): ?>

        <div class="success-message">

            <i class="fas fa-circle-check"></i>

            <?php
            echo htmlspecialchars($success);
            ?>

        </div>

    <?php endif; ?>



    <!-- =====================================================
         REGISTRATION FORM
    ====================================================== -->

    <form
        method="POST"
        autocomplete="off"
    >


        <!-- FULLNAME -->

        <div class="form-group">

            <label for="fullname">

                Full Name

                <span class="required">*</span>

            </label>

            <input
                type="text"
                id="fullname"
                name="fullname"
                placeholder="Enter full name"
                value="<?= htmlspecialchars($fullname ?? ''); ?>"
                maxlength="100"
                required
            >

        </div>



        <!-- USERNAME + ROLE -->

        <div class="form-row">


            <div class="form-group">

                <label for="username">

                    Username

                    <span class="required">*</span>

                </label>

                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Enter username"
                    value="<?= htmlspecialchars($username ?? ''); ?>"
                    maxlength="50"
                    autocomplete="username"
                    required
                >

            </div>



            <div class="form-group">

                <label for="role">

                    Role

                    <span class="required">*</span>

                </label>

                <select
                    id="role"
                    name="role"
                    required
                >

                    <option
                        value=""
                        disabled
                        <?= empty($role)
                            ? 'selected'
                            : ''; ?>
                    >
                        Select role
                    </option>

                    <option
                        value="Cashier"
                        <?= ($role ?? '') === 'Cashier'
                            ? 'selected'
                            : ''; ?>
                    >
                        Cashier
                    </option>

                    <option
                        value="Pharmacist"
                        <?= ($role ?? '') === 'Pharmacist'
                            ? 'selected'
                            : ''; ?>
                    >
                        Pharmacist
                    </option>

                </select>

            </div>


        </div>



        <!-- PASSWORD + CONFIRM PASSWORD -->

        <div class="form-row">


            <div class="form-group">

                <label for="password">

                    Password

                    <span class="required">*</span>

                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter password"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >

                <div class="password-note">

                    Minimum 6 characters

                </div>

            </div>



            <div class="form-group">

                <label for="confirm_password">

                    Confirm Password

                    <span class="required">*</span>

                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm password"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >

            </div>


        </div>



        <!-- EMAIL -->

        <div class="form-group">

            <label for="email">

                Email

                <span
                    style="font-weight: normal; color: #999;"
                >
                    (Optional)
                </span>

            </label>

            <input
                type="email"
                id="email"
                name="email"
                placeholder="Enter email"
                value="<?= htmlspecialchars($email ?? ''); ?>"
                maxlength="100"
            >

        </div>



        <!-- CONTACT NUMBER -->

        <div class="form-group">

            <label for="contact_no">

                Contact Number

                <span
                    style="font-weight: normal; color: #999;"
                >
                    (Optional)
                </span>

            </label>

            <input
                type="text"
                id="contact_no"
                name="contact_no"
                placeholder="Enter contact number"
                value="<?= htmlspecialchars($contactNo ?? ''); ?>"
                maxlength="30"
            >

        </div>



        <!-- REGISTER BUTTON -->

        <button
            type="submit"
            class="register-button"
        >

            <i class="fas fa-user-plus"></i>

            Create Account

        </button>


    </form>



    <!-- =====================================================
         LOGIN LINK
    ====================================================== -->

    <div class="login-link">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </div>


</div>


</body>

</html>