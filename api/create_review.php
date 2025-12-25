<?php
/**
 * API для создания отзыва
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if(!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Необходима авторизация'
    ]);
    exit;
}

require_once '../classes/Database.php';
require_once '../classes/Review.php';
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

$review = new Review($db);
$application = new Application($db);

$application_id = (int)($_POST['application_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$comment = trim($_POST['comment'] ?? '');

$errors = [];

if($application_id <= 0) {
    $errors[] = 'Неверный ID заявки';
}

// Проверяем, может ли пользователь оставить отзыв
if($application_id > 0 && !$application->canLeaveReview($application_id, $_SESSION['user_id'])) {
    $errors[] = 'Отзыв можно оставить только после завершения обучения';
}

if(!$review->validateRating($rating)) {
    $errors[] = 'Рейтинг должен быть от 1 до 5';
}

if(empty($comment)) {
    $errors[] = 'Напишите комментарий к отзыву';
}

if(!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => implode('. ', $errors),
        'errors' => $errors
    ]);
    exit;
}

$review->application_id = $application_id;
$review->user_id = $_SESSION['user_id'];
$review->rating = $rating;
$review->comment = $comment;

$result = $review->create();
echo json_encode($result);
?>

