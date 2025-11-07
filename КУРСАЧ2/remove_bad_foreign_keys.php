<?php
require_once 'config.php';

try {
    // Удаляем проблемный внешний ключ
    $pdo->exec("ALTER TABLE news DROP FOREIGN KEY news_ibfk_1");
    echo "Проблемный внешний ключ news_ibfk_1 успешно удален!<br>";
    
    // Проверяем и удаляем другие возможные проблемные ключи
    $stmt = $pdo->query("SHOW CREATE TABLE news");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (strpos($result['Create Table'], 'CONSTRAINT') !== false) {
        // Если есть другие CONSTRAINT, которые ссылаются на archive, удаляем их
        $pdo->exec("ALTER TABLE news DROP FOREIGN KEY news_ibfk_5");
        echo "Дополнительные проблемные ключи удалены!<br>";
    }
    
    echo "База данных исправлена! Теперь добавление новостей должно работать.";
    
} catch(PDOException $e) {
    echo "Ошибка: " . $e->getMessage() . "<br>";
    
    // Альтернативный способ - через прямой SQL
    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        echo "Проверка внешних ключей отключена.<br>";
    } catch(PDOException $e2) {
        echo "Не удалось отключить проверку внешних ключей: " . $e2->getMessage();
    }
}
?>