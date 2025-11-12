<?php
require_once 'config.php';  // Первым - настройки БД и сессии
require_once 'auth.php';    // Вторым - функции авторизации

// Проверяем авторизацию и права (только администратор)
if (!isLoggedIn() || getUserRole() != 1) {
    header('Location: index.php');
    exit;
}

// Получаем новости на модерации
$stmt = $pdo->prepare("
    SELECT n.*, c.title as category_title, u.name, u.lastname, s.title as status_title 
    FROM news n 
    LEFT JOIN categories c ON n.categories_id = c.categories_id 
    LEFT JOIN users u ON n.id_user = u.id_user 
    LEFT JOIN status s ON n.id_status = s.id_status 
    WHERE n.id_status = 1 
    ORDER BY n.date_relise DESC
");
$stmt->execute();
$moderation_news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка действий модерации
if (isset($_POST['action']) && isset($_POST['news_id']) && is_numeric($_POST['news_id'])) {
    $news_id = (int)$_POST['news_id'];
    $action = $_POST['action'];
    $reason = trim($_POST['reason'] ?? '');
    
    $new_status = 0;
    $success_message = '';
    
    switch ($action) {
        case 'publish':
            $new_status = 2; // опубликованно
            $success_message = "Новость опубликована";
            break;
        case 'reject':
            $new_status = 4; // отклонено
            $success_message = "Новость отклонена";
            break;
    }
    
    if ($new_status > 0) {
        // Обновляем статус новости
        $stmt = $pdo->prepare("UPDATE news SET id_status = ? WHERE id_nwes = ?");
        $stmt->execute([$new_status, $news_id]);
        
        // Записываем в историю изменений
        $stmt_changing = $pdo->prepare("INSERT INTO changing (id_user, id_news, date_time) VALUES (?, ?, NOW())");
        $stmt_changing->execute([getUserId(), $news_id]);
        
        // Если новость отклонена, записываем в архив с причиной
        if ($action == 'reject' && !empty($reason)) {
            // Сначала проверяем, есть ли уже запись в архиве
            $stmt_check = $pdo->prepare("SELECT archive_id FROM archive WHERE id_news = ?");
            $stmt_check->execute([$news_id]);
            $existing_archive = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_archive) {
                // Обновляем существующую запись
                $stmt_archive = $pdo->prepare("UPDATE archive SET reason = ?, date = CURDATE() WHERE id_news = ?");
                $stmt_archive->execute([$reason, $news_id]);
            } else {
                // Создаем новую запись
                $stmt_archive = $pdo->prepare("INSERT INTO archive (id_news, reason, date) VALUES (?, ?, CURDATE())");
                $stmt_archive->execute([$news_id, $reason]);
            }
        }
        
        $success = $success_message;
        
        // Обновляем список новостей
        header('Location: moderation.php?success=' . urlencode($success));
        exit;
    }
}

$success = $_GET['success'] ?? '';

$title = "Модерация новостей - Лагерь Смена";
require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Модерация новостей - Лагерь Смена</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h2>Панель модерации</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="moderation-info">
            <div class="stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo count($moderation_news); ?></span>
                    <span class="stat-label">новостей на модерации</span>
                </div>
            </div>
            <div class="quick-actions">
                <a href="manage_news.php?status=1" class="btn btn-primary">Все новости на модерации</a>
                <a href="manage_news.php" class="btn btn-secondary">Все новости</a>
            </div>
        </div>

        <div class="moderation-list">
            <?php if (empty($moderation_news)): ?>
                <div class="empty-state">
                    <h3>Нет новостей для модерации</h3>
                    <p>Все новости проверены и обработаны.</p>
                </div>
            <?php else: ?>
                <?php foreach ($moderation_news as $item): ?>
                    <div class="moderation-item">
                        <div class="moderation-content">
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <div class="news-meta">
                                <span><strong>Автор:</strong> <?php echo htmlspecialchars($item['name'] . ' ' . $item['lastname']); ?></span>
                                <span><strong>Категория:</strong> <?php echo htmlspecialchars($item['category_title']); ?></span>
                                <span><strong>Дата:</strong> <?php echo $item['date_relise']; ?></span>
                            </div>
                            <div class="news-text">
                                <p><?php echo htmlspecialchars($item['text']); ?></p>
                            </div>
                        </div>
                        
                        <div class="moderation-actions">
                            <form method="POST" class="action-form">
                                <input type="hidden" name="news_id" value="<?php echo $item['id_nwes']; ?>">
                                
                                <button type="submit" name="action" value="publish" class="btn btn-success btn-block">
                                    ✅ Опубликовать
                                </button>
                                
                                <div class="reject-section">
                                    <textarea name="reason" placeholder="Причина отклонения (необязательно)" maxlength="250" rows="3"></textarea>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-block">
                                        ❌ Отклонить
                                    </button>
                                </div>
                                
                                <a href="edit_news.php?id=<?php echo $item['id_nwes']; ?>" class="btn btn-secondary btn-block">
                                    ✏️ Редактировать
                                </a>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>