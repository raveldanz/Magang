$php = "C:\Users\TK ABA SBY 69 (3)\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

Write-Host "1. Checking database connection..."
& $php artisan db:show

Write-Host "2. Running migrations..."
& $php artisan migrate --force

Write-Host "3. Running database seeders..."
& $php artisan db:seed --force

Write-Host "=== Laravel DB Ready! ==="
