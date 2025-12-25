<?php
/**
 * Выход из системы
 */
session_start();

// Уничтожаем сессию
session_unset();
session_destroy();

// Перенаправляем на главную
header('Location: ../index.php');
exit;
?>

