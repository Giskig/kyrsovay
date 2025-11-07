<?php
require_once 'config.php';
require_once 'auth.php';

if (isset($_GET['logout'])) {
    logout();
}

// Получаем только ОПУБЛИКОВАННЫЕ новости (статус 2) - для всех пользователей
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

$title = "Лагерь Смена";
require_once 'header.php';
?>

<div class="container">
    <!-- Публичный приветственный блок -->
    <div class="public-welcome">
        <h1>Добро пожаловать в лагерь "Смена"! 🏕️</h1>
        <p class="welcome-subtitle">Место, где происходят удивительные события, творческие открытия и спортивные достижения</p>
        
    </div>

    <!-- Блок с новостями (доступен всем) -->
    <div class="news-section">
        <div class="section-header">
            <h2>📰 Последние новости лагеря</h2>
        </div>

        <?php if (empty($news)): ?>
            <div class="empty-news">
                <h3>Пока нет опубликованных новостей</h3>
                <p>Будьте первым, кто поделится интересной информацией!</p>
                <?php if (!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-primary">Войти и предложить новость</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="news-grid-main">
                <?php foreach ($news as $item): ?>
                    <div class="news-card-main">
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
                        <div class="news-content-preview">
                            <?php 
                            $preview = strip_tags($item['text']);
                            if (strlen($preview) > 200) {
                                $preview = substr($preview, 0, 200) . '...';
                            }
                            echo htmlspecialchars($preview);
                            ?>
                        </div>
                        <a href="news_detail.php?id=<?php echo $item['id_nwes']; ?>" class="read-more">Читать полностью →</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Информационные карточки (для всех) -->
    <div class="info-cards">
        <div class="info-card">
            <h3>🎓 Образование</h3>
            <p>Новейшие методики обучения и развития в нашем лагере</p>
        </div>
        <div class="info-card">
            <h3>🎪 Мероприятия</h3>
            <p>Ближайшие события, конкурсы и развлекательные программы</p>
        </div>
        <div class="info-card">
            <h3>⚽ Спорт</h3>
            <p>Спортивные достижения, тренировки и соревнования</p>
        </div>
        <div class="info-card">
            <h3>🎨 Творчество</h3>
            <p>Творческие проекты, выставки и мастер-классы</p>
        </div>
    </div>
</div>

