<?php

require 'connection.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| ADMIN ACCESS ONLY
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id'])) {

    header("Location: login.php");
    exit;

}


if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'Admin'
) {

    header("Location: dashboard.php");
    exit;

}



/*
|--------------------------------------------------------------------------
| CSRF TOKEN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));

}



/*
|--------------------------------------------------------------------------
| INITIAL VALUES
|--------------------------------------------------------------------------
*/

$error = "";
$success = "";

$fullname = "";
$username = "";
$email = "";
$contact = "";

$role = "Cashier";
$status = "Active";



/*
|--------------------------------------------------------------------------
| CREATE USER
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
    |--------------------------------------------------------------------------
    | VERIFY CSRF
    |--------------------------------------------------------------------------
    */

    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {

        die("Invalid request.");

    }



    /*
    |--------------------------------------------------------------------------
    | GET FORM DATA
    |--------------------------------------------------------------------------
    */

    $fullname =
        trim($_POST['fullname'] ?? '');


    $username =
        strtolower(
            trim($_POST['username'] ?? '')
        );


    $email =
        trim($_POST['email'] ?? '');


    $contact =
        trim($_POST['contact_no'] ?? '');


    $password =
        $_POST['password'] ?? '';


    $confirmPassword =
        $_POST['confirm_password'] ?? '';




    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */


    if (
        $fullname === "" ||
        $username === "" ||
        $password === "" ||
        $confirmPassword === ""
    ) {


        $error =
            "Please complete all required fields.";


    }


    elseif (strlen($fullname) > 100) {


        $error =
            "Full name is too long.";


    }


    elseif (strlen($username) > 50) {


        $error =
            "Username is too long.";


    }


    elseif (
        $email !== "" &&
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {


        $error =
            "Please enter a valid email address.";


    }


    elseif (strlen($email) > 100) {


        $error =
            "Email address is too long.";


    }


    elseif (strlen($contact) > 30) {


        $error =
            "Contact number is too long.";


    }


    elseif (strlen($password) < 8) {


        $error =
            "Password must contain at least 8 characters.";


    }


    elseif ($password !== $confirmPassword) {


        $error =
            "Passwords do not match.";


    }



    else {


        /*
        |--------------------------------------------------------------------------
        | CHECK DUPLICATE USERNAME
        |--------------------------------------------------------------------------
        */


        $stmt =
            $conn->prepare("
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



        $result =
            $stmt->get_result();



        if ($result->num_rows > 0) {


            $error =
                "Username already exists.";


            $stmt->close();



        }


        else {


            $stmt->close();



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
            | OPTIONAL FIELDS
            |--------------------------------------------------------------------------
            */


            $emailValue =
                $email !== ""
                ? $email
                : null;



            $contactValue =
                $contact !== ""
                ? $contact
                : null;




            /*
            |--------------------------------------------------------------------------
            | INSERT CASHIER ACCOUNT
            |--------------------------------------------------------------------------
            */


            $stmt =
                $conn->prepare("
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
                        ?
                    )
                ");



            $stmt->bind_param(
                "sssssss",
                $fullname,
                $username,
                $hashedPassword,
                $role,
                $emailValue,
                $contactValue,
                $status
            );



            if ($stmt->execute()) {


                $success =
                    "Cashier account created successfully.";



                /*
                Clear fields
                */

                $fullname = "";
                $username = "";
                $email = "";
                $contact = "";



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
content="width=device-width, initial-scale=1.0">


<title>
ValueMeds Registration
</title>


<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


<style>


*{
box-sizing:border-box;
}


body{

margin:0;

min-height:100vh;

display:flex;

align-items:center;

justify-content:center;

background:#f4f6fb;

font-family:Arial,sans-serif;

}



.register-box{

width:100%;

max-width:430px;

background:white;

padding:35px;

border-radius:14px;

box-shadow:
0 10px 30px rgba(0,0,0,.10);

}



.register-header{

text-align:center;

margin-bottom:25px;

}



.register-header i{

font-size:45px;

color:#16246D;

margin-bottom:10px;

}



.register-header h1{

margin:0;

color:#16246D;

}



.register-header p{

margin-top:8px;

color:#777;

}



.form-group{

margin-bottom:18px;

}



.form-group label{

display:block;

margin-bottom:7px;

font-weight:600;

}



.form-group input{

width:100%;

padding:12px;

border:1px solid #ccc;

border-radius:8px;

font-size:14px;

outline:none;

}



.form-group input:focus{

border-color:#16246D;

box-shadow:
0 0 0 2px rgba(22,36,109,.10);

}



.register-button{

width:100%;

padding:13px;

border:none;

border-radius:8px;

background:#16246D;

color:white;

font-size:15px;

font-weight:600;

cursor:pointer;

}



.register-button:hover{

background:#2b45b5;

}



.message{

padding:10px;

border-radius:8px;

margin-bottom:18px;

text-align:center;

font-size:14px;

}



.error{

background:#ffe8e8;

color:#c62828;

}



.success{

background:#e4f8e8;

color:#176b2c;

}



.login-link{

text-align:center;

margin-top:18px;

font-size:14px;

}



.login-link a{

color:#16246D;

font-weight:bold;

text-decoration:none;

}


</style>


</head>


<body>


<div class="register-box">


<div class="register-header">

<i class="fas fa-user-plus"></i>


<h1>
ValueMeds
</h1>


<p>
Create Cashier Account
</p>


</div>



<?php if ($error): ?>

<div class="message error">

<?php echo htmlspecialchars($error); ?>

</div>

<?php endif; ?>



<?php if ($success): ?>

<div class="message success">

<?php echo htmlspecialchars($success); ?>

</div>

<?php endif; ?>



<form method="POST">
<input
type="hidden"
name="csrf_token"
value="<?php echo $_SESSION['csrf_token']; ?>">

<div class="form-group">

<label>
Full Name
</label>


<input
    type="text"
    name="fullname"
    placeholder="Enter full name"
    value="<?php echo htmlspecialchars($fullname); ?>"
    required>


</div>

<div class="form-group">

<label>
Email
</label>

<input
type="email"
name="email"
placeholder="Enter email"
value="<?php echo htmlspecialchars($email); ?>">

</div>

<div class="form-group">

<label>
Contact Number
</label>

<input
    type="text"
    name="contact_no"
    placeholder="Enter contact number"
    value="<?php echo htmlspecialchars($contact); ?>">

</div>

<div class="form-group">

<label>
Username
</label>


<input
    type="text"
    name="username"
    placeholder="Enter username"
    value="<?php echo htmlspecialchars($username); ?>"
    required>


</div>



<div class="form-group">

<label>
Password
</label>


<input
type="password"
name="password"
placeholder="Enter password"
required>


</div>



<div class="form-group">

<label>
Confirm Password
</label>


<input
type="password"
name="confirm_password"
placeholder="Confirm password"
required>


</div>



<button
type="submit"
class="register-button">


<i class="fas fa-user-plus"></i>

Register


</button>



</form>



<div class="login-link">

Already have an account?

<a href="login.php">
Login
</a>


</div>



</div>


</body>

</html>