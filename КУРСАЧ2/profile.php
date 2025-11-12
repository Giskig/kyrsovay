<?php
require_once 'config.php';
require_once 'auth.php';

// Требуем авторизацию для доступа к профилю
requireAuth();

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

// Получаем историю входов
$stmt = $pdo->prepare("SELECT * FROM login_history WHERE id_user = ? ORDER BY entry_date DESC LIMIT 5");
$stmt->execute([$user_id]);
$login_history = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем новости пользователя
$user_news = [];
$stmt = $pdo->prepare("
    SELECT n.*, c.title as category_title, s.title as status_title 
    FROM news n 
    LEFT JOIN categories c ON n.categories_id = c.categories_id 
    LEFT JOIN status s ON n.id_status = s.id_status 
    WHERE n.id_user = ? 
    ORDER BY n.date_relise DESC
");
$stmt->execute([$user_id]);
$user_news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Статистика для администратора
$stats = [];
if ($role_id == 1) {
    // Общее количество новостей
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM news");
    $stats['total_news'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Новости на модерации
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM news WHERE id_status = 1");
    $stats['moderation_news'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Опубликованные новости
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM news WHERE id_status = 2");
    $stats['published_news'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Всего пользователей
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

$title = "Профиль - Лагерь Смена";
require_once 'header.php';
?>
<div class="container">
    <div class="profile-header">
        <h2 style="color: black;">Профиль пользователя</h2>
        <div>
            <p><strong>Имя:</strong> <?php echo $_SESSION['user_name'] . ' ' . $_SESSION['user_lastname']; ?></p>
            <p><strong>Логин:</strong> <?php echo $_SESSION['login']; ?></p>
            <p><strong>Роль:</strong> 
                <?php 
                switch($role_id) {
                    case 1: echo 'Администратор'; break;
                    case 2: echo 'Преподаватель'; break;
                    case 3: echo 'Ученик'; break;
                }
                ?>
            </p>
        </div>
    </div>

<?php if ($role_id == 1): ?>
    <div class="profile-section">
        <h3>⚙️ Административные функции</h3>
                <a href="moderation.php" class="btn btn-warning">⚡ Панель модерации</a>
                <a href="manage_news.php" class="btn btn-primary">📝 Все новости</a>
                <a href="add_news.php" class="btn btn-success">➕ Добавить новость</a>
                <a href="edit_user.php" class="btn btn-success">✏️ редактировать пользователей</a>
    </div>
<?php endif; ?>

        <?php if ($role_id == 2): ?>
        <div class="profile-section">
            <h3>Административные функции</h3>
            <div class="admin-actions-profile">
                <a href="add_news.php" class="btn btn-success">➕ Добавить новость</a>
            </div>
        </div>
    <?php endif; ?>


    <?php if ($role_id == 3): ?>
        <div class="profile-section">
            <h3>Функции ученика</h3>
            <a href="suggest_news.php" class="btn btn-primary">📨 Предложить новость</a>
            <p>
                Как ученик, вы можете предлагать новости, которые будут отправлены на модерацию администратору.
                После проверки ваша новость может быть опубликована на главной странице.
            </p>
        </div>
    <?php endif; ?>

    <!-- Блок с новостями пользователя - для ВСЕХ ролей -->
    <div class="profile-section">
        <h3>Мои новости</h3>
        <?php if (empty($user_news)): ?>
            <div class="empty-state">
                <p>У вас пока нет новостей.</p>
                <?php if ($role_id == 3): ?>
                    <a href="suggest_news.php" class="btn btn-primary">Предложить первую новость</a>
                <?php elseif ($role_id == 2): ?>
                    <a href="add_news.php" class="btn btn-primary">Добавить первую новость</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div>
                <?php foreach ($user_news as $news): ?>
                    <div class="user-news-item">
                        <div class="news-header">
                            <h4><?php echo htmlspecialchars($news['title']); ?></h4>
                            <span class="status-badge status-<?php echo $news['id_status']; ?>">
                                <?php 
                                // Русские названия статусов для лучшего отображения
                                $status_text = htmlspecialchars($news['status_title']);
                                $status_text = str_replace('moderation', 'на модерации', $status_text);
                                $status_text = str_replace('published', 'опубликовано', $status_text);
                                $status_text = str_replace('archive', 'в архиве', $status_text);
                                $status_text = str_replace('rejected', 'отклонено', $status_text);
                                echo $status_text;
                                ?>
                            </span>
                        </div>

                        <div>
                            <p><strong>Категория:</strong> <?php echo htmlspecialchars($news['category_title']); ?></p>
                            <p><strong>Дата создания:</strong> <?php echo $news['date_relise']; ?></p>
                            <p><strong>Текст:</strong> <?php echo htmlspecialchars($news['text']); ?></p>
                        </div>
                        
                        <div>
                            <?php if (canEditNews($news['id_user'])): ?>
                                <a href="edit_news.php?id=<?php echo $news['id_nwes']; ?>" class="btn btn-small">Редактировать</a>
                            <?php endif; ?>
                            
                            <?php if ($role_id == 3): ?>
                                <span>
                                    <?php if ($news['id_status'] == 1): ?>
                                        ⏳ Ожидает проверки администратором
                                    <?php elseif ($news['id_status'] == 2): ?>
                                        ✅ Ваша новость опубликована!
                                    <?php elseif ($news['id_status'] == 4): ?>
                                        ❌ Новость отклонена администратором
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div >
                <p><strong>Всего новостей:</strong> <?php echo count($user_news); ?></p>
                <?php 
                $published_count = 0;
                $moderation_count = 0;
                $rejected_count = 0;
                
                foreach ($user_news as $news) {
                    if ($news['id_status'] == 2) $published_count++;
                    if ($news['id_status'] == 1) $moderation_count++;
                    if ($news['id_status'] == 4) $rejected_count++;
                }
                ?>
                <p><strong>Опубликовано:</strong> <?php echo $published_count; ?></p>
                <p><strong>На модерации:</strong> <?php echo $moderation_count; ?></p>
                <?php if ($rejected_count > 0): ?>
                    <p><strong>Отклонено:</strong> <?php echo $rejected_count; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h3>История входов</h3>
        <?php if (!empty($login_history)): ?>
            <div>
                <?php foreach ($login_history as $entry): ?>
                    <div>
                        📅 Дата входа: <?php echo $entry['entry_date']; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>История входов отсутствует.</p>
        <?php endif; ?>
    </div>
</div>

