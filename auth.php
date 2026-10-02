<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Allowed Roles
|--------------------------------------------------------------------------
*/

const SYSTEM_ROLES = [
    "Admin",
    "Pharmacist",
    "Cashier"
];



function requireLogin()
{

    if (!isset($_SESSION['id'])) {

        header("Location: login.php");
        exit;

    }

}



function getRole()
{
    return $_SESSION['role'] ?? null;
}




function validateRole()
{

    requireLogin();


    if (!in_array(getRole(), SYSTEM_ROLES)) {


        session_destroy();

        header("Location: login.php");

        exit;

    }

}




/*
|--------------------------------------------------------------------------
| Allow Specific Roles
|--------------------------------------------------------------------------
*/

function allowRoles($roles = [])
{

    validateRole();


    if (!in_array(getRole(), $roles)) {


        header("Location: unauthorized.php");

        exit;

    }

}



/*
|--------------------------------------------------------------------------
| Pages for Everyone Logged In
|--------------------------------------------------------------------------
*/

function allUsers()
{

    validateRole();

}




/*
|--------------------------------------------------------------------------
| Cashier Only
|--------------------------------------------------------------------------
*/

function cashierOnly()
{

    allowRoles([
        "Cashier"
    ]);

}




/*
|--------------------------------------------------------------------------
| Admin + Pharmacist
|--------------------------------------------------------------------------
*/

function managementOnly()
{

    allowRoles([
        "Admin",
        "Pharmacist"
    ]);

}

?>