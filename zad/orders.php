
<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'waiter') header('Location: index.php');
require 'db.php';

$waiter_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE waiter_id = ?");
$stmt->execute([$waiter_id]);
$orders = $stmt->fetchAll();
?>
<link rel="stylesheet" href="style.css">
<div class="container">
    <h2>Мои заказы</h2>
    <div class="order-list">
        <?php foreach ($orders as $order): ?>
            <div class="order-item">
                <b>Стол №<?= $order['table_number'] ?></b>
                <br>Статус: <?= htmlspecialchars($order['status']) ?>
                <br>Создан: <?= $order['created_at'] ?>
            </div>
        <?php endforeach; ?>
    </div>
    <p><a href="index.php">Назад</a></p>
</div>
