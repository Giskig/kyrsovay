<?php
require_once 'config.php';  // Первым - настройки БД и сессии
require_once 'auth.php';    // Вторым - функции авторизации

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Проверяем роль (только админ и учитель)
if (getUserRole() != 1 && getUserRole() != 2) {
    die("У вас нет прав для редактирования новостей");
}

// Проверяем ID новости
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Неверный ID новости");
}

$news_id = (int)$_GET['id'];

// Получаем данные новости
$stmt = $pdo->prepare("
    SELECT n.*, u.name, u.lastname 
    FROM news n 
    LEFT JOIN users u ON n.id_user = u.id_user 
    WHERE n.id_nwes = ?
");
$stmt->execute([$news_id]);
$news_item = $stmt->fetch(PDO::FETCH_ASSOC);

// Проверяем существование новости
if (!$news_item) {
    die("Новость не найдена");
}

// Проверяем права на редактирование
if (!canEditNews($news_item['id_user'])) {
    die("У вас нет прав для редактирования этой новости");
}

// Получаем категории
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем статусы (для администратора)
$statuses = [];
if (getUserRole() == 1) {
    $stmt = $pdo->query("SELECT * FROM status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $text = trim($_POST['text']);
    $category_id = (int)$_POST['category_id'];
    $status_id = (int)($_POST['status_id'] ?? $news_item['id_status']);
    
    // Валидация
    if (empty($title) || empty($text) || empty($category_id)) {
        $error = "Все поля обязательны для заполнения!";
    } elseif (strlen($title) > 250 || strlen($text) > 2000) {
        $error = "Заголовок и текст не должны превышать 1900 символов!";
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE news 
                SET title = ?, text = ?, categories_id = ?, id_status = ?
                WHERE id_nwes = ?
            ");
            
            if ($stmt->execute([$title, $text, $category_id, $status_id, $news_id])) {
                // Записываем изменение в таблицу changing
                $stmt_changing = $pdo->prepare("
                    INSERT INTO changing (id_user, id_news, date_time) 
                    VALUES (?, ?, NOW())
                ");
                $stmt_changing->execute([getUserId(), $news_id]);
                
                $success = "Новость успешно обновлена!";
                
                // Обновляем данные новости
                $news_item['title'] = $title;
                $news_item['text'] = $text;
                $news_item['categories_id'] = $category_id;
                $news_item['id_status'] = $status_id;
            } else {
                $error = "Ошибка при обновлении новости";
            }
            
        } catch(PDOException $e) {
            $error = "Ошибка базы данных: " . $e->getMessage();
            logError("Edit news error: " . $e->getMessage());
        }
    }
}

$title = "Название страницы - Лагерь Смена";
require_once 'header.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактировать новость - Лагерь Смена</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <h2>Редактировать новость</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="news-info">
            <p><strong>Автор:</strong> <?php echo htmlspecialchars($news_item['name'] . ' ' . $news_item['lastname']); ?></p>
            <p><strong>Дата создания:</strong> <?php echo $news_item['date_relise']; ?></p>
        </div>

        <form method="POST" class="news-form">
            <div class="form-group">
                <label for="title">Заголовок:</label>
                <input type="text" id="title" name="title" required maxlength="250" 
                       value="<?php echo htmlspecialchars($news_item['title']); ?>">
            </div>
            
            <div class="form-group">
                <label for="category_id">Категория:</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Выберите категорию</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['categories_id']; ?>" 
                            <?php echo ($news_item['categories_id'] == $category['categories_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <?php if (getUserRole() == 1 && !empty($statuses)): ?>
            <div class="form-group">
                <label for="status_id">Статус:</label>
                <select id="status_id" name="status_id">
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?php echo $status['id_status']; ?>" 
                            <?php echo ($news_item['id_status'] == $status['id_status']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($status['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="text">Текст новости:</label>
                <textarea id="text" name="text" required rows="10" placeholder="Введите полный текст новости..."><?php echo htmlspecialchars($news_item['text']); ?></textarea>
                <div class="char-count">Осталось символов: <span id="char-remaining"></span></div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                <a href="manage_news.php" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>

    <script>
        const textarea = document.getElementById('text');
        const charRemaining = document.getElementById('char-remaining');
        
        function updateCharCount() {
            const remaining = 2000 - textarea.value.length;
            charRemaining.textContent = remaining;
            charRemaining.style.color = remaining < 0 ? 'red' : 'inherit';
        }
        
        textarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Инициализация
    </script>
</body>
</html>