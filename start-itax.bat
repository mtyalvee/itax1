@echo off
echo Starting iTax Laravel application...
start "iTax Laravel Server" "C:\xampp\php\php.exe" artisan serve
echo Note: Node.js/NPM is not installed globally, skipping Vite Dev Server.
echo (Assets are dynamically loaded via CDN in layout)
echo Waiting for server to initialize...
ping 127.0.0.1 -n 4 >nul
start http://localhost:8000
