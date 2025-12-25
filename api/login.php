<?php
/**
 * API для авторизации пользователей
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../classes/Database.php';
require_once '../classes/User.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается'
    ]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if($db === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка подключения к базе данных'
    ]);
    exit;
}

$user = new User($db);

// Получаем данные
$login = trim($_POST['login'] ?? '');
$password = trim($_POST['password'] ?? '');

// Валидация
if(empty($login)) {
    echo json_encode([
        'success' => false,
        'message' => 'Введите логин'
    ]);
    exit;
}

if(empty($password)) {
    echo json_encode([
        'success' => false,
        'message' => 'Введите пароль'
    ]);
    exit;
}

$user->login = $login;
$user->password = $password;

$result = $user->login();

if($result['success']) {
    // Сохраняем данные пользователя в сессию
    $_SESSION['user_id'] = $result['user']['id'];
    $_SESSION['login'] = $result['user']['login'];
    $_SESSION['full_name'] = $result['user']['full_name'];
    $_SESSION['role'] = $result['user']['role'];
}

echo json_encode($result);
?>

