@echo off
echo ============================================
echo Instalador de Base de Datos - Clinica InvestLab
echo ============================================
echo.

REM Configuracion de XAMPP (ajusta la ruta si es necesaria)
set MYSQL_PATH=C:\xampp\mysql\bin
set DB_NAME=clinica_diabetes
set DB_USER=root
set DB_PASS=

echo Verificando MySQL...
"%MYSQL_PATH%\mysql.exe" --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: No se encontro MySQL en %MYSQL_PATH%
    echo Por favor, ajusta la ruta de XAMPP en este script.
    pause
    exit /b 1
)

echo MySQL encontrado correctamente.
echo.

echo ============================================
echo PASO 1: Creando base de datos
echo ============================================
echo.

"%MYSQL_PATH%\mysql.exe" -u %DB_USER% -e "DROP DATABASE IF EXISTS %DB_NAME%;"
"%MYSQL_PATH%\mysql.exe" -u %DB_USER% -e "CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

if errorlevel 1 (
    echo ERROR: No se pudo crear la base de datos
    pause
    exit /b 1
)

echo Base de datos '%DB_NAME%' creada exitosamente.
echo.

echo ============================================
echo PASO 2: Creando tablas, triggers y vistas
echo ============================================
echo.

"%MYSQL_PATH%\mysql.exe" -u %DB_USER% %DB_NAME% < database_schema.sql

if errorlevel 1 (
    echo ERROR: No se pudieron crear las tablas
    pause
    exit /b 1
)

echo Tablas creadas exitosamente.
echo.

echo ============================================
echo PASO 3: Cargando datos iniciales
echo ============================================
echo.

"%MYSQL_PATH%\mysql.exe" -u %DB_USER% %DB_NAME% < rangos_referencia.sql

if errorlevel 1 (
    echo ERROR: No se pudieron cargar los datos iniciales
    pause
    exit /b 1
)

echo Datos iniciales cargados exitosamente.
echo.

echo ============================================
echo INSTALACION COMPLETADA
echo ============================================
echo.
echo La base de datos '%DB_NAME%' ha sido instalada correctamente.
echo.
echo Resumen:
echo - 35 tablas creadas
echo - 4 triggers instalados
echo - 3 vistas creadas
echo - 50+ rangos de referencia cargados
echo - 60+ reglas de interpretacion cargadas
echo - 20 medicamentos en catalogo
echo.
echo Puedes acceder a la base de datos en:
echo http://localhost/phpmyadmin
echo.
echo Usuario: root
echo Contraseña: (vacia)
echo Base de datos: clinica_diabetes
echo.

pause
