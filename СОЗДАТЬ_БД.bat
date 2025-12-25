@echo off
chcp 65001 > nul
cls
echo ========================================
echo  Database Creation
echo ========================================
echo.

set PSQL_PATH=C:\postgreSQL\bin\psql.exe
set PGUSER=postgres
set PGPASSWORD=1234

if not exist "%PSQL_PATH%" (
    echo [ERROR] PostgreSQL not found at: %PSQL_PATH%
    echo.
    echo Possible paths:
    echo - Standalone: C:\Program Files\PostgreSQL\16\bin\psql.exe
    echo - Portable: C:\postgreSQL\bin\psql.exe
    echo.
    echo Change PSQL_PATH variable in this file
    echo.
    pause
    exit /b 1
)

if not exist "database\init.sql" (
    echo [ERROR] File database\init.sql not found!
    pause
    exit /b 1
)

echo [1/3] Connecting to PostgreSQL...
echo.

echo [2/3] Executing SQL script...
"%PSQL_PATH%" -U %PGUSER% -d korochki_est -f "database\init.sql" 2>error.log

if %ERRORLEVEL% EQU 0 (
    echo [3/3] Database created successfully!
    echo.
    echo ========================================
    echo  Database korochki_est ready
    echo ========================================
    echo.
    echo Created:
    echo - Table users
    echo - Table courses
    echo - Table applications
    echo - Table reviews
    echo - Views
    echo.
    echo Data:
    echo - Admin: Admin / KorokNET
    echo - Test users: testuser1 / test1234
    echo - 3 courses
    echo - Test applications and reviews
    echo.
    echo Next steps:
    echo 1. cd backend
    echo 2. npm install
    echo 3. Create backend\.env
    echo 4. Run ЗАПУСК_ПРОЕКТА.bat
    echo.
    if exist error.log del error.log
) else (
    echo [ERROR] Failed to create database
    echo.
    if exist error.log (
        echo Error details:
        type error.log
        echo.
    )
    echo Check:
    echo - PostgreSQL service is running
    echo - Login and password are correct
    echo - File database\init.sql exists
)

echo.
set PGPASSWORD=
pause
