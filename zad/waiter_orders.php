<!-- waiter_orders.php -->
<?php
require 'db.php';
checkRole(['waiter']);

// Создание нового заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $stmt = $pdo->prepare("INSERT INTO orders 
        (table_number, status, shift_id, waiter_id) 
        VALUES (?, 'created', ?, ?)");
    $stmt->execute([
        $_POST['table_number'],
        $_POST['shift_id'],
        $_SESSION['user']['id']
    ]);
    $orderId = $pdo->lastInsertId();
    
    // Добавление элементов заказа
    foreach ($_POST['items'] as $item) {
        $stmt = $pdo->prepare("INSERT INTO order_items 
            (order_id, menu_item_id, quantity) 
            VALUES (?,?,?)");
        $stmt->execute([$orderId, $item['id'], $item['quantity']]);
    }
    
    header("Location: waiter_orders.php");
    exit;
}

// Изменение статуса заказа
if (isset($_GET['change_status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_GET['new_status'], $_GET['order_id']]);
    header("Location: waiter_orders.php");
    exit;
}

$orders = $pdo->prepare("SELECT * FROM orders WHERE waiter_id = ?");
$orders->execute([$_SESSION['user']['id']]);
$orders = $orders->fetchAll();

$shifts = $pdo->query("SELECT * FROM shifts")->fetchAll();
$menuItems = $pdo->query("SELECT * FROM menu_items")->fetchAll();
?>

<link rel="stylesheet" href="style.css">
<div class="container">
    <div class="current-user">
        <?= $_SESSION['user']['full_name'] ?> (<?= $_SESSION['user']['role'] ?>)
        <a href="logout.php">Выйти</a>
    </div>

    <h2>Управление заказами (Официант)</h2>
    
    <h3>Новый заказ</h3>
    <form method="post" id="orderForm">
        <div class="grid-container">
            <div>
                <label>Номер стола:</label>
                <input type="number" name="table_number" required>
            </div>
            <div>
                <label>Смена:</label>
                <select name="shift_id" required>
                    <?php foreach ($shifts as $shift): ?>
                    <option value="<?= $shift['id'] ?>">
                        <?= $shift['start_time'] ?> - <?= $shift['end_time'] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div id="itemsContainer">
            <div class="item">
                <select name="items[0][id]" required>
                    <?php foreach ($menuItems as $item): ?>
                    <option value="<?= $item['id'] ?>">
                        <?= $item['name'] ?> (<?= $item['price'] ?> руб.)
                    </option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="items[0][quantity]" value="1" min="1" required>
            </div>
        </div>
        <button type="button" onclick="addItem()">+ Добавить позицию</button>
        <button type="submit" name="create_order">Создать заказ</button>
    </form>

    <h3>Список заказов</h3>
    <div class="order-list">
        <?php foreach ($orders as $order): ?>
        <div class="order-item">
            <b>Заказ #<?= $order['id'] ?> (Стол <?= $order['table_number'] ?>)</b>
            <p>Статус: <?= $order['status'] ?></p>
            <div class="actions">
                <?php if ($order['status'] != 'closed'): ?>
                    <form class="status-form" method="get">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="new_status" onchange="this.form.submit()">
                            <option value="created" <?= $order['status'] == 'created' ? 'selected' : '' ?>>Создан</option>
                            <option value="paid" <?= $order['status'] == 'paid' ? 'selected' : '' ?>>Оплачен</option>
                            <option value="closed" <?= $order['status'] == 'closed' ? 'selected' : '' ?>>Закрыт</option>
                        </select>
                        <input type="hidden" name="change_status" value="1">
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <p><a href="index.php">Назад</a></p>
</div>

<script>
let itemCount = 1;
function addItem() {
    const container = document.getElementById('itemsContainer');
    const newItem = document.createElement('div');
    newItem.className = 'item';
    newItem.innerHTML = `
        <select name="items[${itemCount}][id]" required>
            <?php foreach ($menuItems as $item): ?>
            <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="items[${itemCount}][quantity]" value="1" min="1" required>
    `;
    container.appendChild(newItem);
    itemCount++;
}
</script>
