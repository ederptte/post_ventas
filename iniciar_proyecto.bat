@echo off
echo Iniciando Laragon...
start "" "C:\laragon\laragon.exe"

echo Iniciando Backend (Laravel)...
start cmd /k "cd /d C:\Users\ederp\Desktop\Eder_Alvarez\proyecto_venta_tienda && php artisan serve"

echo Iniciando Frontend (Vue)...
start cmd /k "cd /d C:\Users\ederp\Desktop\Eder_Alvarez\proyecto_venta_tienda\frontend-tienda && npm run dev"

echo Abriendo la aplicacion en el navegador...
:: Espera unos segundos opcionales para que los servidores alcancen a arrancar, y abre tu frontend de Vue (por lo general corre en el puerto 5173 o 3000)
timeout /t 3 >nul
start http://localhost:5173

echo ¡Todo listo! Laragon, Laravel y Vue estan corriendo y la app se abrio en tu navegador.
