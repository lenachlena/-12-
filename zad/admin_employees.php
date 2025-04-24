<!-- admin_employees.php -->
<?php
require 'db.php';
checkRole(['admin']);

// Обработка удаления
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

// Обработка добавления/редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'login' => $_POST['login'],
        'password' => $_POST['password'],
        'full_name' => $_POST['full_name'],
        'role' => $_POST['role']
    ];

    if ($_POST['id']) {
        $stmt = $pdo->prepare("UPDATE users SET 
            login = ?, password = ?, full_name = ?, role = ? 
            WHERE id = ?");
        $data[] = $_POST['id'];
        $stmt->execute(array_values($data));
    } else {
        $stmt = $pdo->prepare("INSERT INTO users 
            (login, password, full_name, role) 
            VALUES (?,?,?,?)");
        $stmt->execute(array_values($data));
    }
}

$users = $pdo->query("SELECT * FROM users")->fetchAll();
$editUser = isset($_GET['edit']) ? $pdo->query("SELECT * FROM users WHERE id = ".$_GET['edit'])->fetch() : null;
?>

<link rel="stylesheet" href="style.css">
<div class="container">
    <div class="current-user">
        <?= $_SESSION['user']['full_name'] ?> (<?= $_SESSION['user']['role'] ?>)
        <a href="logout.php">Выйти</a>
    </div>

    <h2>Управление сотрудниками</h2>
    
    <div class="form-edit">
        <h3><?= $editUser ? 'Редактировать' : 'Добавить' ?> сотрудника</h3>
        <form method="post">
            <input type="hidden" name="id" value="<?= $editUser['id'] ?? '' ?>">
            <input type="text" name="login" placeholder="Логин" value="<?= $editUser['login'] ?? '' ?>" required>
            <input type="password" name="password" placeholder="Пароль" required>
            <input type="text" name="full_name" placeholder="ФИО" value="<?= $editUser['full_name'] ?? '' ?>" required>
            <select name="role" required>
                <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Админ</option>
                <option value="waiter" <?= ($editUser['role'] ?? '') === 'waiter' ? 'selected' : '' ?>>Официант</option>
                <option value="cook" <?= ($editUser['role'] ?? '') === 'cook' ? 'selected' : '' ?>>Повар</option>
            </select>
            <button type="submit">Сохранить</button>
        </form>
    </div>

    <table>
        <tr>
            <th>ФИО</th>
            <th>Логин</th>
            <th>Роль</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['full_name'] ?></td>
            <td><?= $user['login'] ?></td>
            <td><?= $user['role'] ?></td>
            <td class="actions">
                <a href="?edit=<?= $user['id'] ?>">✏️</a>
                <a href="?delete=<?= $user['id'] ?>" onclick="return confirm('Удалить?')">❌</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="index.php">Назад</a></p>
</div>
