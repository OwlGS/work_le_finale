@echo off
chcp 65001 >nul
cls
echo ========================================
echo  Запуск проекта
echo ========================================
echo.

if not exist "backend" (
    echo [ERROR] Backend folder not found!
    pause
    exit /b 1
)

if not exist "backend\.env" (
    echo [ERROR] File backend\.env not found!
    echo.
    echo Create file backend\.env with:
    echo.
    echo PORT=3000
    echo NODE_ENV=development
    echo DB_HOST=localhost
    echo DB_PORT=5432
    echo DB_NAME=korochki_est
    echo DB_USER=postgres
    echo DB_PASSWORD=your_password
    echo JWT_SECRET=a7f8d3e1b9c4f2a8e5d7b3c9f1a4e8d2b5c7f9a1e3d6b8c4f2a7e9d1b3c5f8a2
    echo JWT_EXPIRES_IN=7d
    echo CORS_ORIGIN=*
    echo.
    pause
    exit /b 1
)

if not exist "backend\node_modules" (
    echo [ERROR] Dependencies not installed!
    echo.
    echo Run: cd backend ^&^& npm install
    echo.
    pause
    exit /b 1
)

if not exist "backend\server.js" (
    echo [ERROR] File backend\server.js not found!
    pause
    exit /b 1
)

echo [1/3] Starting backend server...
start "Backend" cmd /k "cd backend && npm start"
timeout /t 3 /nobreak >nul

echo [2/3] Starting frontend...
start "Frontend" cmd /k "python -m http.server 8000"
timeout /t 2 /nobreak >nul

echo [3/3] Opening browser...
start http://localhost:8000/index.html

cls
echo ========================================
echo  Project started successfully!
echo ========================================
echo.
echo Backend API:  http://localhost:3000/api
echo Health check: http://localhost:3000/api/health
echo Frontend:     http://localhost:8000
echo.
echo ========================================
echo  Access
echo ========================================
echo.
echo Admin:
echo   Login:  Admin
echo   Password: KorokNET
echo.
echo Users:
echo   Register on registration page
echo.
echo ========================================
echo  Control
echo ========================================
echo.
echo To stop:
echo - Close Backend and Frontend windows
echo - Or press Ctrl+C in each window
echo.
echo Check server logs in Backend window
echo.
pause
