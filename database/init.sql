CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

DROP TABLE IF EXISTS reviews CASCADE;
DROP TABLE IF EXISTS applications CASCADE;
DROP TABLE IF EXISTS courses CASCADE;
DROP TABLE IF EXISTS users CASCADE;
DROP VIEW IF EXISTS applications_full CASCADE;
DROP VIEW IF EXISTS user_stats CASCADE;

CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    login VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE INDEX idx_users_login ON users(login);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

CREATE TABLE courses (
    course_id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    duration VARCHAR(50),
    price DECIMAL(10, 2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_courses_name ON courses(name);

CREATE TABLE applications (
    application_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    course_id INTEGER NOT NULL REFERENCES courses(course_id) ON DELETE RESTRICT,
    start_date DATE NOT NULL,
    payment_method VARCHAR(20) NOT NULL CHECK (payment_method IN ('cash', 'phone_transfer')),
    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'in_progress', 'completed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_applications_user_id ON applications(user_id);
CREATE INDEX idx_applications_status ON applications(status);
CREATE INDEX idx_applications_created_at ON applications(created_at DESC);

CREATE TABLE reviews (
    review_id SERIAL PRIMARY KEY,
    application_id INTEGER NOT NULL REFERENCES applications(application_id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(user_id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_reviews_application_id ON reviews(application_id);

CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_users_updated_at 
    BEFORE UPDATE ON users
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_applications_updated_at 
    BEFORE UPDATE ON applications
    FOR EACH ROW 
    EXECUTE FUNCTION update_updated_at_column();

INSERT INTO users (login, password_hash, full_name, phone, email, role) VALUES
('Admin', crypt('KorokNET', gen_salt('bf')), 'Администратор Портала', '8(000)000-00-00', 'admin@korochki.est', 'admin');

INSERT INTO courses (name, description, duration, price) VALUES
('Основы алгоритмизации и программирования', 
 'Изучение основ алгоритмов, структур данных и написания эффективного кода. Подходит для начинающих программистов.', 
 '3 месяца', 
 15000.00),
('Основы веб-дизайна', 
 'Создание современных веб-интерфейсов, изучение UX/UI принципов, работа с Figma и адаптивной версткой.', 
 '2 месяца', 
 12000.00),
('Основы проектирования баз данных', 
 'Проектирование и разработка реляционных баз данных, нормализация, оптимизация запросов, работа с PostgreSQL и MySQL.', 
 '2 месяца', 
 13000.00);

INSERT INTO users (login, password_hash, full_name, phone, email, role) VALUES
('testuser1', crypt('test1234', gen_salt('bf')), 'Иванов Иван Иванович', '8(911)111-11-11', 'ivanov@test.ru', 'user'),
('testuser2', crypt('test1234', gen_salt('bf')), 'Петрова Анна Сергеевна', '8(922)222-22-22', 'petrova@test.ru', 'user');

INSERT INTO applications (user_id, course_id, start_date, payment_method, status) VALUES
(2, 1, '2025-01-15', 'cash', 'new'),
(2, 2, '2025-02-01', 'phone_transfer', 'in_progress'),
(3, 3, '2024-12-01', 'cash', 'completed');

INSERT INTO reviews (application_id, user_id, rating, comment) VALUES
(3, 3, 5, 'Отличный курс! Все понятно объяснили, много практики. Рекомендую всем, кто хочет освоить базы данных.');

CREATE VIEW applications_full AS
SELECT 
    a.application_id,
    a.user_id,
    u.full_name AS user_name,
    u.email AS user_email,
    u.phone AS user_phone,
    c.name AS course_name,
    c.description AS course_description,
    c.duration AS course_duration,
    c.price AS course_price,
    a.start_date,
    a.payment_method,
    a.status,
    a.created_at,
    a.updated_at,
    r.review_id,
    r.rating,
    r.comment AS review_comment
FROM applications a
JOIN users u ON a.user_id = u.user_id
JOIN courses c ON a.course_id = c.course_id
LEFT JOIN reviews r ON a.application_id = r.application_id
ORDER BY a.created_at DESC;

CREATE VIEW user_stats AS
SELECT 
    u.user_id,
    u.full_name,
    u.email,
    u.phone,
    COUNT(a.application_id) AS total_applications,
    COUNT(CASE WHEN a.status = 'new' THEN 1 END) AS new_applications,
    COUNT(CASE WHEN a.status = 'in_progress' THEN 1 END) AS in_progress_applications,
    COUNT(CASE WHEN a.status = 'completed' THEN 1 END) AS completed_applications,
    COUNT(r.review_id) AS total_reviews
FROM users u
LEFT JOIN applications a ON u.user_id = a.user_id
LEFT JOIN reviews r ON u.user_id = r.user_id
WHERE u.role = 'user'
GROUP BY u.user_id, u.full_name, u.email, u.phone
ORDER BY total_applications DESC;
