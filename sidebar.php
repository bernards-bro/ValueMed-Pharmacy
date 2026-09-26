<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentPage = basename($_SERVER['PHP_SELF']);

?>
<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar">

<div class="logo">
    ValueMeds
</div>


<a href="dashboard.php"
class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">

<i class="fas fa-home"></i>
Dashboard

</a>


<div class="menu-title">
INVENTORY
</div>


<a href="products.php"
class="<?= $currentPage == 'products.php' ? 'active' : '' ?>">

<i class="fas fa-pills"></i>
Products

</a>


<a href="inventory.php"
class="<?= $currentPage == 'inventory.php' ? 'active' : '' ?>">

<i class="fas fa-box-open"></i>
Inventory

</a>


<a href="stock_alerts.php"
class="<?= $currentPage == 'stock_alerts.php' ? 'active' : '' ?>">

<i class="fas fa-triangle-exclamation"></i>
Stock Alerts

</a>



<div class="menu-title">
SALES
</div>


<a href="pos.php"
class="<?= $currentPage == 'pos.php' ? 'active' : '' ?>">

<i class="fas fa-cash-register"></i>
Point of Sale

</a>


<a href="sales_history.php"
class="<?= $currentPage == 'sales_history.php' ? 'active' : '' ?>">

<i class="fas fa-clock-rotate-left"></i>
Sales History

</a>


<a href="reports.php"
class="<?= $currentPage == 'reports.php' ? 'active' : '' ?>">

<i class="fas fa-chart-column"></i>
Reports

</a>



<?php if (
    isset($_SESSION['role']) &&
    $_SESSION['role'] === 'Admin'
): ?>


<div class="menu-title">
ADMINISTRATION
</div>


<a href="register.php"
class="<?= $currentPage == 'register.php' ? 'active' : '' ?>">

<i class="fas fa-user-plus"></i>

Users

</a>


<?php endif; ?>


</div>