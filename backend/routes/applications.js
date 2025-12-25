const express = require('express');
const router = express.Router();
const pool = require('../config/database');
const { authenticateToken, requireAdmin } = require('../middleware/auth');

router.get('/courses', async (req, res) => {
    try {
        const result = await pool.query('SELECT * FROM courses ORDER BY name');
        res.json(result.rows);
    } catch (error) {
        console.error('Ошибка получения курсов:', error);
        res.status(500).json({ error: 'Ошибка сервера' });
    }
});

router.post('/', authenticateToken, async (req, res) => {
    try {
        const { course_id, start_date, payment_method } = req.body;
        const user_id = req.user.user_id;


        if (!course_id || !start_date || !payment_method) {
            return res.status(400).json({ error: 'Все поля обязательны' });
        }

        if (!user_id) {
            return res.status(401).json({ error: 'Пользователь не авторизован' });
        }

        if (!['cash', 'phone_transfer'].includes(payment_method)) {
            return res.status(400).json({ error: 'Некорректный способ оплаты' });
        }

        const dateRegex = /^\d{2}\.\d{2}\.\d{4}$/;
        if (!dateRegex.test(start_date)) {
            return res.status(400).json({ error: 'Дата должна быть в формате ДД.ММ.ГГГГ' });
        }

        const [day, month, year] = start_date.split('.');
        const formattedDate = `${year}-${month}-${day}`;

        const courseIdInt = parseInt(course_id);
        if (isNaN(courseIdInt)) {
            return res.status(400).json({ error: 'Некорректный ID курса' });
        }

        const courseCheck = await pool.query(
            'SELECT course_id FROM courses WHERE course_id = $1',
            [courseIdInt]
        );

        if (courseCheck.rows.length === 0) {
            return res.status(400).json({ error: 'Курс не найден' });
        }

        const result = await pool.query(
            'INSERT INTO applications (user_id, course_id, start_date, payment_method) VALUES ($1, $2, $3, $4) RETURNING application_id',
            [user_id, courseIdInt, formattedDate, payment_method]
        );

        res.status(201).json({ 
            success: true,
            message: 'Заявка отправлена на рассмотрение',
            application_id: result.rows[0].application_id 
        });

    } catch (error) {
        console.error('Ошибка создания заявки:', error);
        console.error('Детали ошибки:', error.message);
        res.status(500).json({ error: 'Ошибка сервера: ' + error.message });
    }
});

router.get('/my', authenticateToken, async (req, res) => {
    try {
        const user_id = req.user.user_id;

        const result = await pool.query(`
            SELECT 
                a.application_id,
                TO_CHAR(a.start_date, 'DD.MM.YYYY') as start_date,
                a.payment_method,
                a.status,
                a.created_at,
                c.name as course_name,
                c.description as course_description,
                c.duration as course_duration,
                c.price as course_price,
                r.review_id,
                r.rating,
                r.comment as review_comment
            FROM applications a
            JOIN courses c ON a.course_id = c.course_id
            LEFT JOIN reviews r ON a.application_id = r.application_id
            WHERE a.user_id = $1
            ORDER BY a.created_at DESC
        `, [user_id]);

        res.json(result.rows);

    } catch (error) {
        console.error('Ошибка получения заявок:', error);
        res.status(500).json({ error: 'Ошибка сервера' });
    }
});

router.get('/all', authenticateToken, requireAdmin, async (req, res) => {
    try {
        const result = await pool.query(`
            SELECT 
                a.application_id,
                a.user_id,
                u.full_name as user_name,
                u.email as user_email,
                u.phone as user_phone,
                c.name as course_name,
                TO_CHAR(a.start_date, 'DD.MM.YYYY') as start_date,
                a.payment_method,
                a.status,
                a.created_at,
                a.updated_at
            FROM applications a
            JOIN users u ON a.user_id = u.user_id
            JOIN courses c ON a.course_id = c.course_id
            ORDER BY a.created_at DESC
        `);

        res.json(result.rows);

    } catch (error) {
        console.error('Ошибка получения всех заявок:', error);
        res.status(500).json({ error: 'Ошибка сервера' });
    }
});

router.patch('/:id/status', authenticateToken, requireAdmin, async (req, res) => {
    try {
        const { id } = req.params;
        const { status } = req.body;

        if (!['new', 'in_progress', 'completed'].includes(status)) {
            return res.status(400).json({ error: 'Некорректный статус' });
        }

        const result = await pool.query(
            'UPDATE applications SET status = $1 WHERE application_id = $2 RETURNING application_id',
            [status, id]
        );

        if (result.rows.length === 0) {
            return res.status(404).json({ error: 'Заявка не найдена' });
        }

        res.json({ success: true, message: 'Статус обновлен' });

    } catch (error) {
        console.error('Ошибка обновления статуса:', error);
        res.status(500).json({ error: 'Ошибка сервера' });
    }
});

router.post('/:id/review', authenticateToken, async (req, res) => {
    try {
        const { id } = req.params;
        const { rating, comment } = req.body;
        const user_id = req.user.user_id;

        if (!rating || rating < 1 || rating > 5) {
            return res.status(400).json({ error: 'Рейтинг должен быть от 1 до 5' });
        }

        const applicationCheck = await pool.query(
            'SELECT status FROM applications WHERE application_id = $1 AND user_id = $2',
            [id, user_id]
        );

        if (applicationCheck.rows.length === 0) {
            return res.status(404).json({ error: 'Заявка не найдена' });
        }

        if (applicationCheck.rows[0].status !== 'completed') {
            return res.status(400).json({ error: 'Отзыв можно оставить только после завершения курса' });
        }

        const reviewCheck = await pool.query(
            'SELECT review_id FROM reviews WHERE application_id = $1',
            [id]
        );

        if (reviewCheck.rows.length > 0) {
            return res.status(400).json({ error: 'Отзыв уже оставлен для этой заявки' });
        }

        await pool.query(
            'INSERT INTO reviews (application_id, user_id, rating, comment) VALUES ($1, $2, $3, $4)',
            [id, user_id, rating, comment]
        );

        res.status(201).json({ success: true, message: 'Отзыв успешно добавлен' });

    } catch (error) {
        console.error('Ошибка добавления отзыва:', error);
        res.status(500).json({ error: 'Ошибка сервера' });
    }
});

module.exports = router;

