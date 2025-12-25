const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const pool = require('../config/database');

router.post('/register', async (req, res) => {
    try {
        const { login, password, full_name, phone, email } = req.body;

        const loginRegex = /^[a-zA-Z0-9]{6,}$/;
        if (!loginRegex.test(login)) {
            return res.status(400).json({ 
                error: 'Логин должен содержать только латиницу и цифры, минимум 6 символов' 
            });
        }

        if (password.length < 8) {
            return res.status(400).json({ 
                error: 'Пароль должен содержать минимум 8 символов' 
            });
        }

        const nameRegex = /^[А-Яа-яЁё\s]+$/;
        if (!nameRegex.test(full_name)) {
            return res.status(400).json({ 
                error: 'ФИО должно содержать только кириллицу и пробелы' 
            });
        }

        const phoneRegex = /^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/;
        if (!phoneRegex.test(phone)) {
            return res.status(400).json({ 
                error: 'Телефон должен быть в формате 8(XXX)XXX-XX-XX' 
            });
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            return res.status(400).json({ 
                error: 'Некорректный формат email' 
            });
        }

        const existingUser = await pool.query(
            'SELECT user_id FROM users WHERE login = $1',
            [login]
        );

        if (existingUser.rows.length > 0) {
            return res.status(400).json({ error: 'Логин уже занят' });
        }

        const existingEmail = await pool.query(
            'SELECT user_id FROM users WHERE email = $1',
            [email]
        );

        if (existingEmail.rows.length > 0) {
            return res.status(400).json({ error: 'Email уже используется' });
        }

        const password_hash = await bcrypt.hash(password, 10);

        const result = await pool.query(
            'INSERT INTO users (login, password_hash, full_name, phone, email) VALUES ($1, $2, $3, $4, $5) RETURNING user_id',
            [login, password_hash, full_name, phone, email]
        );

        res.status(201).json({ 
            success: true,
            message: 'Регистрация успешна',
            user_id: result.rows[0].user_id 
        });

    } catch (error) {
        console.error('Ошибка регистрации:', error);
        res.status(500).json({ error: 'Ошибка сервера' });
    }
});

router.post('/login', async (req, res) => {
    try {
        const { login, password } = req.body;

        const result = await pool.query(
            'SELECT user_id, login, password_hash, full_name, role FROM users WHERE login = $1 AND is_active = TRUE',
            [login]
        );

        if (result.rows.length === 0) {
            return res.status(401).json({ error: 'Неверный логин или пароль' });
        }

        const user = result.rows[0];

        const pgCheck = await pool.query(
            'SELECT (crypt($1, password_hash) = password_hash) as match FROM users WHERE login = $2',
            [password, login]
        );

        const isValidPassword = pgCheck.rows[0] && pgCheck.rows[0].match;

        if (!isValidPassword) {
            return res.status(401).json({ error: 'Неверный логин или пароль' });
        }

        const token = jwt.sign(
            { 
                user_id: user.user_id, 
                login: user.login, 
                role: user.role 
            },
            process.env.JWT_SECRET,
            { expiresIn: process.env.JWT_EXPIRES_IN || '7d' }
        );

        res.json({
            success: true,
            token,
            user: {
                user_id: user.user_id,
                login: user.login,
                full_name: user.full_name,
                role: user.role
            }
        });

    } catch (error) {
        console.error('Ошибка авторизации:', error);
        res.status(500).json({ error: 'Ошибка сервера' });
    }
});

router.get('/verify', async (req, res) => {
    try {
        const authHeader = req.headers['authorization'];
        const token = authHeader && authHeader.split(' ')[1];

        if (!token) {
            return res.status(401).json({ error: 'Токен не предоставлен' });
        }

        const decoded = jwt.verify(token, process.env.JWT_SECRET);

        const result = await pool.query(
            'SELECT user_id, login, full_name, role FROM users WHERE user_id = $1 AND is_active = TRUE',
            [decoded.user_id]
        );

        if (result.rows.length === 0) {
            return res.status(401).json({ error: 'Пользователь не найден' });
        }

        res.json({
            success: true,
            user: result.rows[0]
        });

    } catch (error) {
        res.status(401).json({ error: 'Недействительный токен' });
    }
});

module.exports = router;

