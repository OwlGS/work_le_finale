<?php
/**
 * Класс для работы с пользователями
 * Регистрация, авторизация, валидация
 */
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $login;
    public $password;
    public $full_name;
    public $phone;
    public $email;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Валидация логина
     * Требования: латиница и цифры, минимум 6 символов
     */
    public function validateLogin($login) {
        if(empty($login)) {
            return false;
        }
        // Проверяем: только латиница и цифры, от 6 символов
        return preg_match('/^[a-zA-Z0-9]{6,}$/', $login);
    }

    /**
     * Валидация пароля
     * Требования: минимум 8 символов
     */
    public function validatePassword($password) {
        if(empty($password)) {
            return false;
        }
        return strlen($password) >= 8;
    }

    /**
     * Валидация ФИО
     * Требования: только кириллица и пробелы
     */
    public function validateFullName($name) {
        if(empty($name)) {
            return false;
        }
        // Проверяем: только кириллица (включая ё) и пробелы
        return preg_match('/^[А-Яа-яЁё\s]+$/u', $name);
    }

    /**
     * Валидация телефона
     * Формат: 8(XXX)XXX-XX-XX
     */
    public function validatePhone($phone) {
        if(empty($phone)) {
            return false;
        }
        return preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone);
    }

    /**
     * Валидация email
     */
    public function validateEmail($email) {
        if(empty($email)) {
            return false;
        }
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Проверка уникальности логина
     */
    private function isLoginUnique($login) {
        $query = "SELECT id FROM " . $this->table . " WHERE login = :login LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':login', $login);
        $stmt->execute();
        
        return $stmt->rowCount() == 0;
    }

    /**
     * Регистрация нового пользователя
     * @return array ['success' => bool, 'message' => string]
     */
    public function register() {
        // Проверяем уникальность логина
        if(!$this->isLoginUnique($this->login)) {
            return [
                'success' => false,
                'message' => 'Логин уже занят. Выберите другой логин.'
            ];
        }

        // Вставляем пользователя в БД
        $query = "INSERT INTO " . $this->table . " 
                  (login, password, full_name, phone, email) 
                  VALUES (:login, :password, :full_name, :phone, :email)";
        
        $stmt = $this->conn->prepare($query);
        
        // Хешируем пароль перед сохранением
        $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
        
        $stmt->bindParam(':login', $this->login);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':email', $this->email);
        
        if($stmt->execute()) {
            return [
                'success' => true,
                'message' => 'Регистрация успешна! Теперь вы можете войти в систему.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при регистрации. Попробуйте позже.'
            ];
        }
    }

    /**
     * Авторизация пользователя
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function login() {
        $query = "SELECT id, login, password, full_name, role 
                  FROM " . $this->table . " 
                  WHERE login = :login LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':login', $this->login);
        $stmt->execute();
        
        if($stmt->rowCount() == 0) {
            return [
                'success' => false,
                'message' => 'Пользователь с таким логином не найден',
                'user' => null
            ];
        }
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Проверяем пароль
        if(password_verify($this->password, $row['password'])) {
            return [
                'success' => true,
                'message' => 'Вход выполнен успешно',
                'user' => [
                    'id' => $row['id'],
                    'login' => $row['login'],
                    'full_name' => $row['full_name'],
                    'role' => $row['role']
                ]
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Неверный пароль',
                'user' => null
            ];
        }
    }

    /**
     * Получение информации о пользователе по ID
     */
    public function getUserById($user_id) {
        $query = "SELECT id, login, full_name, phone, email, role 
                  FROM " . $this->table . " 
                  WHERE id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

