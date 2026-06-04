# ============================================
# Script de démarrage Itrizel RH
# ============================================

Write-Host "`n=== Démarrage Itrizel RH ===" -ForegroundColor Cyan

# 1. Nettoyage
Write-Host "`n[1/5] Nettoyage..." -ForegroundColor Yellow
Remove-Item "C:\xampp\htdocs\itrizel-rh\public\hot" -Force -ErrorAction SilentlyContinue

# 2. MySQL
Write-Host "[2/5] MySQL..." -ForegroundColor Yellow
$mysql = Get-Process mysqld -ErrorAction SilentlyContinue
if (-not $mysql) {
    Remove-Item "C:\xampp\mysql\data\mysql.pid" -Force -ErrorAction SilentlyContinue
    Start-Process "C:\xampp\mysql\bin\mysqld.exe" -ArgumentList "--defaults-file=C:\xampp\mysql\bin\my.ini" -WindowStyle Hidden
    Start-Sleep 4
}
$mysql = Get-Process mysqld -ErrorAction SilentlyContinue
if ($mysql) { Write-Host "      MySQL OK (port 3306)" -ForegroundColor Green }
else { Write-Host "      MySQL ECHEC !" -ForegroundColor Red }

# 3. PHP-CGI
Write-Host "[3/5] PHP-CGI..." -ForegroundColor Yellow
$phpcgi = Get-Process php-cgi -ErrorAction SilentlyContinue
if (-not $phpcgi) {
    Start-Process "C:\xampp\php\php-cgi.exe" -ArgumentList "-b 127.0.0.1:9001" -WindowStyle Hidden
    Start-Sleep 2
}
$check = netstat -ano | findstr "9001"
if ($check) { Write-Host "      PHP-CGI OK (port 9001)" -ForegroundColor Green }
else { Write-Host "      PHP-CGI ECHEC !" -ForegroundColor Red }

# 4. Nginx
Write-Host "[4/5] Nginx..." -ForegroundColor Yellow
$nginx = Get-Process nginx -ErrorAction SilentlyContinue
if ($nginx) {
    # Reload config pour ajouter le vhost RH
    & "D:\nginx\nginx-1.30.2\nginx.exe" -s reload 2>$null
    Write-Host "      Nginx rechargé (ports 8080 + 8081)" -ForegroundColor Green
} else {
    Start-Process "D:\nginx\nginx-1.30.2\nginx.exe" -WorkingDirectory "D:\nginx\nginx-1.30.2" -WindowStyle Hidden
    Start-Sleep 2
    Write-Host "      Nginx OK (ports 8080 + 8081)" -ForegroundColor Green
}

# 5. Vite (dev assets)
Write-Host "[5/5] Vite (assets)..." -ForegroundColor Yellow
Set-Location "C:\xampp\htdocs\itrizel-rh"
Start-Process "npm" -ArgumentList "run dev" -WindowStyle Normal

# Résumé
Write-Host "`n=== Environnement prêt ===" -ForegroundColor Cyan
Write-Host "   GCM    : http://localhost:8080" -ForegroundColor White
Write-Host "   RH     : http://localhost:8081" -ForegroundColor Green
Write-Host ""
