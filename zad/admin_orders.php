<?php
require 'db.php';
checkRole(['admin']);

// Фильтрация по смене
$filter_shift = $_GET['shift_id'] ?? '';

$shifts = $pdo->query("SELECT * FROM shifts ORDER BY start_time DESC")->fetchAll();

$query = "SELECT o.*, u.full_name as waiter_name, s.start_time, s.end_time 
          FROM orders o
          JOIN users u ON o.waiter_id = u.id
          JOIN shifts s ON o.shift_id = s.id";

$params = [];
if ($filter_shift) {
    $query .= " WHERE o.shift_id = ?";
    $params[] = $filter_shift;
}

$query .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll();

?>

<link rel="stylesheet" href="style.css">
<div class="container">
    <div class="current-user">
        <?= $_SESSION['user']['full_name'] ?> (<?= $_SESSION['user']['role'] ?>)
        <a href="logout.php">Выйти</a>
    </div>

    <h2>Все заказы</h2>

    <form method="get" style="margin-bottom: 20px;">
        <label>Фильтр по смене:</label>
        <select name="shift_id" onchange="this.form.submit()">
            <option value="">Все смены</option>
            <?php foreach ($shifts as $shift): ?>
                <option value="<?= $shift['id'] ?>" <?= $filter_shift == $shift['id'] ? 'selected' : '' ?>>
                    <?= $shift['start_time'] ?> — <?= $shift['end_time'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <table>
        <tr>
            <th>ID заказа</th>
            <th>Стол</th>
            <th>Статус</th>
            <th>Официант</th>
            <th>Смена</th>
            <th>Дата создания</th>
        </tr>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td><?= $order['table_number'] ?></td>
                <td><?= $order['status'] ?></td>
                <td><?= htmlspecialchars($order['waiter_name']) ?></td>
                <td><?= $order['start_time'] ?> — <?= $order['end_time'] ?></td>
                <td><?= $order['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <p><a href="index.php">Назад</a></p>
</div>
