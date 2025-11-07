<?php
require_once 'config.php';  // Первым - настройки БД и сессии
require_once 'auth.php';    // Вторым - функции авторизации

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Получаем только ОПУБЛИКОВАННЫЕ новости (статус 2)
$stmt = $pdo->prepare("
    SELECT n.*, c.title as category_title, u.name, u.lastname, s.title as status_title 
    FROM news n 
    LEFT JOIN categories c ON n.categories_id = c.categories_id 
    LEFT JOIN users u ON n.id_user = u.id_user 
    LEFT JOIN status s ON n.id_status = s.id_status 
    WHERE n.id_status = 2 
    ORDER BY n.date_relise DESC
");
$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

$title = "Новости - Лагерь Смена";
require_once 'header.php';
?>

<div class="container">
    <h2>Новостной блок</h2>
    
    <?php if (getUserRole() == 1 || getUserRole() == 2): ?>
        <div class="news-actions-header">
            <a href="add_news.php" class="btn">Добавить новость</a>
            <?php if (getUserRole() == 1): ?>
                <a href="moderation.php" class="btn" style="background: #e74c3c;">⚡ Панель модерации</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="news-grid">
        <?php if (empty($news)): ?>
            <div class="empty-news">
                <h3>Пока нет опубликованных новостей</h3>
                <p>Будьте первым, кто поделится интересной информацией!</p>
            </div>
        <?php else: ?>
            <?php foreach ($news as $item): ?>
                <div class="news-card">
                    <div class="news-category"><?php echo htmlspecialchars($item['category_title']); ?></div>
                    <h3 class="news-title">
                        <a href="news_detail.php?id=<?php echo $item['id_nwes']; ?>">
                            <?php echo htmlspecialchars($item['title']); ?>
                        </a>
                    </h3>
                    <div class="news-meta">
                        <span class="news-author">👤 <?php echo htmlspecialchars($item['name'] . ' ' . $item['lastname']); ?></span>
                        <span class="news-date">📅 <?php echo $item['date_relise']; ?></span>
                    </div>
                    <div class="news-preview">
                        <?php 
                        $preview = strip_tags($item['text']);
                        if (strlen($preview) > 150) {
                            $preview = substr($preview, 0, 150) . '...';
                        }
                        echo htmlspecialchars($preview);
                        ?>
                    </div>
                    <a href="news_detail.php?id=<?php echo $item['id_nwes']; ?>" class="read-more">Читать полностью →</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

