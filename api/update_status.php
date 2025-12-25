<?php
/**
 * API для обновления статуса заявки (только для администратора)
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

// Проверка прав администратора
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'Доступ запрещён'
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

$id = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

if($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Неверный ID заявки'
    ]);
    exit;
}

$result = $application->updateStatus($id, $status);
echo json_encode($result);
?>

