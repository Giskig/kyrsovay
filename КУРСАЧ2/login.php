<?php
require_once 'config.php';
require_once 'auth.php';

// Если пользователь уже авторизован, перенаправляем на главную
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (loginUser($_POST['login'], $_POST['password'])) {
        // Успешный вход - перенаправляем на главную
        header('Location: index.php');
        exit;
    } else {
        $error = "Неверный логин или пароль!";
    }
}

$title = "Вход в систему - Лагерь Смена";
require_once 'header.php';
?>

<div class="container">
    <div>
        <div>
            <h2>Вход в систему</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" class="login-form">
                <div class="form-group">
                    <label for="login">Логин:</label>
                    <input type="text" id="login" name="login" required 
                           placeholder="Введите ваш логин" value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Пароль:</label>
                    <input type="password" id="password" name="password" required 
                           placeholder="Введите ваш пароль">
                </div>
                
                <button type="submit">Войти в систему</button>
            </form>

            <div>
                <a href="index.php">← Вернуться на главную</a>
            </div>

            <div>
                <h4>Тестовые аккаунты:</h4>
                <div>
                    <div>
                        <strong>Администратор</strong>
                        <span>: admin</span>
                        <span>Пароль: admin123</span>
                    </div>
                    <div>
                        <strong>Преподаватель</strong>
                        <span>: teacher</span>
                        <span>Пароль: teacher123</span>
                    </div>
                    <div>
                        <strong>Ученик</strong>
                        <span>: student</span>
                        <span>Пароль: student123</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
