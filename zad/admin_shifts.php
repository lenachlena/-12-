<?php
require 'db.php';
checkRole(['admin']);

// Обработка удаления смены
if (isset($_GET['delete_shift'])) {
    $stmt = $pdo->prepare("DELETE FROM shifts WHERE id = ?");
    $stmt->execute([$_GET['delete_shift']]);
    header('Location: admin_shifts.php');
    exit;
}

// Обработка удаления сотрудника со смены
if (isset($_GET['delete_worker'])) {
    $stmt = $pdo->prepare("DELETE FROM shift_workers WHERE id = ?");
    $stmt->execute([$_GET['delete_worker']]);
    header('Location: admin_shifts.php');
    exit;
}

// Добавление/редактирование смены
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_shift'])) {
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $shift_id = $_POST['shift_id'] ?? null;

    if ($shift_id) {
        $stmt = $pdo->prepare("UPDATE shifts SET start_time = ?, end_time = ? WHERE id = ?");
        $stmt->execute([$start, $end, $shift_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO shifts (start_time, end_time) VALUES (?, ?)");
        $stmt->execute([$start, $end]);
    }
    header('Location: admin_shifts.php');
    exit;
}

// Добавление сотрудника на смену
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_worker'])) {
    $shift_id = $_POST['shift_id_worker'];
    $user_id = $_POST['user_id'];
    $role = $_POST['role_worker'];

    // Проверим, что такого сотрудника на смене нет
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM shift_workers WHERE shift_id = ? AND user_id = ?");
    $stmt->execute([$shift_id, $user_id]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO shift_workers (shift_id, user_id, role) VALUES (?, ?, ?)");
        $stmt->execute([$shift_id, $user_id, $role]);
    }
    header('Location: admin_shifts.php');
    exit;
}

// Получаем смены
$shifts = $pdo->query("SELECT * FROM shifts ORDER BY start_time DESC")->fetchAll();

// Получаем всех сотрудников (официанты и повара)
$staff = $pdo->query("SELECT id, full_name, role FROM users WHERE role IN ('waiter', 'cook')")->fetchAll();

// Для каждого сдвига получаем сотрудников
$shift_workers = [];
foreach ($shifts as $shift) {
    $stmt = $pdo->prepare("
        SELECT sw.id as sw_id, u.full_name, u.role 
        FROM shift_workers sw 
        JOIN users u ON sw.user_id = u.id 
        WHERE sw.shift_id = ?");
    $stmt->execute([$shift['id']]);
    $shift_workers[$shift['id']] = $stmt->fetchAll();
}

?>

<link rel="stylesheet" href="style.css">
<div class="container">
    <div class="current-user">
        <?= $_SESSION['user']['full_name'] ?> (<?= $_SESSION['user']['role'] ?>)
        <a href="logout.php">Выйти</a>
    </div>
    <h2>Управление сменами</h2>

    <h3>Добавить / Редактировать смену</h3>
    <form method="post">
        <input type="hidden" name="shift_id" value="<?= $_GET['edit_shift'] ?? '' ?>">
        <label>Начало смены:</label>
        <input type="datetime-local" name="start_time" required value="<?php
            if (isset($_GET['edit_shift'])) {
                $shift = $pdo->prepare("SELECT * FROM shifts WHERE id = ?");
                $shift->execute([$_GET['edit_shift']]);
                $s = $shift->fetch();
                echo date('Y-m-d\TH:i', strtotime($s['start_time']));
            }
        ?>">
        <label>Конец смены:</label>
        <input type="datetime-local" name="end_time" required value="<?php
            if (isset($_GET['edit_shift'])) {
                echo date('Y-m-d\TH:i', strtotime($s['end_time']));
            }
        ?>">
        <button type="submit" name="save_shift">Сохранить смену</button>
    </form>

    <h3>Список смен</h3>
    <?php foreach ($shifts as $shift): ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
            <b>Смена #<?= $shift['id'] ?>:</b> <?= $shift['start_time'] ?> — <?= $shift['end_time'] ?>
            <div class="actions">
                <a href="?edit_shift=<?= $shift['id'] ?>">✏️ Редактировать</a> |
                <a href="?delete_shift=<?= $shift['id'] ?>" onclick="return confirm('Удалить смену?')">❌ Удалить</a>
            </div>

            <h4>Сотрудники на смене</h4>
            <ul>
                <?php foreach ($shift_workers[$shift['id']] as $worker): ?>
                    <li>
                        <?= htmlspecialchars($worker['full_name']) ?> (<?= $worker['role'] ?>)
                        <a href="?delete_worker=<?= $worker['sw_id'] ?>" onclick="return confirm('Удалить сотрудника со смены?')">❌</a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <form method="post" style="margin-top: 10px;">
                <input type="hidden" name="shift_id_worker" value="<?= $shift['id'] ?>">
                <select name="user_id" required>
                    <option value="">Выберите сотрудника</option>
                    <?php foreach ($staff as $person): ?>
                        <option value="<?= $person['id'] ?>">
                            <?= htmlspecialchars($person['full_name']) ?> (<?= $person['role'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="role_worker" required>
                    <option value="waiter">Официант</option>
                    <option value="cook">Повар</option>
                </select>
                <button type="submit" name="add_worker">Добавить на смену</button>
            </form>
        </div>
    <?php endforeach; ?>

    <p><a href="index.php">Назад</a></p>
</div>
