@echo off
cd /d "C:\xampp\htdocs\+RM"
echo.
echo  +RM Dashboard Server
echo  ====================
echo    URL: http://localhost:8585
echo    Press Ctrl+C to stop.
echo.
"C:\xampp\php\php.exe" -S localhost:8585 -t "C:\xampp\htdocs\+RM"
echo.
echo Server stopped.
pause
