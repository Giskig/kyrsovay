<?php
require_once 'auth.php';
require_once 'config.php';

if (!isLoggedIn() || !canSuggestNews()) {
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
    } elseif (strlen($title) > 250 || strlen($text) > 250) {
        $error = "Заголовок и текст не должны превышать 250 символов!";
    } else {
        // Ученики отправляют новости на модерацию (статус 1)
        $status_id = 1; // на модерации
        $role_id = getUserRole();
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO news (id_user, categories_id, id_status, role_id, title, text, date_relise) 
                VALUES (?, ?, ?, ?, ?, ?, CURDATE())
            ");
            
            if ($stmt->execute([$user_id, $category_id, $status_id, $role_id, $title, $text])) {
                $success = "Новость успешно отправлена на модерацию! Ожидайте проверки администратором.";
                $_POST = array();
            } else {
                $error = "Ошибка при отправке новости";
            }
            
        } catch(PDOException $e) {
            $error = "Ошибка базы данных: " . $e->getMessage();
            logError("Suggest news error: " . $e->getMessage());
        }
    }
}

$title = "Предложить новость - Лагерь Смена";
require_once 'header.php';
?>



<div class="container">
    <h2>Предложить новость</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="student-info">
        <div class="info-card">
            <h3>📝 Как это работает?</h3>
            <ul>
                <li>Вы предлагаете новость, которая будет отправлена на модерацию</li>
                <li>Администратор проверит вашу новость и опубликует её</li>
                <li>Вы сможете увидеть статус своей новости в профиле</li>
                <li>После публикации новость увидят все пользователи</li>
            </ul>
        </div>
    </div>

    <form method="POST" class="news-form">
        <div class="form-group">
            <label for="title">Заголовок новости:</label>
            <input type="text" id="title" name="title" required maxlength="250" 
                   value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                   placeholder="Введите интересный заголовок">
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
            <textarea id="text" name="text" required maxlength="250" rows="5" 
                      placeholder="Опишите вашу новость подробно..."><?php echo htmlspecialchars($_POST['text'] ?? ''); ?></textarea>
            <div class="char-count">Осталось символов: <span id="char-remaining">250</span></div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                📨 Отправить на модерацию
            </button>
            <a href="profile.php" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>

<script>
    const textarea = document.getElementById('text');
    const charRemaining = document.getElementById('char-remaining');
    
    function updateCharCount() {
        const remaining = 250 - textarea.value.length;
        charRemaining.textContent = remaining;
        charRemaining.style.color = remaining < 0 ? 'red' : 'inherit';
    }
    
    textarea.addEventListener('input', updateCharCount);
    updateCharCount();
</script>

