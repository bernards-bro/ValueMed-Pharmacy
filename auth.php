<?php

if (session_status() === PHP_SESSION_NONE) {

    session_start();

}


/*
|--------------------------------------------------------------------------
| LOGIN CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['id'])) {

    header("Location: index.php");
    exit;

}



/*
|--------------------------------------------------------------------------
| ROLE CHECK FUNCTION
|--------------------------------------------------------------------------
*/

function requireRole($allowedRoles = [])
{

    if (!isset($_SESSION['role'])) {

        header("Location: index.php");
        exit;

    }


    $userRole = $_SESSION['role'];


    if (!in_array($userRole, $allowedRoles)) {

        header("Location: pos.php");
        exit;

    }

}

?>