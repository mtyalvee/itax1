@echo off
echo Starting iTax Laravel application...
start "iTax Laravel Server" php artisan serve
start "iTax Vite Server" cmd /c npm run dev
echo Waiting for servers to initialize...
ping 127.0.0.1 -n 4 >nul
start http://localhost:8000
