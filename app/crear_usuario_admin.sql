-- ============================================================================
-- CREAR USUARIO ADMINISTRADOR
-- Script para crear el primer usuario del sistema
-- ============================================================================

USE clinica_diabetes;

-- Verificar si ya existe un administrador
SELECT COUNT(*) as total FROM usuarios WHERE id_rol = 1;

-- Si no existe, crear usuario administrador
-- NOTA: La contraseña 'admin123' está hasheada con bcrypt
-- Hash generado: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT INTO usuarios (id_rol, nombre, apellido_paterno, email, password_hash, activo)
SELECT 1, 'Admin', 'Sistema', 'admin@clinica.com', 
       '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1
WHERE NOT EXISTS (
    SELECT 1 FROM usuarios WHERE email = 'admin@clinica.com'
);

-- Verificar creación
SELECT id_usuario, nombre, apellido_paterno, email, nombre_rol
FROM usuarios u
JOIN roles r ON u.id_rol = r.id_rol
WHERE u.email = 'admin@clinica.com';


