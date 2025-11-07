<?php
require_once 'config.php';  // Первым - настройки БД и сессии
require_once 'auth.php';    // Вторым - функции авторизации

// НЕ требуем авторизацию для просмотра новостей

// Проверяем ID новости
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$news_id = (int)$_GET['id'];

// Получаем полную информацию о новости
$stmt = $pdo->prepare("
    SELECT n.*, c.title as category_title, u.name, u.lastname, s.title as status_title 
    FROM news n 
    LEFT JOIN categories c ON n.categories_id = c.categories_id 
    LEFT JOIN users u ON n.id_user = u.id_user 
    LEFT JOIN status s ON n.id_status = s.id_status 
    WHERE n.id_nwes = ? AND n.id_status = 2
");
$stmt->execute([$news_id]);
$news_item = $stmt->fetch(PDO::FETCH_ASSOC);

// Если новость не найдена или не опубликована
if (!$news_item) {
    header('Location: index.php');
    exit;
}

$title = htmlspecialchars($news_item['title']) . " - Лагерь Смена";
require_once 'header.php';
?>

<div class="container">
    <div class="news-detail">
        <!-- Хлебные крошки -->
        <div class="breadcrumbs">
            <a href="news.php">← Все новости</a>
        </div>

        <!-- Заголовок новости -->
        <article class="news-article">
            <div class="news-header">
                <div class="news-category-badge">
                    <?php echo htmlspecialchars($news_item['category_title']); ?>
                </div>
                <h1><?php echo htmlspecialchars($news_item['title']); ?></h1>
                <div class="news-meta-detailed">
                    <div class="author-info">
                        <div class="author-avatar">👤</div>
                        <div class="author-details">
                            <strong><?php echo htmlspecialchars($news_item['name'] . ' ' . $news_item['lastname']); ?></strong>
                            <span>Автор</span>
                        </div>
                    </div>
                    <div class="publication-info">
                        <div class="publication-date">
                            <strong>📅 <?php echo $news_item['date_relise']; ?></strong>
                            <span>Дата публикации</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Полный текст новости -->
            <div class="news-content">
                <?php 
                $text = htmlspecialchars($news_item['text']);
                // Форматируем текст - добавляем переносы строк
                $text = nl2br($text);
                echo $text;
                ?>
            </div>

            <!-- Действия -->
            <div class="news-actions-detailed">
                <?php if (canEditNews($news_item['id_user'])): ?>
                    <a href="edit_news.php?id=<?php echo $news_item['id_nwes']; ?>" class="btn btn-small">✏️ Редактировать</a>
                <?php endif; ?>
                <a href="news.php" class="btn btn-secondary">← К списку новостей</a>
            </div>
        </article>

        <!-- Похожие новости -->
        <div class="related-news">
            <h3>Другие новости</h3>
            <?php
            // Получаем 3 последние новости из той же категории (кроме текущей)
            $stmt_related = $pdo->prepare("
                SELECT n.*, c.title as category_title, u.name, u.lastname 
                FROM news n 
                LEFT JOIN categories c ON n.categories_id = c.categories_id 
                LEFT JOIN users u ON n.id_user = u.id_user 
                WHERE n.categories_id = ? AND n.id_nwes != ? AND n.id_status = 2 
                ORDER BY n.date_relise DESC 
                LIMIT 3
            ");
            $stmt_related->execute([$news_item['categories_id'], $news_id]);
            $related_news = $stmt_related->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($related_news)): ?>
                <div class="related-grid">
                    <?php foreach ($related_news as $related): ?>
                        <div class="related-card">
                            <h4>
                                <a href="news_detail.php?id=<?php echo $related['id_nwes']; ?>">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </a>
                            </h4>
                            <div class="related-meta">
                                <span>👤 <?php echo htmlspecialchars($related['name'] . ' ' . $related['lastname']); ?></span>
                                <span>📅 <?php echo $related['date_relise']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Других новостей в этой категории пока нет.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

