
<?php

session_start();

/*
    Clear the current session.
*/
$_SESSION = [];

session_destroy();

header("Location: register.php");
exit;

