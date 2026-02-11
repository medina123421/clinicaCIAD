@echo off
echo ============================================
echo INSTALADOR DE APLICACION WEB
echo Clinica InvestLab
echo ============================================
echo.

REM Verificar que estamos en la carpeta correcta
if not exist "composer.json" (
    echo ERROR: Este script debe ejecutarse desde la carpeta 'app'
    echo Por favor, navegue a: C:\Users\medin\Desktop\Clinica InvestLab\app
    pause
    exit /b 1
)

echo Paso 1: Instalando dependencias con Composer...
echo.

REM Verificar si Composer está instalado
where composer >nul 2>&1
if errorlevel 1 (
    echo.
    echo ADVERTENCIA: Composer no está instalado o no está en el PATH
    echo.
    echo Para instalar Composer:
    echo 1. Descargue desde: https://getcomposer.org/download/
    echo 2. Ejecute el instalador
    echo 3. Reinicie esta terminal
    echo.
    echo Presione cualquier tecla para continuar sin instalar dependencias...
    pause >nul
    goto crear_usuario
)

composer install --no-dev --optimize-autoloader

if errorlevel 1 (
    echo.
    echo ERROR: No se pudieron instalar las dependencias
    echo Intente ejecutar manualmente: composer install
    echo.
    pause
    goto crear_usuario
)

echo.
echo Dependencias instaladas correctamente.
echo.

:crear_usuario
echo ============================================
echo Paso 2: Crear Usuario Administrador
echo ============================================
echo.

REM Crear script PHP temporal para crear usuario
echo ^<?php > crear_usuario_temp.php
echo require_once 'config/database.php'; >> crear_usuario_temp.php
echo. >> crear_usuario_temp.php
echo $database = new Database(); >> crear_usuario_temp.php
echo $db = $database-^>getConnection(); >> crear_usuario_temp.php
echo. >> crear_usuario_temp.php
echo // Verificar si ya existe un administrador >> crear_usuario_temp.php
echo $query = "SELECT COUNT(*) as total FROM usuarios WHERE id_rol = 1"; >> crear_usuario_temp.php
echo $stmt = $db-^>query($query); >> crear_usuario_temp.php
echo $result = $stmt-^>fetch(PDO::FETCH_ASSOC); >> crear_usuario_temp.php
echo. >> crear_usuario_temp.php
echo if ($result['total'] ^> 0) { >> crear_usuario_temp.php
echo     echo "Ya existe un usuario administrador.\n"; >> crear_usuario_temp.php
echo     exit(0); >> crear_usuario_temp.php
echo } >> crear_usuario_temp.php
echo. >> crear_usuario_temp.php
echo // Crear usuario administrador >> crear_usuario_temp.php
echo $password = 'admin123'; // Contraseña por defecto >> crear_usuario_temp.php
echo $password_hash = password_hash($password, PASSWORD_BCRYPT); >> crear_usuario_temp.php
echo. >> crear_usuario_temp.php
echo $query = "INSERT INTO usuarios (id_rol, nombre, apellido_paterno, email, password_hash, activo) >> crear_usuario_temp.php
echo           VALUES (1, 'Admin', 'Sistema', 'admin@clinica.com', :password_hash, 1)"; >> crear_usuario_temp.php
echo. >> crear_usuario_temp.php
echo $stmt = $db-^>prepare($query); >> crear_usuario_temp.php
echo $stmt-^>bindParam(':password_hash', $password_hash); >> crear_usuario_temp.php
echo. >> crear_usuario_temp.php
echo if ($stmt-^>execute()) { >> crear_usuario_temp.php
echo     echo "Usuario administrador creado exitosamente.\n"; >> crear_usuario_temp.php
echo     echo "Email: admin@clinica.com\n"; >> crear_usuario_temp.php
echo     echo "Contraseña: admin123\n"; >> crear_usuario_temp.php
echo     echo "\nIMPORTANTE: Cambie esta contraseña después del primer login.\n"; >> crear_usuario_temp.php
echo } else { >> crear_usuario_temp.php
echo     echo "Error al crear el usuario administrador.\n"; >> crear_usuario_temp.php
echo     exit(1); >> crear_usuario_temp.php
echo } >> crear_usuario_temp.php
echo ?^> >> crear_usuario_temp.php

REM Ejecutar script PHP
php crear_usuario_temp.php

REM Eliminar script temporal
del crear_usuario_temp.php

echo.
echo ============================================
echo INSTALACION COMPLETADA
echo ============================================
echo.
echo La aplicacion web esta lista para usarse.
echo.
echo Acceso:
echo URL: http://localhost/app/
echo Email: admin@clinica.com
echo Contraseña: admin123
echo.
echo IMPORTANTE: Cambie la contraseña después del primer login.
echo.
echo Proximos pasos:
echo 1. Abra su navegador
echo 2. Vaya a: http://localhost/app/
echo 3. Inicie sesion con las credenciales de arriba
echo.

pause
