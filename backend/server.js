require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const bodyParser = require('body-parser');
const pool = require('./config/database');

const authRoutes = require('./routes/auth');
const applicationsRoutes = require('./routes/applications');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(helmet());

app.use(cors({
    origin: process.env.CORS_ORIGIN || '*',
    credentials: true
}));

const limiter = rateLimit({
    windowMs: 15 * 60 * 1000,
    max: 100,
    message: 'Слишком много запросов с вашего IP, попробуйте позже'
});
app.use('/api/', limiter);

app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

app.use(express.static('../'));

app.get('/api/health', async (req, res) => {
    try {
        await pool.query('SELECT 1');
        res.json({ 
            status: 'OK', 
            message: 'Сервер работает',
            database: 'Подключено к PostgreSQL',
            timestamp: new Date().toISOString()
        });
    } catch (error) {
        res.status(500).json({ 
            status: 'ERROR', 
            message: 'Ошибка подключения к базе данных',
            error: error.message
        });
    }
});

app.use('/api/auth', authRoutes);
app.use('/api/applications', applicationsRoutes);

app.use((req, res) => {
    res.status(404).json({ error: 'Маршрут не найден' });
});

app.use((err, req, res, next) => {
    console.error('Ошибка сервера:', err.stack);
    res.status(500).json({ error: 'Внутренняя ошибка сервера' });
});

app.listen(PORT, () => {
    console.log('========================================');
    console.log('  Сервер Корочки.есть');
    console.log('========================================');
    console.log('Сервер запущен на порту ' + PORT);
    console.log('API: http://localhost:' + PORT + '/api');
    console.log('Health: http://localhost:' + PORT + '/api/health');
    console.log('========================================');
});

process.on('SIGTERM', async () => {
    console.log('SIGTERM получен, закрытие сервера...');
    await pool.end();
    console.log('Пул соединений с БД закрыт');
    process.exit(0);
});

process.on('SIGINT', async () => {
    console.log('SIGINT получен, закрытие сервера...');
    await pool.end();
    console.log('Пул соединений с БД закрыт');
    process.exit(0);
});

module.exports = app;

