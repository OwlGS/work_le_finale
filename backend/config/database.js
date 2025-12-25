const { Pool } = require('pg');
require('dotenv').config();

const pool = new Pool({
    host: process.env.DB_HOST || 'localhost',
    port: process.env.DB_PORT || 5432,
    database: process.env.DB_NAME || 'korochki_est',
    user: process.env.DB_USER || 'postgres',
    password: process.env.DB_PASSWORD || '1234',
    max: 20,
    idleTimeoutMillis: 30000,
    connectionTimeoutMillis: 2000,
});

pool.query('SELECT NOW()', (err, res) => {
    if (err) {
        console.error('Ошибка подключения к PostgreSQL:', err.message);
    } else {
        console.log('Подключено к PostgreSQL');
    }
});

pool.on('error', (err) => {
    console.error('Ошибка PostgreSQL:', err.message);
});

module.exports = pool;

