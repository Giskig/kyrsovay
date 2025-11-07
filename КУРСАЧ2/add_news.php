<?php
require_once 'config.php';  // Первым - настройки БД и сессии
require_once 'auth.php';    // Вторым - функции авторизации

if (!isLoggedIn() || !canPublishNews()) {
    header('Location: index.php');
    exit;
}

// Получаем категории
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title']);
    $text = trim($_POST['text']);
    $category_id = (int)$_POST['category_id'];
    $user_id = getUserId();
    
    // Валидация
    if (empty($title) || empty($text) || empty($category_id)) {
        $error = "Все поля обязательны для заполнения!";
    } elseif (strlen($title) > 250) {
        $error = "Заголовок не должен превышать 250 символов!";
    } elseif (strlen($text) > 2000) {
        $error = "Текст новости не должен превышать 2000 символов!";
    } else {
        // Учителя публикуют сразу (статус 2), администраторы тоже
        $status_id = 2; // опубликованно
        $role_id = getUserRole();
        
        try {
            // Простая вставка - внешние ключи отключены в config.php
            $stmt = $pdo->prepare("
                INSERT INTO news (id_user, categories_id, id_status, role_id, title, text, date_relise) 
                VALUES (?, ?, ?, ?, ?, ?, CURDATE())
            ");
            
            if ($stmt->execute([$user_id, $category_id, $status_id, $role_id, $title, $text])) {
                $news_id = $pdo->lastInsertId();
                $success = "Новость успешно опубликована!";
                
                // Записываем изменение в историю (если нужно)
                try {
                    $stmt_changing = $pdo->prepare("INSERT INTO changing (id_user, id_news, date_time) VALUES (?, ?, NOW())");
                    $stmt_changing->execute([$user_id, $news_id]);
                } catch (Exception $e) {
                    // Игнорируем ошибки с таблицей changing
                }
                
                $_POST = array(); // Очищаем поля формы
            } else {
                $error = "Ошибка при публикации новости";
            }
            
        } catch(PDOException $e) {
            $error = "Ошибка базы данных: " . $e->getMessage();
            logError("Add news error: " . $e->getMessage());
        }
    }
}

$title = "Добавить новость - Лагерь Смена";
require_once 'header.php';
?>

<div class="container">
    <h2>Добавить новость</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="news-form" id="newsForm">
        <div class="form-group">
            <label for="title">Заголовок:</label>
            <input type="text" id="title" name="title" required maxlength="250" 
                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                   placeholder="Введите заголовок новости (максимум 250 символов)">
            <div class="char-count">
                Осталось символов: <span id="title-remaining">250</span>
            </div>
        </div>
        
        <div class="form-group">
            <label for="category_id">Категория:</label>
            <select id="category_id" name="category_id" required>
                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['categories_id']; ?>" 
                        <?php echo (($_POST['category_id'] ?? '') == $category['categories_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="text">Текст новости:</label>
            <textarea id="text" name="text" required rows="10" 
                      placeholder="Введите полный текст новости (максимум 2000 символов)..."
                      maxlength="2000"><?php echo htmlspecialchars($_POST['text'] ?? ''); ?></textarea>
            <div class="char-count">
                Осталось символов: <span id="text-remaining">2000</span>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <?php echo (getUserRole() == 1 ? 'Опубликовать' : 'Опубликовать новость'); ?>
            </button>
            <a href="index.php" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>

<script>
    const titleInput = document.getElementById('title');
    const textInput = document.getElementById('text');
    const titleRemaining = document.getElementById('title-remaining');
    const textRemaining = document.getElementById('text-remaining');
    const submitBtn = document.getElementById('submitBtn');
    const newsForm = document.getElementById('newsForm');

    // Функция для обновления счетчика символов
    function updateCharCount(input, counter, maxLength) {
        const remaining = maxLength - input.value.length;
        counter.textContent = remaining;
        
        if (remaining < 0) {
            counter.style.color = 'red';
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.6';
            submitBtn.style.cursor = 'not-allowed';
        } else if (remaining < 50) {
            counter.style.color = 'orange';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        } else {
            counter.style.color = 'green';
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
    }

    // Обработчики для заголовка
    titleInput.addEventListener('input', function() {
        updateCharCount(titleInput, titleRemaining, 250);
    });

    // Обработчики для текста
    textInput.addEventListener('input', function() {
        updateCharCount(textInput, textRemaining, 2000);
    });

    // Валидация при отправке формы
    newsForm.addEventListener('submit', function(e) {
        const titleLength = titleInput.value.length;
        const textLength = textInput.value.length;
        
        if (titleLength > 250) {
            e.preventDefault();
            alert('Заголовок не должен превышать 250 символов!');
            titleInput.focus();
            return false;
        }
        
        if (textLength > 2000) {
            e.preventDefault();
            alert('Текст новости не должен превышать 2000 символов!');
            textInput.focus();
            return false;
        }
        
        if (textLength === 0) {
            e.preventDefault();
            alert('Текст новости не может быть пустым!');
            textInput.focus();
            return false;
        }
    });

    // Инициализация при загрузке
    updateCharCount(titleInput, titleRemaining, 250);
    updateCharCount(textInput, textRemaining, 2000);
</script>

