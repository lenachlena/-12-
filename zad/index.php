<!-- index.php -->
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
$user = $_SESSION['user'];
?>

<link rel="stylesheet" href="style.css">
<div class="container">
    <div class="current-user">
        <?= $user['full_name'] ?> (<?= $user['role'] ?>)
        <a href="logout.php">Выйти</a>
    </div>

    <h2>Главное меню</h2>
    <ul>
        <?php if ($user['role'] === 'admin'): ?>
            <li><a href="admin_employees.php">Управление сотрудниками</a></li>
            <li><a href="admin_shifts.php">Управление сменами</a></li>
            <li><a href="admin_orders.php">Все заказы</a></li>
        <?php elseif ($user['role'] === 'waiter'): ?>
            <li><a href="waiter_orders.php">Мои заказы</a></li>
        <?php elseif ($user['role'] === 'cook'): ?>
            <li><a href="cook_orders.php">Текущие заказы</a></li>
        <?php endif; ?>
        <li><a href="menu.php">Меню кафе</a></li>
    </ul>
</div>
