# Base de Datos - Clínica de Diabetes InvestLab

Sistema de gestión integral para clínica especializada en diabetes y prediabetes.

## 📋 Contenido del Proyecto

Este proyecto incluye:

- **database_schema.sql**: Script completo con 35 tablas, triggers y vistas
- **rangos_referencia.sql**: Datos iniciales con rangos de referencia e interpretaciones
- **database_diagram.md**: Diagrama entidad-relación completo
- **database_documentation.md**: Documentación detallada de todas las tablas
- **sample_queries.sql**: 26 consultas SQL de ejemplo listas para usar

## 🚀 Instalación Rápida

### Requisitos Previos

- MySQL 8.0 o superior
- Acceso con privilegios de administrador

### Paso 1: Crear la Base de Datos

```bash
mysql -u root -p < database_schema.sql
```

Esto creará:
- Base de datos `clinica_diabetes`
- 35 tablas con todas las relaciones
- Triggers automáticos (cálculo de IMC y edad)
- 3 vistas útiles
- Índices optimizados

### Paso 2: Cargar Datos Iniciales

```bash
mysql -u root -p clinica_diabetes < rangos_referencia.sql
```

Esto insertará:
- Roles (Administrador, Doctor)
- Rangos de referencia para todos los parámetros clínicos
- Reglas de interpretación automática
- Catálogo de medicamentos comunes

### Paso 3: Crear Usuario Administrador

```sql
-- Primero hashea la contraseña en tu aplicación con bcrypt
-- Ejemplo en PHP: password_hash('tu_contraseña', PASSWORD_BCRYPT)

USE clinica_diabetes;

INSERT INTO usuarios (id_rol, nombre, apellido_paterno, email, password_hash, activo)
VALUES (1, 'Admin', 'Sistema', 'admin@clinica.com', 'HASH_BCRYPT_AQUI', TRUE);
```

## 📊 Características Principales

### 1. Sistema de Interpretación Automática

Cada análisis clínico incluye interpretación automática con sistema de semáforos:

- 🟢 **Verde (Normal)**: Valores dentro del rango normal
- 🟡 **Amarillo (Precaución)**: Valores que requieren monitoreo
- 🔴 **Rojo (Alerta)**: Valores que requieren atención inmediata

### 2. Cálculos Automáticos

- **IMC**: Se calcula automáticamente al ingresar peso y talla
- **Edad**: Se calcula automáticamente desde la fecha de nacimiento
- **TFG**: Tasa de filtración glomerular

### 3. Módulos Completos

1. **Usuarios**: Control de acceso (Admin y Doctores)
2. **Pacientes**: Información demográfica completa
3. **Visitas**: Registro de consultas médicas
4. **Análisis Clínicos**: 7 tipos de análisis diferentes
5. **Tratamientos**: Medicamentos y ajustes
6. **Control Glucémico**: Glucometrías diarias
7. **Complicaciones**: Micro y macrovasculares
8. **Anexos**: Archivos adjuntos

## 📈 Gráficas Disponibles

El sistema está diseñado para generar las siguientes gráficas:

1. **Glucosa en ayunas** - Evolución temporal
2. **HbA1c** - Tendencia trimestral/semestral
3. **Peso e IMC** - Evolución del peso corporal
4. **Presión Arterial** - Sistólica y diastólica
5. **Perfil Lipídico** - Colesterol, LDL, HDL, triglicéridos
6. **Función Renal** - TFG y creatinina

## 🔍 Consultas de Ejemplo

El archivo `sample_queries.sql` incluye 26 consultas listas para usar:

- Búsqueda de pacientes
- Historial de visitas
- Análisis con interpretación
- Datos para gráficas
- Tratamientos activos
- Eventos de hipoglucemia/hiperglucemia
- Reportes y estadísticas

## 📚 Estructura de la Base de Datos

### Tablas Principales (35 tablas)

**Módulo de Usuarios:**
- roles
- usuarios
- sesiones

**Módulo de Pacientes:**
- pacientes
- contactos_emergencia
- antecedentes_familiares

**Módulo de Visitas:**
- visitas
- datos_clinicos
- notas_consulta

**Módulo de Análisis:**
- analisis_glucosa
- analisis_perfil_renal
- analisis_perfil_lipidico
- analisis_electrolitos
- analisis_hepaticos
- analisis_cardiovascular
- analisis_otros

**Módulo de Tratamientos:**
- medicamentos_catalogo
- tratamientos
- ajustes_tratamiento

**Módulo de Control:**
- glucometrias
- hipoglucemias
- hiperglucemias

**Módulo de Complicaciones:**
- complicaciones_microvasculares
- complicaciones_macrovasculares

**Otros Módulos:**
- estilo_vida
- educacion_diabetes
- salud_mental
- anexos
- rangos_referencia
- interpretaciones

## 🎯 Rangos de Referencia Principales

### Glucosa y HbA1c

| Parámetro | Normal | Precaución | Alerta |
|-----------|--------|------------|--------|
| Glucosa ayunas | 70-100 mg/dL | 100-126 mg/dL | ≥126 o <70 mg/dL |
| HbA1c | <5.7% | 5.7-6.4% | ≥6.5% |

### Presión Arterial

| Parámetro | Normal | Precaución | Alerta |
|-----------|--------|------------|--------|
| Sistólica | <120 mmHg | 120-139 mmHg | ≥140 mmHg |
| Diastólica | <80 mmHg | 80-89 mmHg | ≥90 mmHg |

### Perfil Lipídico

| Parámetro | Normal | Precaución | Alerta |
|-----------|--------|------------|--------|
| Colesterol total | <200 mg/dL | 200-239 mg/dL | ≥240 mg/dL |
| LDL | <100 mg/dL | 100-159 mg/dL | ≥160 mg/dL |
| Triglicéridos | <150 mg/dL | 150-199 mg/dL | ≥200 mg/dL |

## 💻 Integración con Aplicación Web

### Endpoints Sugeridos para API REST

```
GET    /api/pacientes                    # Lista de pacientes
POST   /api/pacientes                    # Crear paciente
GET    /api/pacientes/{id}               # Detalle de paciente
PUT    /api/pacientes/{id}               # Actualizar paciente

GET    /api/pacientes/{id}/visitas       # Visitas del paciente
POST   /api/visitas                      # Registrar visita
GET    /api/visitas/{id}                 # Detalle de visita

POST   /api/analisis/glucosa             # Registrar análisis de glucosa
GET    /api/analisis/{id}/interpretacion # Obtener interpretación

GET    /api/graficas/glucosa/{id}        # Datos para gráfica de glucosa
GET    /api/graficas/hba1c/{id}          # Datos para gráfica de HbA1c
GET    /api/graficas/peso/{id}           # Datos para gráfica de peso

GET    /api/tratamientos/{id}            # Tratamientos del paciente
POST   /api/tratamientos                 # Prescribir medicamento

GET    /api/glucometrias/{id}            # Glucometrías del paciente
POST   /api/glucometrias                 # Registrar glucometría
```

### Ejemplo de Uso en Aplicación

**Obtener interpretación de glucosa:**

```sql
-- Usar la consulta #24 de sample_queries.sql
-- Reemplazar los ? con el valor de glucosa del paciente
```

**Generar gráfica de HbA1c:**

```sql
-- Usar la consulta #10 de sample_queries.sql
-- Retorna datos listos para Chart.js o similar
```

## 🔐 Seguridad

### Recomendaciones Importantes

1. **Contraseñas**: SIEMPRE hashear con bcrypt antes de almacenar
2. **Conexión**: Usar SSL/TLS para conexiones a MySQL
3. **Usuarios**: Crear usuarios de MySQL con privilegios mínimos
4. **Respaldos**: Hacer respaldos diarios automáticos
5. **Auditoría**: Todos los cambios se registran con `created_by` y timestamps

### Crear Usuario de Aplicación (Privilegios Limitados)

```sql
CREATE USER 'clinica_app'@'localhost' IDENTIFIED BY 'contraseña_segura';
GRANT SELECT, INSERT, UPDATE ON clinica_diabetes.* TO 'clinica_app'@'localhost';
FLUSH PRIVILEGES;
```

## 🛠️ Mantenimiento

### Respaldos

```bash
# Respaldo completo
mysqldump -u root -p clinica_diabetes > backup_$(date +%Y%m%d).sql

# Respaldo solo estructura
mysqldump -u root -p --no-data clinica_diabetes > estructura.sql

# Respaldo solo datos
mysqldump -u root -p --no-create-info clinica_diabetes > datos.sql
```

### Restauración

```bash
mysql -u root -p clinica_diabetes < backup_20260128.sql
```

### Optimización

```sql
-- Analizar tablas
ANALYZE TABLE pacientes, visitas, analisis_glucosa;

-- Optimizar tablas
OPTIMIZE TABLE pacientes, visitas, analisis_glucosa;

-- Ver tamaño de tablas
SELECT 
    table_name AS 'Tabla',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Tamaño (MB)'
FROM information_schema.TABLES
WHERE table_schema = 'clinica_diabetes'
ORDER BY (data_length + index_length) DESC;
```

## 📖 Documentación Adicional

- **database_diagram.md**: Diagrama entidad-relación completo
- **database_documentation.md**: Documentación detallada de cada tabla
- **sample_queries.sql**: 26 consultas de ejemplo comentadas

## 🆘 Solución de Problemas

### Error: "Table already exists"

```sql
-- Eliminar base de datos existente (¡CUIDADO! Esto borra todos los datos)
DROP DATABASE IF EXISTS clinica_diabetes;
-- Luego ejecutar database_schema.sql nuevamente
```

### Error: "Cannot add foreign key constraint"

Asegúrate de ejecutar los scripts en orden:
1. Primero `database_schema.sql`
2. Luego `rangos_referencia.sql`

### Verificar Instalación

```sql
USE clinica_diabetes;

-- Ver todas las tablas
SHOW TABLES;

-- Verificar triggers
SHOW TRIGGERS;

-- Verificar vistas
SHOW FULL TABLES WHERE TABLE_TYPE LIKE 'VIEW';

-- Contar registros en rangos de referencia
SELECT COUNT(*) FROM rangos_referencia;
SELECT COUNT(*) FROM interpretaciones;
SELECT COUNT(*) FROM medicamentos_catalogo;
```

## 📞 Soporte

Para más información, consulta:
- `database_documentation.md` - Documentación completa
- `sample_queries.sql` - Ejemplos de consultas

## 📝 Licencia

Este proyecto fue desarrollado específicamente para Clínica InvestLab.

---

**Versión**: 1.0  
**Fecha**: Enero 2026  
**Motor**: MySQL 8.0+
