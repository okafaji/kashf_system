@echo off
REM ============================================
REM Restore Script for kashf_system
REM ============================================
REM This script restores both code and database

setlocal enabledelayedexpansion

REM Configuration
set PROJECT_DIR=D:\laragon\www\kashf_system
set LARAGON_DIR=D:\laragon
set DB_USER=root
set DB_NAME=kashf_system

echo.
echo ============================================
echo kashf_system Restore Wizard
echo ============================================
echo.

REM Step 1: Ask for backup folder
set /p BACKUP_FOLDER="Enter backup folder path (e.g., D:\Backups\kashf_system\backup_2026_02_23): "

if not exist "%BACKUP_FOLDER%" (
    echo [ERROR] Backup folder not found!
    pause
    exit /b 1
)

echo [OK] Backup folder found
echo.

REM Step 2: Extract code
echo [1/3] Extracting code...
set CODE_ZIP=%BACKUP_FOLDER%\code_*.zip
for %%f in (%CODE_ZIP%) do (
    set CODE_FILE=%%f
)

if not exist "%CODE_FILE%" (
    echo [ERROR] Code backup file not found!
    pause
    exit /b 1
)

REM Remove old project folder
if exist "%PROJECT_DIR%" (
    echo [WARNING] Removing existing project directory...
    rmdir /s /q "%PROJECT_DIR%"
)

REM Extract with PowerShell
powershell -Command "Expand-Archive -Path '%CODE_FILE%' -DestinationPath '%LARAGON_DIR%\www' -Force" > nul 2>&1

if %ERRORLEVEL% EQU 0 (
    echo [OK] Code extracted successfully
) else (
    echo [ERROR] Failed to extract code
    pause
    exit /b 1
)

echo.

REM Step 3: Restore database
echo [2/3] Restoring database...

REM First, drop existing database
mysql -u %DB_USER% -e "DROP DATABASE IF EXISTS %DB_NAME%; CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2> nul

if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Failed to create database. Make sure MySQL is running!
    pause
    exit /b 1
)

REM Find and restore SQL file
set SQL_FILE=%BACKUP_FOLDER%\database_*.sql
for %%f in (%SQL_FILE%) do (
    set DB_FILE=%%f
)

if not exist "%DB_FILE%" (
    echo [ERROR] Database backup file not found!
    pause
    exit /b 1
)

mysql -u %DB_USER% %DB_NAME% < "%DB_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo [OK] Database restored successfully
) else (
    echo [ERROR] Failed to restore database
    pause
    exit /b 1
)

echo.

REM Step 4: Create required directories
echo [3/5] Creating required directories...

cd /d "%PROJECT_DIR%"

REM Create storage directories if they don't exist
if not exist "storage\logs" mkdir "storage\logs"
if not exist "storage\framework\cache" mkdir "storage\framework\cache"
if not exist "storage\framework\sessions" mkdir "storage\framework\sessions"
if not exist "storage\framework\testing" mkdir "storage\framework\testing"
if not exist "storage\framework\views" mkdir "storage\framework\views"
if not exist "bootstrap\cache" mkdir "bootstrap\cache"

echo [OK] Required directories created

echo.

REM Step 5: Install dependencies
echo [4/5] Installing dependencies...

echo Installing PHP dependencies...
call composer install --no-dev --optimize-autoloader > nul 2>&1

echo Installing Node dependencies...
call npm install --production > nul 2>&1

echo Building assets...
call npm run build > nul 2>&1

if %ERRORLEVEL% EQU 0 (
    echo [OK] Dependencies installed successfully
) else (
    echo [WARNING] Some dependencies may have failed to install
)

echo.

REM Step 6: Laravel optimization
echo [5/5] Optimizing Laravel...

call php artisan storage:link > nul 2>&1
call php artisan config:cache > nul 2>&1
call php artisan route:cache > nul 2>&1
call php artisan view:cache > nul 2>&1

echo [OK] Laravel optimized

echo.
echo ============================================
echo Restore Summary
echo ============================================
echo Project Location: %PROJECT_DIR%
echo Database: %DB_NAME%
echo [OK] Restore completed successfully!
echo.
echo Next steps:
echo 1. Update .env file if necessary
echo 2. Run: php artisan key:generate (if not already done)
echo 3. Run: php artisan serve
echo.

pause
endlocal
