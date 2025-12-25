<?php
/**
 * Класс для работы с базой данных
 * Использует PDO для безопасной работы с MySQL
 */
class Database {
    // Параметры подключения к БД
    // Если используете другой пароль для MySQL, измените здесь
    private $host = 'localhost';
    private $db_name = 'korochki_est';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Получение соединения с базой данных
     * @return PDO объект соединения или null при ошибке
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // Создаём соединение через PDO
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            
            // Устанавливаем режим обработки ошибок
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            // В продакшене лучше логировать ошибки, а не выводить
            error_log("Database Connection Error: " . $e->getMessage());
            return null;
        }
        
        return $this->conn;
    }
}
?>

