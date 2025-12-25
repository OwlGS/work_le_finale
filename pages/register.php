<?php
session_start();
if(isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - Корочки.есть</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">📚 Корочки.есть</a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card fade-in">
                    <div class="card-header">
                        <h3 class="mb-0">Регистрация</h3>
                    </div>
                    <div class="card-body">
                        <div id="error-message" class="alert alert-danger d-none"></div>
                        <div id="success-message" class="alert alert-success d-none"></div>
                        
                        <form id="registerForm">
                            <div class="mb-3">
                                <label for="login" class="form-label">Логин <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="login" name="login" required 
                                       placeholder="Введите логин" autocomplete="username">
                                <small class="form-text text-muted">Латиница и цифры, минимум 6 символов</small>
                                <div class="invalid-feedback" id="login-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required 
                                       placeholder="Введите пароль" autocomplete="new-password">
                                <small class="form-text text-muted">Минимум 8 символов</small>
                                <div class="invalid-feedback" id="password-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="full_name" class="form-label">ФИО <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" required 
                                       placeholder="Иванов Иван Иванович">
                                <small class="form-text text-muted">Только кириллица и пробелы</small>
                                <div class="invalid-feedback" id="full_name-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label">Телефон <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone" required 
                                       placeholder="8(999)999-99-99" maxlength="15">
                                <small class="form-text text-muted">Формат: 8(XXX)XXX-XX-XX</small>
                                <div class="invalid-feedback" id="phone-error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       placeholder="example@mail.ru" autocomplete="email">
                                <div class="invalid-feedback" id="email-error"></div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Создать пользователя</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p>Уже зарегистрированы? <a href="login.php">Войти</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        $(document).ready(function() {
            // Форматирование телефона при вводе
            $('#phone').on('input', function() {
                formatPhone(this);
            });

            // Валидация формы на стороне клиента
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();
                
                // Очищаем предыдущие ошибки
                $('.is-invalid').removeClass('is-invalid');
                $('#error-message').addClass('d-none');
                $('#success-message').addClass('d-none');
                
                let hasErrors = false;
                
                // Валидация логина
                const login = $('#login').val().trim();
                if(login.length < 6 || !/^[a-zA-Z0-9]+$/.test(login)) {
                    $('#login').addClass('is-invalid');
                    $('#login-error').text('Логин должен содержать только латиницу и цифры, минимум 6 символов');
                    hasErrors = true;
                }
                
                // Валидация пароля
                const password = $('#password').val();
                if(password.length < 8) {
                    $('#password').addClass('is-invalid');
                    $('#password-error').text('Пароль должен содержать минимум 8 символов');
                    hasErrors = true;
                }
                
                // Валидация ФИО
                const fullName = $('#full_name').val().trim();
                if(!/^[А-Яа-яЁё\s]+$/.test(fullName)) {
                    $('#full_name').addClass('is-invalid');
                    $('#full_name-error').text('ФИО должно содержать только кириллицу и пробелы');
                    hasErrors = true;
                }
                
                // Валидация телефона
                const phone = $('#phone').val();
                if(!/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/.test(phone)) {
                    $('#phone').addClass('is-invalid');
                    $('#phone-error').text('Телефон должен быть в формате 8(XXX)XXX-XX-XX');
                    hasErrors = true;
                }
                
                // Валидация email
                const email = $('#email').val().trim();
                if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    $('#email').addClass('is-invalid');
                    $('#email-error').text('Некорректный формат email');
                    hasErrors = true;
                }
                
                if(hasErrors) {
                    return;
                }
                
                // Отправка данных на сервер
                $.ajax({
                    url: '../api/register.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if(response.success) {
                            $('#success-message').removeClass('d-none').text(response.message);
                            $('#error-message').addClass('d-none');
                            $('#registerForm')[0].reset();
                            
                            setTimeout(function() {
                                window.location.href = 'login.php';
                            }, 2000);
                        } else {
                            $('#error-message').removeClass('d-none').text(response.message);
                            $('#success-message').addClass('d-none');
                            
                            // Показываем ошибки для конкретных полей, если они есть
                            if(response.errors) {
                                response.errors.forEach(function(error) {
                                    if(error.includes('логин')) {
                                        $('#login').addClass('is-invalid');
                                    }
                                    if(error.includes('пароль')) {
                                        $('#password').addClass('is-invalid');
                                    }
                                    if(error.includes('ФИО')) {
                                        $('#full_name').addClass('is-invalid');
                                    }
                                    if(error.includes('телефон')) {
                                        $('#phone').addClass('is-invalid');
                                    }
                                    if(error.includes('email')) {
                                        $('#email').addClass('is-invalid');
                                    }
                                });
                            }
                        }
                    },
                    error: function() {
                        $('#error-message').removeClass('d-none').text('Ошибка при отправке данных. Попробуйте позже.');
                    }
                });
            });
        });
    </script>
</body>
</html>

