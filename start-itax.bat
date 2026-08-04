@echo off
echo Starting iTax Laravel application on Apache...
start "iTax Apache Server" "C:\Users\MTY\AppData\Local\Microsoft\WinGet\Packages\ApacheLounge.httpd_Microsoft.Winget.Source_8wekyb3d8bbwe\Apache24\bin\httpd.exe"
start "iTax Vite Server" cmd /c npm run dev
echo Waiting for servers to initialize...
ping 127.0.0.1 -n 4 >nul
start http://127.0.0.1:8085
