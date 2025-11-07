<?php
require_once 'config.php';
require_once 'auth.php';

// Только администратор может управлять пользователями
if (!isLoggedIn() || getUserRole() != 1) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

// Обработка добавления пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $login = trim($_POST['login']);
    $password = trim($_POST['password']);
    $role_id = (int)$_POST['role_id'];
    
    // Валидация
    if (empty($name) || empty($lastname) || empty($login) || empty($password)) {
        $error = "Все поля обязательны для заполнения!";
    } else {
        try {
            // Проверяем, не существует ли уже пользователь с таким логином
            $stmt = $pdo->prepare("SELECT id_user FROM users WHERE login = ?");
            $stmt->execute([$login]);
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_user) {
                $error = "Пользователь с логином '$login' уже существует!";
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, lastname, login, password, role_id) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$name, $lastname, $login, $password, $role_id])) {
                    $success = "Пользователь успешно добавлен!";
                } else {
                    $error = "Ошибка при добавлении пользователя";
                }
            }
            
        } catch(PDOException $e) {
            $error = "Ошибка базы данных: " . $e->getMessage();
            logError("Add user error: " . $e->getMessage());
        }
    }
}

// Обработка удаления пользователя
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    // Нельзя удалить самого себя
    if ($user_id == getUserId()) {
        $error = "Вы не можете удалить свой собственный аккаунт!";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
            if ($stmt->execute([$user_id])) {
                $success = "Пользователь успешно удален!";
            } else {
                $error = "Ошибка при удалении пользователя";
            }
        } catch(PDOException $e) {
            $error = "Ошибка базы данных: " . $e->getMessage();
            logError("Delete user error: " . $e->getMessage());
        }
    }
}

// Получаем всех пользователей
$stmt = $pdo->prepare("
    SELECT u.*, r.title as role_title 
    FROM users u 
    LEFT JOIN role r ON u.role_id = r.role_id 
    ORDER BY u.role_id, u.name
");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем роли для формы
$stmt_roles = $pdo->query("SELECT * FROM role ORDER BY role_id");
$roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

$title = "Управление пользователями - Лагерь Смена";
require_once 'header.php';
?>

<div class="container">
    <h2>👥 Управление пользователями</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Кнопка для открытия модального окна -->
    <div class="section-header">
        <h3>📋 Список пользователей</h3>
        <button type="button" class="btn btn-primary" onclick="openModal()">
            ➕ Добавить пользователя
        </button>
    </div>

    <!-- Список пользователей -->
    <div class="users-list-section">
        <?php if (empty($users)): ?>
            <p>Пользователи не найдены.</p>
        <?php else: ?>
            <div class="users-table-container">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Фамилия</th>
                            <th>Логин</th>
                            <th>Роль</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="<?php echo $user['id_user'] == getUserId() ? 'current-user' : ''; ?>">
                                <td><?php echo $user['id_user']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['lastname']); ?></td>
                                <td><?php echo htmlspecialchars($user['login']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role_id']; ?>">
                                        <?php echo htmlspecialchars($user['role_title']); ?>
                                    </span>
                                </td>
                                <td class="user-actions">
                                    <a href="edit_user.php?id=<?php echo $user['id_user']; ?>" class="btn btn-small">✏️ Редактировать</a>
                                    <?php if ($user['id_user'] != getUserId()): ?>
                                        <a href="manage_users.php?delete=<?php echo $user['id_user']; ?>" 
                                           class="btn btn-small btn-danger" 
                                           onclick="return confirm('Вы уверены, что хотите удалить пользователя <?php echo htmlspecialchars($user['name'] . ' ' . $user['lastname']); ?>?')">
                                            🗑️ Удалить
                                        </a>
                                    <?php else: ?>
                                        <span class="current-user-label">Это вы</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Модальное окно для добавления пользователя -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>➕ Добавить нового пользователя</h3>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="POST" class="user-form" id="addUserForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Имя:</label>
                        <input type="text" id="name" name="name" required 
                               placeholder="Введите имя">
                    </div>
                    
                    <div class="form-group">
                        <label for="lastname">Фамилия:</label>
                        <input type="text" id="lastname" name="lastname" required 
                               placeholder="Введите фамилию">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="login">Логин:</label>
                        <input type="text" id="login" name="login" required 
                               placeholder="Введите логин">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Пароль:</label>
                        <input type="password" id="password" name="password" required 
                               placeholder="Введите пароль">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role_id">Роль:</label>
                    <select id="role_id" name="role_id" required>
                        <option value="">Выберите роль</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['role_id']; ?>">
                                <?php echo htmlspecialchars($role['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="add_user" class="btn btn-primary">Добавить пользователя</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Отмена</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Функции для работы с модальным окном
    function openModal() {
        document.getElementById('addUserModal').style.display = 'block';
        document.body.style.overflow = 'hidden'; // Блокируем прокрутку фона
    }

    function closeModal() {
        document.getElementById('addUserModal').style.display = 'none';
        document.body.style.overflow = 'auto'; // Восстанавливаем прокрутку
        // Очищаем форму при закрытии
        document.getElementById('addUserForm').reset();
    }

    // Закрытие модального окна при клике вне его
    window.onclick = function(event) {
        const modal = document.getElementById('addUserModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Закрытие модального окна при нажатии Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // Валидация формы
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        const lastname = document.getElementById('lastname').value.trim();
        const login = document.getElementById('login').value.trim();
        const password = document.getElementById('password').value.trim();
        const role = document.getElementById('role_id').value;

        if (!name || !lastname || !login || !password || !role) {
            e.preventDefault();
            alert('Пожалуйста, заполните все поля!');
            return false;
        }

        // Показываем подтверждение
        const confirmed = confirm(`Создать пользователя?\n\nИмя: ${name}\nФамилия: ${lastname}\nЛогин: ${login}\nРоль: ${document.getElementById('role_id').options[document.getElementById('role_id').selectedIndex].text}`);
        if (!confirmed) {
            e.preventDefault();
            return false;
        }
    });
</script>
