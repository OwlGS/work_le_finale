<?php
/**
 * API для регистрации пользователей
 * Обрабатывает POST запросы с данными регистрации
 */
header('Content-Type: application/json; charset=utf-8');

// Подключаем необходимые классы
require_once '../classes/Database.php';
require_once '../classes/User.php';

// Проверяем метод запроса
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Метод не поддерживается'
    ]);
    exit;
}

// Подключаемся к БД
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

// Получаем и очищаем данные из формы
$login = trim($_POST['login'] ?? '');
$password = trim($_POST['password'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');

// Массив для ошибок валидации
$errors = [];

// Валидация всех полей
if(empty($login)) {
    $errors[] = 'Логин обязателен для заполнения';
} elseif(!$user->validateLogin($login)) {
    $errors[] = 'Логин должен содержать только латиницу и цифры, минимум 6 символов';
}

if(empty($password)) {
    $errors[] = 'Пароль обязателен для заполнения';
} elseif(!$user->validatePassword($password)) {
    $errors[] = 'Пароль должен содержать минимум 8 символов';
}

if(empty($full_name)) {
    $errors[] = 'ФИО обязательно для заполнения';
} elseif(!$user->validateFullName($full_name)) {
    $errors[] = 'ФИО должно содержать только кириллицу и пробелы';
}

if(empty($phone)) {
    $errors[] = 'Телефон обязателен для заполнения';
} elseif(!$user->validatePhone($phone)) {
    $errors[] = 'Телефон должен быть в формате 8(XXX)XXX-XX-XX';
}

if(empty($email)) {
    $errors[] = 'Email обязателен для заполнения';
} elseif(!$user->validateEmail($email)) {
    $errors[] = 'Некорректный формат email';
}

// Если есть ошибки валидации, возвращаем их
if(!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode('. ', $errors),
        'errors' => $errors
    ]);
    exit;
}

// Заполняем объект пользователя
$user->login = $login;
$user->password = $password;
$user->full_name = $full_name;
$user->phone = $phone;
$user->email = $email;

// Пытаемся зарегистрировать
$result = $user->register();

// Возвращаем результат
echo json_encode($result);
?>

