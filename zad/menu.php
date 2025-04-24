<?php
require 'db.php';
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$menuItems = $pdo->query("SELECT * FROM menu_items ORDER BY category, name")->fetchAll();
?>

<link rel="stylesheet" href="style.css">
<div class="container">
    <div class="current-user">
        <?= $_SESSION['user']['full_name'] ?> (<?= $_SESSION['user']['role'] ?>)
        <a href="logout.php">Выйти</a>
    </div>

    <h2>Меню кафе</h2>
    <?php
    $currentCategory = '';
    foreach ($menuItems as $item):
        if ($item['category'] !== $currentCategory):
            if ($currentCategory !== '') echo '</div>';
            echo '<h3>' . htmlspecialchars($item['category']) . '</h3><div>';
            $currentCategory = $item['category'];
        endif;
    ?>
        <div class="menu-item">
            <b><?= htmlspecialchars($item['name']) ?></b> — <?= number_format($item['price'], 2) ?> руб.<br>
            <small><?= htmlspecialchars($item['description']) ?></small>
        </div>
    <?php endforeach; ?>
    </div>

    <p><a href="index.php">Назад</a></p>
</div>
