<?php
/**
 * API для создания заявки на обучение
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Проверка авторизации
if(!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Необходима авторизация'
    ]);
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/Application.php';

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

$application = new Application($db);

// Получаем данные
$course_id = (int)($_POST['course_id'] ?? 0);
$start_date = trim($_POST['start_date'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');

$errors = [];

// Валидация курса
if($course_id <= 0) {
    $errors[] = 'Выберите курс';
}

// Валидация даты
if(empty($start_date)) {
    $errors[] = 'Укажите дату начала обучения';
} elseif(!$application->validateDate($start_date)) {
    $errors[] = 'Дата должна быть в формате ДД.ММ.ГГГГ и не в прошлом';
}

// Валидация способа оплаты
$allowed_payment = ['cash', 'phone_transfer'];
if(empty($payment_method)) {
    $errors[] = 'Выберите способ оплаты';
} elseif(!in_array($payment_method, $allowed_payment)) {
    $errors[] = 'Недопустимый способ оплаты';
}

if(!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode('. ', $errors),
        'errors' => $errors
    ]);
    exit;
}

// Создаём заявку
$application->user_id = $_SESSION['user_id'];
$application->course_id = $course_id;
$application->start_date = $start_date;
$application->payment_method = $payment_method;

$result = $application->create();
echo json_encode($result);
?>

