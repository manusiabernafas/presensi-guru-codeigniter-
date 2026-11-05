@echo off
REM Setup Presensi Guru & Ekskul untuk Windows (XAMPP / Laragon). Pastikan Apache & MySQL menyala.
cd /d "%~dp0"
where php >nul 2>nul || (echo PHP tidak ditemukan. Jalankan dari "Shell" di XAMPP Control Panel, atau tambahkan C:\xampp\php ke PATH. & pause & exit /b 1)
php -m | findstr /i /x "intl" >nul || (echo Ekstensi intl belum aktif. Buka C:\xampp\php\php.ini, hapus tanda ; di depan "extension=intl", simpan, lalu restart Apache. & pause & exit /b 1)
if not exist vendor\autoload.php (
  where composer >nul 2>nul || (echo Composer belum terpasang. Unduh di https://getcomposer.org lalu jalankan ulang. & pause & exit /b 1)
  call composer install --no-dev --optimize-autoloader --no-interaction || (pause & exit /b 1)
)
if not exist .env copy .env.example .env >nul
for %%d in (writable\cache writable\logs writable\session writable\uploads\izin writable\debugbar public\uploads\foto) do if not exist %%d mkdir %%d
php spark db:create presensi_guru >nul 2>nul
php spark migrate --all || (pause & exit /b 1)
php spark db:seed DatabaseSeeder || (pause & exit /b 1)
echo.
echo Selesai! Buka http://localhost/presensi-guru-ekskul
echo Login admin: admin / admin123   guru: guru / guru123
pause
