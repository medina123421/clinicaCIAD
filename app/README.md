# Aplicación Web - CIADI

Centro Integral de Atención a la Diabetes.

## 🚀 Tecnologías Utilizadas

- **Backend**: PHP 8.x
- **Frontend**: HTML5 + Bootstrap 5
- **Base de Datos**: MySQL (XAMPP)
- **Interactividad**: JavaScript + jQuery + AJAX
- **Gráficas**: Chart.js
- **PDFs**: DomPDF (pendiente instalación)
- **Excel**: PhpSpreadsheet (pendiente instalación)
- **Iconos**: Bootstrap Icons

## 📋 Requisitos

- XAMPP (Apache + MySQL + PHP)
- Navegador web moderno
- Composer (opcional, para PDFs y Excel)

## ⚙️ Instalación

### 1. Base de Datos

La base de datos ya debe estar instalada. Si no lo está, ejecute:

```bash
cd "C:\Users\medin\Desktop\Clinica InvestLab"
.\instalar_base_datos.bat
```

### 2. Usuario Administrador

Ejecute el script SQL para crear el usuario administrador:

```bash
cd app
C:\xampp\mysql\bin\mysql.exe -u root clinica_diabetes < crear_usuario_admin.sql
```

O manualmente en phpMyAdmin:
1. Abra http://localhost/phpmyadmin
2. Seleccione la base de datos `clinica_diabetes`
3. Vaya a la pestaña "SQL"
4. Copie y pegue el contenido de `crear_usuario_admin.sql`
5. Haga clic en "Continuar"

### 3. Acceso a la Aplicación

1. Asegúrese de que XAMPP esté corriendo (Apache y MySQL)
2. Abra su navegador
3. Vaya a: **http://localhost/app/**
4. Inicie sesión con:
   - **Email**: admin@clinica.com
   - **Contraseña**: admin123

> ⚠️ **IMPORTANTE**: Cambie la contraseña después del primer login.

## 📁 Estructura del Proyecto

```
app/
├── config/              # Configuración
│   └── database.php     # Conexión a BD
├── includes/            # Archivos comunes
│   ├── auth.php         # Autenticación
│   ├── header.php       # Header HTML
│   └── footer.php       # Footer HTML
├── models/              # Modelos de datos
│   └── Paciente.php     # Modelo de paciente
├── views/               # Vistas
│   ├── pacientes/       # Módulo de pacientes
│   │   ├── lista.php    # Lista con búsqueda AJAX
│   │   ├── nuevo.php    # Formulario de registro
│   │   └── detalle.php  # Vista de detalle
│   ├── visitas/         # Módulo de visitas (en desarrollo)
│   ├── analisis/        # Módulo de análisis (en desarrollo)
│   └── reportes/        # Módulo de reportes (en desarrollo)
├── ajax/                # Endpoints AJAX
│   └── buscar_pacientes.php
├── assets/              # Recursos estáticos
│   ├── css/
│   │   └── custom.css   # Estilos personalizados
│   └── js/
│       └── app.js       # JavaScript principal
├── index.php            # Dashboard principal
├── login.php            # Página de login
└── logout.php           # Cerrar sesión
```

## ✨ Características Implementadas

### ✅ Sistema de Autenticación
- Login con email y contraseña
- Sesiones PHP seguras
- Contraseñas hasheadas con bcrypt
- Protección de rutas

### ✅ Dashboard
- Estadísticas generales
- Total de pacientes
- Visitas del mes
- Pacientes con control inadecuado
- Próximas citas
- Últimos pacientes registrados
- Accesos rápidos

### ✅ Módulo de Pacientes
- **Lista de pacientes** con búsqueda AJAX en tiempo real
- **Registro de nuevos pacientes** con formulario completo
- **Vista de detalle** con historial médico
- Generación automática de número de expediente
- Validación de formularios (JavaScript y PHP)

### ✅ Diseño Moderno
- Interfaz responsiva con Bootstrap 5
- Tema profesional con colores personalizados
- Animaciones y transiciones suaves
- Iconos de Bootstrap Icons
- Cards con sombras y hover effects

### ✅ Búsqueda AJAX
- Búsqueda en tiempo real sin recargar página
- Debounce para optimizar peticiones
- Resultados instantáneos

## 🔄 Módulos en Desarrollo

Los siguientes módulos están preparados pero pendientes de implementación completa:

- **Visitas**: Registro de visitas y consultas
- **Análisis Clínicos**: Glucosa, perfil renal, perfil lipídico
- **Gráficas**: Tendencias de glucosa, HbA1c, peso, PA
- **Tratamientos**: Prescripción y ajustes
- **Reportes PDF**: Generación con DomPDF
- **Exportación Excel**: Con PhpSpreadsheet

## 🔐 Seguridad

- **SQL Injection**: Protección con PDO prepared statements
- **XSS**: Escape de salida con `htmlspecialchars()`
- **Contraseñas**: Hash con `password_hash()` bcrypt
- **Sesiones**: Regeneración periódica de ID
- **Validación**: Cliente (JavaScript) y servidor (PHP)

## 📊 Uso

### Registrar un Nuevo Paciente

1. Vaya a **Pacientes** → **Nuevo Paciente**
2. Complete el formulario con los datos del paciente
3. El número de expediente se genera automáticamente
4. Haga clic en **Guardar Paciente**

### Buscar Pacientes

1. Vaya a **Pacientes**
2. Escriba en el campo de búsqueda
3. Los resultados se filtran automáticamente
4. Puede buscar por: expediente, nombre, email

### Ver Detalle de Paciente

1. En la lista de pacientes, haga clic en el ícono de ojo 👁️
2. Verá toda la información del paciente
3. Última visita y último análisis
4. Puede editar o registrar nueva visita

## 🛠️ Instalación de Dependencias (Opcional)

Para habilitar generación de PDFs y Excel, instale Composer:

1. Descargue Composer desde: https://getcomposer.org/download/
2. Instale Composer en Windows
3. Abra una terminal en la carpeta `app`
4. Ejecute:
   ```bash
   composer install
   ```

Esto instalará:
- DomPDF para generación de PDFs
- PhpSpreadsheet para exportación a Excel

## 🔧 Configuración

### Cambiar Configuración de Base de Datos

Edite el archivo `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'clinica_diabetes';
private $username = 'root';
private $password = '';
```

### Personalizar Estilos

Edite el archivo `assets/css/custom.css` para cambiar colores y estilos.

## 📝 Próximos Pasos

1. **Implementar módulo de visitas completo**
   - Formulario de nueva visita
   - Captura de signos vitales
   - Notas de consulta

2. **Implementar módulo de análisis clínicos**
   - Formularios para cada tipo de análisis
   - Interpretación automática con semáforos
   - Comparación con análisis anteriores

3. **Implementar gráficas**
   - Glucosa en el tiempo
   - HbA1c trimestral
   - Evolución de peso
   - Presión arterial

4. **Implementar reportes**
   - PDF de paciente
   - PDF de análisis
   - Excel de estadísticas

5. **Implementar tratamientos**
   - Prescripción de medicamentos
   - Ajustes de dosis
   - Historial

## 🐛 Solución de Problemas

### Error de conexión a la base de datos

- Verifique que MySQL esté corriendo en XAMPP
- Verifique las credenciales en `config/database.php`
- Asegúrese de que la base de datos `clinica_diabetes` exista

### No puedo iniciar sesión

- Verifique que ejecutó el script `crear_usuario_admin.sql`
- Use las credenciales: admin@clinica.com / admin123
- Verifique que el usuario esté activo en la tabla `usuarios`

### La búsqueda AJAX no funciona

- Verifique que jQuery esté cargando correctamente
- Abra la consola del navegador (F12) para ver errores
- Verifique que el archivo `ajax/buscar_pacientes.php` exista

## 📞 Soporte

Para problemas o preguntas:
1. Revise la documentación de la base de datos
2. Verifique los logs de PHP en XAMPP
3. Revise la consola del navegador (F12)

## 📄 Licencia

Sistema desarrollado para Clínica InvestLab.

---

**Versión**: 1.0  
**Última actualización**: Enero 2026
