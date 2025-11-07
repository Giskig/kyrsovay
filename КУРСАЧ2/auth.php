<?php
require_once 'config.php';

function loginUser($login, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_lastname'] = $user['lastname'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['login'] = $user['login'];
        
        // Записываем вход в историю
        $stmt = $pdo->prepare("INSERT INTO login_history (id_user, entry_date) VALUES (?, CURDATE())");
        $stmt->execute([$user['id_user']]);
        
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role_id'] ?? null;
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function canEditNews($news_author_id = null) {
    $user_role = getUserRole();
    $user_id = getUserId();
    
    // Администратор может редактировать все новости
    if ($user_role == 1) {
        return true;
    }
    
    // Учитель может редактировать только свои новости
    if ($user_role == 2 && $news_author_id == $user_id) {
        return true;
    }
    
    return false;
}

function canPublishNews() {
    $user_role = getUserRole();
    
    // Администратор и учитель могут публиковать новости
    return $user_role == 1 || $user_role == 2;
}

function canSuggestNews() {
    $user_role = getUserRole();
    
    // Ученик может только предлагать новости
    return $user_role == 3;
}

function canManageNews() {
    $user_role = getUserRole();
    
    // Администратор и учитель могут управлять новостями
    return $user_role == 1 || $user_role == 2;
}

function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>