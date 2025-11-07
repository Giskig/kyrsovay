<?php
session_start();

$host = '127.0.0.1:3306';
$dbname = 'smena';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // ЭКСТРЕННОЕ РЕШЕНИЕ: отключаем проверку внешних ключей глобально
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

function logError($message) {
    file_put_contents('error.log', date('Y-m-d H:i:s') . ' - ' . $message . PHP_EOL, FILE_APPEND);
}


// Функция для принудительной авторизации
function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?auth_required=1');
        exit;
    }
}
?>