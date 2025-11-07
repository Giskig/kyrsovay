<?php
session_start();
require_once 'config.php';

echo "<h1>Отладка сессии и прав</h1>";

echo "<h3>Данные сессии:</h3>";
echo "<pre>";
var_dump($_SESSION);
echo "</pre>";

echo "<h3>GET параметры:</h3>";
echo "<pre>";
var_dump($_GET);
echo "</pre>";

// Проверяем существование новости
if (isset($_GET['id'])) {
    $news_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM news WHERE id_nwes = ?");
    $stmt->execute([$news_id]);
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Данные новости (ID: $news_id):</h3>";
    echo "<pre>";
    var_dump($news);
    echo "</pre>";
}

echo '<br><a href="manage_news.php">Вернуться к управлению новостями</a>';
?>