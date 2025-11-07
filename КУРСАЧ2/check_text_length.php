<?php
$host = '127.0.0.1:3306';
$dbname = 'smena';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>Проверка структуры таблицы news</h2>";
    
    // Получаем информацию о столбце text
    $stmt = $pdo->query("SHOW COLUMNS FROM news LIKE 'text'");
    $column_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Информация о поле 'text':</h3>";
    echo "<pre>";
    print_r($column_info);
    echo "</pre>";
    
    // Проверяем максимальную длину текста
    $stmt = $pdo->query("SELECT MAX(LENGTH(text)) as max_length FROM news");
    $max_length = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p><strong>Максимальная длина текста в существующих новостях:</strong> " . $max_length['max_length'] . " символов</p>";
    
} catch(PDOException $e) {
    echo "<h3 style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</h3>";
}
?>