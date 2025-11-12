<?php
require_once 'config.php';
require_once 'auth.php';

// Только администратор может редактировать пользователей
if (!isLoggedIn() || getUserRole() != 1) {
    header('Location: index.php');
    exit;
}

// Проверяем ID пользователя
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: manage_users.php');
    exit;
}

$user_id = (int)$_GET['id'];

// Получаем данные пользователя
$stmt = $pdo->prepare("
    SELECT u.*, r.title as role_title 
    FROM users u 
    LEFT JOIN role r ON u.role_id = r.role_id 
    WHERE u.id_user = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Если пользователь не найден
if (!$user) {
    header('Location: manage_users.php');
    exit;
}

// Получаем роли для формы
$stmt_roles = $pdo->query("SELECT * FROM role ORDER BY role_id");
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

$success = '';
$error = '';

// Обработка формы редактирования
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user'])) {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    $role_id = (int)$_POST['role_id'];
    
    // Валидация
    if (empty($name) || empty($lastname) || empty($login)) {
        $error = "Имя, фамилия и логин обязательны для заполнения!";
    } else {
        try {
            // Проверяем, не существует ли уже пользователь с таким логином (кроме текущего)
            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE login = ? AND id_user != ?");
            $stmt->execute([$login, $user_id]);
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_user) {
                $error = "Пользователь с логином '$login' уже существует!";
            } else {
                // Если пароль не пустой - обновляем его
                if (!empty($password)) {
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET name = ?, lastname = ?, login = ?, password = ?, role_id = ? 
                        WHERE id_user = ?
                    ");
                    $stmt->execute([$name, $lastname, $login, $password, $role_id, $user_id]);
                } else {
                    // Если пароль пустой - не меняем его
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET name = ?, lastname = ?, login = ?, role_id = ? 
                        WHERE id_user = ?
                    ");
                    $stmt->execute([$name, $lastname, $login, $role_id, $user_id]);
                }
                
                $success = "Данные пользователя успешно обновлены!";
                
                // Обновляем данные пользователя
                $user['name'] = $name;
                $user['lastname'] = $lastname;
                $user['login'] = $login;
                $user['role_id'] = $role_id;
            }
            
        } catch(PDOException $e) {
            $error = "Ошибка базы данных: " . $e->getMessage();
            logError("Edit user error: " . $e->getMessage());
        }
    }
}

$title = "Редактирование пользователя - Лагерь Смена";
require_once 'header.php';
?>
<div class="container">
    <div class="breadcrumbs">
        <a href="manage_users.php">← Назад к управлению пользователями</a>
    </div>

    <h2>✏️ Редактирование пользователя</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div>
        <div>
            <h3><?php echo htmlspecialchars($user['name'] . ' ' . $user['lastname']); ?></h3>
            <span class="role-badge role-<?php echo $user['role_id']; ?>">
                <?php echo htmlspecialchars($user['role_title']); ?>
            </span>
        </div>
    </div>

    <form method="POST" class="user-form">
        <div class="form-row">
            <div class="form-group">
                <label for="name">Имя:</label>
                <input type="text" id="name" name="name" required 
                       value="<?php echo htmlspecialchars($user['name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="lastname">Фамилия:</label>
                <input type="text" id="lastname" name="lastname" required 
                       value="<?php echo htmlspecialchars($user['lastname']); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="login">Логин:</label>
                <input type="text" id="login" name="login" required 
                       value="<?php echo htmlspecialchars($user['login']); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Новый пароль:</label>
                <input type="password" id="password" name="password" 
                       placeholder="Оставьте пустым, если не нужно менять">
            </div>
        </div>
        
        <div class="form-group">
            <label for="role_id">Роль:</label>
            <select id="role_id" name="role_id" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?php echo $role['role_id']; ?>" 
                        <?php echo ($user['role_id'] == $role['role_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($role['title']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-actions">
            <button type="submit" name="edit_user" class="btn btn-primary">Сохранить изменения</button>
            <a href="manage_users.php" class="btn btn-secondary">Отмена</a>
        </div>
    </form>
</div>

