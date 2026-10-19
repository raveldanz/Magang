$php = "php"

Write-Host "1. Checking database connection..."
& $php artisan db:show

Write-Host "2. Running migrations..."
& $php artisan migrate --force

Write-Host "3. Running database seeders..."
& $php artisan db:seed --force

Write-Host "=== Laravel DB Ready! ==="
