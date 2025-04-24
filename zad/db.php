<!-- db.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$host = 'localhost';
$db = 'cafe_management_system';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

function checkRole($allowedRoles) {
    if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], $allowedRoles)) {
        header('Location: index.php');
        exit;
    }
}
?>
