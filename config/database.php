CREATE DATABASE IF NOT EXISTS korochki_est 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE korochki_est;

<!-- Таблица пользователей -->
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

<!-- Таблица курсов -->
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    duration VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

<!-- Таблица заявок -->
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    start_date DATE NOT NULL,
    payment_method ENUM('cash', 'phone_transfer') NOT NULL,
    status ENUM('new', 'in_progress', 'completed') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

<!-- Таблица отзывов -->
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    application_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_application_id (application_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

<!-- Вставка администратора -->
INSERT INTO users (login, password, full_name, phone, email, role) 
VALUES ('Admin', '123456Qw', 'Администратор', '8(999)999-99-99', 'admin@korochki.est', 'admin');
<!-- Пароль: KorokNEBUDET (нужно будет хешировать через password_hash()) -->

<!-- Вставка курсов -->
INSERT INTO courses (name, description, duration) VALUES
('Основы алгоритмизации и программирования', 'Изучение основ программирования', '3 месяца'),
('Основы веб-дизайна', 'Создание современных веб-интерфейсов', '2 месяца'),
('Основы проектирования баз данных', 'Проектирование и разработка БД', '2 месяца');