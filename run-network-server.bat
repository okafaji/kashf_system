@echo off
cd /d "D:\laragon\www\kashf_system"
echo.
echo =========================================
echo  KASHF_SYSTEM Network Server
echo =========================================
echo.
echo Starting PHP server on 0.0.0.0:80
echo Access from any computer on network:
echo   http://169.254.226.16
echo.
echo Press Ctrl+C to stop
echo =========================================
echo.

"D:\laragon\bin\php\php-8.3.10-nts-Win32-vs16-x64\php.exe" -S 0.0.0.0:80 -t public\
pause
