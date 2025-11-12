<?php
require_once 'config.php';  // Первым - настройки БД и сессии
require_once 'auth.php';    // Вторым - функции авторизации

if (!isLoggedIn() || !canManageNews()) {
    header('Location: index.php');
    exit;
}

// Получаем новости в зависимости от роли
if (getUserRole() == 1) {
    // Администратор видит все новости
    $stmt = $pdo->prepare("
        SELECT n.*, c.title as category_title, u.name, u.lastname, s.title as status_title 
        FROM news n 
        LEFT JOIN categories c ON n.categories_id = c.categories_id 
        LEFT JOIN users u ON n.id_user = u.id_user 
        LEFT JOIN status s ON n.id_status = s.id_status 
        ORDER BY n.date_relise DESC
    ");
    $stmt->execute();
} else {
    // Учитель видит только свои новости
    $stmt = $pdo->prepare("
        SELECT n.*, c.title as category_title, u.name, u.lastname, s.title as status_title 
        FROM news n 
        LEFT JOIN categories c ON n.categories_id = c.categories_id 
        LEFT JOIN users u ON n.id_user = u.id_user 
        LEFT JOIN status s ON n.id_status = s.id_status 
        WHERE n.id_user = ?
        ORDER BY n.date_relise DESC
    ");
    $stmt->execute([getUserId()]);
}

$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Обработка действий с новостями
if (isset($_GET['action']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $news_id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Проверяем права на действие
    $stmt = $pdo->prepare("SELECT id_user, id_status FROM news WHERE id_nwes = ?");
    $stmt->execute([$news_id]);
    $news_item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($news_item && (getUserRole() == 1 || $news_item['id_user'] == getUserId())) {
        switch ($action) {
            case 'publish':
                $stmt = $pdo->prepare("UPDATE news SET id_status = 2 WHERE id_nwes = ?");
                $stmt->execute([$news_id]);
                $success = "Новость опубликована";
                break;
                
            case 'archive':
                $stmt = $pdo->prepare("UPDATE news SET id_status = 3 WHERE id_nwes = ?");
                $stmt->execute([$news_id]);
                $success = "Новость перемещена в архив";
                break;
                
            case 'reject':
                $stmt = $pdo->prepare("UPDATE news SET id_status = 4 WHERE id_nwes = ?");
                $stmt->execute([$news_id]);
                $success = "Новость отклонена";
                break;
                
            case 'to_moderation':
                $stmt = $pdo->prepare("UPDATE news SET id_status = 1 WHERE id_nwes = ?");
                $stmt->execute([$news_id]);
                $success = "Новость отправлена на модерацию";
                break;
        }
        
        // Записываем изменение в историю
        if (isset($success)) {
            $stmt_changing = $pdo->prepare("INSERT INTO changing (id_user, id_news, date_time) VALUES (?, ?, NOW())");
            $stmt_changing->execute([getUserId(), $news_id]);
        }
        
        // Перенаправляем чтобы избежать повторной отправки формы
        header('Location: manage_news.php?success=' . urlencode($success));
        exit;
    } else {
        $error = "У вас нет прав для выполнения этого действия";
    }
}

// Получаем сообщения об успехе/ошибке
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$title = "Управление новостями - Лагерь Смена";
require_once 'header.php';
?>
<div class="container">
    <h2>Управление новостями</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="management-header">
        <div>
            <a href="add_news.php" class="btn btn-primary">Добавить новость</a>
            <?php if (getUserRole() == 1): ?>
                <a href="moderation.php" class="btn btn-warning">Панель модерации</a>
            <?php endif; ?>
        </div>
        
        <div>
            <?php if (getUserRole() == 1): ?>
                <span class="role-badge admin">👑 Администратор</span>
                <small>Вы можете управлять всеми новостями</small>
            <?php else: ?>
                <span class="role-badge teacher">👨‍🏫 Преподаватель</span>
                <small>Вы управляете только своими новостями</small>
            <?php endif; ?>
        </div>
    </div>

    <div class="news-list">
        <?php if (empty($news)): ?>
            <p>Новостей не найдено.</p>
        <?php else: ?>
            <table class="news-table">
                <thead>
                    <tr>
                        <th>Заголовок</th>
                        <th>Категория</th>
                        <th>Статус</th>
                        <th>Автор</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $item): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                                <div class="news-preview"><?php echo htmlspecialchars(substr($item['text'], 0, 50)); ?>...</div>
                            </td>
                            <td><?php echo htmlspecialchars($item['category_title']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $item['id_status']; ?>">
                                    <?php echo htmlspecialchars($item['status_title']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($item['name'] . ' ' . $item['lastname']); ?></td>
                            <td><?php echo $item['date_relise']; ?></td>
                            <td class="actions">
                                <?php if (canEditNews($item['id_user'])): ?>
                                    <a href="edit_news.php?id=<?php echo $item['id_nwes']; ?>" class="btn btn-small">Редактировать</a>
                                <?php endif; ?>
                                
                                <?php if (getUserRole() == 1 || $item['id_user'] == getUserId()): ?>
                                    <div class="status-actions">
                                        <?php if ($item['id_status'] == 1): // На модерации ?>
                                            <?php if (getUserRole() == 1): ?>
                                                <a href="manage_news.php?action=publish&id=<?php echo $item['id_nwes']; ?>" 
                                                   class="btn btn-small btn-success"
                                                   onclick="return confirm('Опубликовать эту новость?')">
                                                    Опубликовать
                                                </a>
                                                <a href="manage_news.php?action=reject&id=<?php echo $item['id_nwes']; ?>" 
                                                   class="btn btn-small btn-danger"
                                                   onclick="return confirm('Отклонить эту новость?')">
                                                    Отклонить
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($item['id_status'] == 2): // Опубликовано ?>
                                            <a href="manage_news.php?action=archive&id=<?php echo $item['id_nwes']; ?>" 
                                               class="btn btn-small btn-warning"
                                               onclick="return confirm('Переместить в архив?')">
                                                В архив
                                            </a>
                                        <?php elseif ($item['id_status'] == 3): // В архиве ?>
                                            <a href="manage_news.php?action=publish&id=<?php echo $item['id_nwes']; ?>" 
                                               class="btn btn-small btn-success"
                                               onclick="return confirm('Вернуть из архива?')">
                                                Вернуть
                                            </a>
                                        <?php elseif ($item['id_status'] == 4): // Отклонено ?>
                                            <?php if (getUserRole() == 1): ?>
                                                <a href="manage_news.php?action=publish&id=<?php echo $item['id_nwes']; ?>" 
                                                   class="btn btn-small btn-success"
                                                   onclick="return confirm('Опубликовать эту новость?')">
                                                    Опубликовать
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($item['id_user'] == getUserId()): ?>
                                                <a href="manage_news.php?action=to_moderation&id=<?php echo $item['id_nwes']; ?>" 
                                                   class="btn btn-small btn-secondary"
                                                   onclick="return confirm('Отправить на повторную модерацию?')">
                                                    На модерацию
                                                </a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

