# Корочки.есть

Информационная система для записи на онлайн курсы

## Запуск

### 1. Создать базу данных в pgAdmin

1. Открой pgAdmin
2. Правой кнопкой на Databases → Create → Database
3. Имя: korochki_est
4. Encoding: UTF8
5. Сохрани

### 2. Выполнить SQL скрипт

1. Кликни на базу korochki_est
2. Tools → Query Tool
3. Открой файл database/init.sql
4. Выполни весь скрипт (F5)

Должны создаться:
- 4 таблицы (users, courses, applications, reviews)
- Индексы
- Триггеры
- 2 представления
- Админ и тестовые данные

### 3. Установить зависимости

```
cd backend
npm install
```

### 4. Проверить .env

Файл backend/.env должен содержать твои актуальные данные:

```
PORT=3000
NODE_ENV=development
DB_HOST=localhost
DB_PORT=5432
DB_NAME=korochki_est
DB_USER=postgres
DB_PASSWORD=твой_пароль_postgres
JWT_SECRET=a7f8d3e1b9c4f2a8e5d7b3c9f1a4e8d2b5c7f9a1e3d6b8c4f2a7e9d1b3c5f8a2
JWT_EXPIRES_IN=7d
CORS_ORIGIN=*
```

### 5. Запустить

Запусти ЗАПУСК_ПРОЕКТА.bat

Откроется:
- Backend: http://localhost:3000
- Frontend: http://localhost:8000

Проверь что backend запустился и подключился к БД.

### Вход

Администратор: Admin / KorokNET
Тестовый: testuser1 / test1234

## Если не работает

1. Проверь что PostgreSQL запущен
2. Проверь пароль в .env
3. Проверь что все таблицы созданы в pgAdmin
4. Проверь логи в окне Backend

