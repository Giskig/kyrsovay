<?php
// header.php
if (!isset($title)) {
    $title = 'Лагерь Смена';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="nav">
            <h1><a href="index.php" style="color: white; text-decoration: none;">Лагерь Смена</a></h1>
            <div class="nav-links">
                <?php if (isLoggedIn()): ?>
                    <span>Добро пожаловать, <?php echo $_SESSION['user_name']; ?>!</span>
                    <a href="index.php">Главная</a>
                    <a href="profile.php">Профиль</a>
                    <a href="index.php?logout=true">Выйти</a>
                <?php else: ?>
                    <a href="index.php">Главная</a>
                    <a href="login.php">Войти</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Баннер необходимости авторизации -->
    <?php if (isset($_GET['auth_required']) && !isLoggedIn()): ?>
        <div class="auth-banner">
            <div class="container">
                <p>⚠️ Для доступа к этой странице необходимо <a href="login.php">войти в систему</a></p>
            </div>
        </div>
    <?php endif; ?>