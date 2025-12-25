<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/Application.php';

// Получаем список курсов для отображения
$database = new Database();
$db = $database->getConnection();

$courses_query = "SELECT * FROM courses ORDER BY id";
$courses_stmt = $db->prepare($courses_query);
$courses_stmt->execute();
$courses = $courses_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корочки.есть - Портал дополнительного образования</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Навигация -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">📚 Корочки.есть</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a class="nav-link" href="pages/applications.php">Мои заявки</a>
                        <a class="nav-link" href="pages/create_application.php">Подать заявку</a>
                        <?php if($_SESSION['role'] == 'admin'): ?>
                            <a class="nav-link" href="pages/admin.php">Админ панель</a>
                        <?php endif; ?>
                        <span class="nav-link"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        <a class="nav-link" href="api/logout.php">Выход</a>
                    <?php else: ?>
                        <a class="nav-link" href="pages/login.php">Вход</a>
                        <a class="nav-link" href="pages/register.php">Регистрация</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Слайдер изображений -->
        <div class="slider-container">
            <div class="slider-wrapper" id="sliderWrapper">
                <img src="assets/images/slider/slide1.jpg" alt="Курс 1" class="slider-slide">
                <img src="assets/images/slider/slide2.jpg" alt="Курс 2" class="slider-slide">
                <img src="assets/images/slider/slide3.jpg" alt="Курс 3" class="slider-slide">
                <img src="assets/images/slider/slide4.jpg" alt="Курс 4" class="slider-slide">
            </div>
            <button class="slider-controls slider-prev" onclick="changeSlide(-1)">‹</button>
            <button class="slider-controls slider-next" onclick="changeSlide(1)">›</button>
            <div class="slider-dots" id="sliderDots"></div>
        </div>

        <!-- Приветственный блок -->
        <div class="text-center mt-5 mb-4 fade-in">
            <h1 class="display-4">Добро пожаловать на портал дополнительного образования!</h1>
            <p class="lead text-muted">Выберите курс и начните обучение прямо сейчас</p>
        </div>

        <!-- Карточки курсов -->
        <div class="row mt-4">
            <?php foreach($courses as $course): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($course['description']); ?></p>
                        <p class="text-muted"><small>Длительность: <?php echo htmlspecialchars($course['duration']); ?></small></p>
                        <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] != 'admin'): ?>
                            <a href="pages/create_application.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">Записаться на курс</a>
                        <?php elseif(!isset($_SESSION['user_id'])): ?>
                            <a href="pages/register.php" class="btn btn-primary">Зарегистрироваться</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

