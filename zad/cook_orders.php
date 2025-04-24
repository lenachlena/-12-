<!-- cook_orders.php -->
<?php
require 'db.php';
checkRole(['cook']);

// Обновление статуса блюда
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE order_items 
        SET status = ?, cook_id = ? 
        WHERE id = ?");
    $stmt->execute([
        $_POST['status'],
        $_SESSION['user']['id'],
        $_POST['item_id']
    ]);
    header("Location: cook_orders.php");
    exit;
}

$currentShift = $pdo->query("SELECT * FROM shifts 
    WHERE start_time <= NOW() AND end_time >= NOW() 
    LIMIT 1")->fetch();

$orders = [];
if ($currentShift) {
    $orders = $pdo->prepare("
        SELECT oi.*, m.name as item_name 
        FROM order_items oi
        JOIN menu_items m ON oi.menu_item_id = m.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.shift_id = ?
    ");
    $orders->execute([$currentShift['id']]);
    $orders = $orders->fetchAll();
}
?>

<link rel="stylesheet" href="style.css">
<div class="container">
    <div class="current-user">
        <?= $_SESSION['user']['full_name'] ?> (<?= $_SESSION['user']['role'] ?>)
        <a href="logout.php">Выйти</a>
    </div>

    <h2>Текущие заказы (Повар)</h2>
    
    <?php if ($currentShift): ?>
    <div class="grid-container">
        <?php foreach ($orders as $order): ?>
        <div class="order-item">
            <b><?= $order['item_name'] ?></b>
            <p>Количество: <?= $order['quantity'] ?></p>
            <form method="post">
                <input type="hidden" name="item_id" value="<?= $order['id'] ?>">
                <select name="status" onchange="this.form.submit()">
                    <option value="waiting" <?= $order['status'] == 'waiting' ? 'selected' : '' ?>>Ожидает</option>
                    <option value="in_progress" <?= $order['status'] == 'in_progress' ? 'selected' : '' ?>>Готовится</option>
                    <option value="ready" <?= $order['status'] == 'ready' ? 'selected' : '' ?>>Готово</option>
                </select>
                <input type="hidden" name="update_status" value="1">
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <p>Сейчас нет активных смен</p>
    <?php endif; ?>
    <p><a href="index.php">Назад</a></p>
</div>
