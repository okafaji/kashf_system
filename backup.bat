@echo off
REM ============================================
REM Automated Backup Script for kashf_system
REM ============================================
REM This script backs up both code and database

setlocal enabledelayedexpansion

REM Get current date and time
for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c_%%a_%%b)
for /f "tokens=1-2 delims=/:" %%a in ('time /t') do (set mytime=%%a_%%b)

REM Configuration
set BACKUP_DIR=D:\Backups\kashf_system
set PROJECT_DIR=D:\laragon\www\kashf_system
set ARCHIVE_TOOL=7z
set DB_USER=root
set DB_NAME=kashf_system

REM Create backup directory if not exists
if not exist "%BACKUP_DIR%" (
    mkdir "%BACKUP_DIR%"
    echo [INFO] Created backup directory: %BACKUP_DIR%
)

REM Create timestamped backup folder
set TIMESTAMP=%mydate%_%mytime%
set BACKUP_FOLDER=%BACKUP_DIR%\backup_%TIMESTAMP%
mkdir "%BACKUP_FOLDER%"

echo.
echo ============================================
echo Starting Backup: %TIMESTAMP%
echo ============================================
echo.

REM Backup Database
echo [1/2] Backing up database...
mysqldump -u %DB_USER% %DB_NAME% > "%BACKUP_FOLDER%\database_%TIMESTAMP%.sql"
if %ERRORLEVEL% EQU 0 (
    echo [OK] Database backup completed
) else (
    echo [ERROR] Database backup failed
    goto :END
)

echo.

REM Backup Code (exclude large folders)
echo [2/2] Backing up code...

if exist "C:\Program Files\7-Zip\7z.exe" (
    "C:\Program Files\7-Zip\7z.exe" a "%BACKUP_FOLDER%\code_%TIMESTAMP%.zip" "%PROJECT_DIR%" ^
        -x!%PROJECT_DIR%\node_modules ^
        -x!%PROJECT_DIR%\vendor ^
        -x!%PROJECT_DIR%\storage\logs ^
        -x!%PROJECT_DIR%\.git > nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        echo [OK] Code backup completed with 7z
    ) else (
        echo [ERROR] Code backup failed with 7z
        goto :END
    )
) else if exist "C:\Program Files (x86)\WinRAR\rar.exe" (
    "C:\Program Files (x86)\WinRAR\rar.exe" a -r -x*\node_modules -x*\vendor -x*\storage\logs -x*\.git "%BACKUP_FOLDER%\code_%TIMESTAMP%.zip" "%PROJECT_DIR%" > nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        echo [OK] Code backup completed with WinRAR
    ) else (
        echo [ERROR] Code backup failed with WinRAR
        goto :END
    )
) else (
    echo [WARNING] 7z or WinRAR not found. Attempting built-in compression...
    cd "%BACKUP_DIR%"
    powershell -Command "Compress-Archive -Path '%PROJECT_DIR%' -DestinationPath '%BACKUP_FOLDER%\code_%TIMESTAMP%.zip' -Force" > nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        echo [OK] Code backup completed with PowerShell
    ) else (
        echo [ERROR] Code backup failed
        goto :END
    )
)

echo.
echo ============================================
echo Backup Summary
echo ============================================
echo Backup Folder: %BACKUP_FOLDER%
echo Backup Date: %mydate%
echo Backup Time: %mytime%
echo.
echo [OK] Backup completed successfully!
echo.

:END
pause
endlocal
