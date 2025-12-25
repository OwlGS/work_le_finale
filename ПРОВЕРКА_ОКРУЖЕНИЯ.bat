@echo off
chcp 65001 >nul
cls
echo ========================================
echo  Environment Check
echo ========================================
echo.

set ALL_OK=1

echo [1/5] Checking Node.js...
where node >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    node --version
    echo OK Node.js installed
) else (
    echo ERROR Node.js NOT installed
    echo   Download from https://nodejs.org
    set ALL_OK=0
)
echo.

echo [2/5] Checking npm...
where npm >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    npm --version
    echo OK npm installed
) else (
    echo ERROR npm NOT installed
    set ALL_OK=0
)
echo.

echo [3/5] Checking Python...
where python >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    python --version
    echo OK Python installed
) else (
    echo ERROR Python NOT installed
    echo   Download from https://www.python.org
    set ALL_OK=0
)
echo.

echo [4/5] Checking PostgreSQL...
set PSQL_PATH=C:\postgreSQL\bin\psql.exe
if exist "%PSQL_PATH%" (
    "%PSQL_PATH%" --version
    echo OK PostgreSQL found
) else (
    echo ERROR PostgreSQL not found at: %PSQL_PATH%
    echo   Download from https://www.postgresql.org/download/windows/
    echo   Or change path in СОЗДАТЬ_БД.bat
    set ALL_OK=0
)
echo.

echo [5/5] Checking Git...
where git >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    git --version
    echo OK Git installed
) else (
    echo ERROR Git NOT installed
    echo   Download from https://git-scm.com
    set ALL_OK=0
)
echo.

echo ========================================
if %ALL_OK% EQU 1 (
    echo  All tools installed!
    echo ========================================
    echo.
    echo Next steps:
    echo 1. Run СОЗДАТЬ_БД.bat
    echo 2. cd backend ^&^& npm install
    echo 3. Create backend\.env
    echo 4. Run ЗАПУСК_ПРОЕКТА.bat
) else (
    echo  Problems detected
    echo ========================================
    echo.
    echo Install missing components
    echo and run check again
)
echo.
pause
