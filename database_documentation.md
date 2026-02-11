# Documentación de Base de Datos - Clínica de Diabetes

## Información General

- **Nombre de la Base de Datos**: `clinica_diabetes`
- **Motor**: MySQL 8.0+
- **Charset**: utf8mb4
- **Collation**: utf8mb4_unicode_ci
- **Total de Tablas**: 35 tablas principales + 3 vistas

## Índice de Contenidos

1. [Módulo de Usuarios](#módulo-de-usuarios)
2. [Módulo de Pacientes](#módulo-de-pacientes)
3. [Módulo de Visitas](#módulo-de-visitas)
4. [Módulo de Análisis Clínicos](#módulo-de-análisis-clínicos)
5. [Módulo de Medicamentos](#módulo-de-medicamentos)
6. [Módulo de Estilo de Vida](#módulo-de-estilo-de-vida)
7. [Módulo de Complicaciones](#módulo-de-complicaciones)
8. [Módulo de Control y Seguimiento](#módulo-de-control-y-seguimiento)
9. [Sistema de Interpretación Automática](#sistema-de-interpretación-automática)
10. [Vistas y Consultas Útiles](#vistas-y-consultas-útiles)

---

## Módulo de Usuarios

### Tabla: `roles`
Catálogo de roles del sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_rol | INT PK | Identificador único |
| nombre_rol | VARCHAR(50) | Nombre del rol (Admin, Doctor) |
| descripcion | TEXT | Descripción del rol |

**Roles disponibles:**
- **Administrador**: Acceso completo al sistema
- **Doctor**: Acceso a pacientes y análisis

### Tabla: `usuarios`
Doctores y administradores del sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_usuario | INT PK | Identificador único |
| id_rol | INT FK | Rol del usuario |
| nombre | VARCHAR(100) | Nombre |
| apellido_paterno | VARCHAR(100) | Apellido paterno |
| email | VARCHAR(150) | Email único |
| password_hash | VARCHAR(255) | Contraseña hasheada (bcrypt) |
| cedula_profesional | VARCHAR(50) | Cédula profesional |
| especialidad | VARCHAR(100) | Especialidad médica |
| activo | BOOLEAN | Usuario activo/inactivo |

**Índices:**
- `idx_email`: Búsqueda rápida por email
- `idx_activo`: Filtrado de usuarios activos

---

## Módulo de Pacientes

### Tabla: `pacientes`
Información demográfica y personal de pacientes.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_paciente | INT PK | Identificador único |
| numero_expediente | VARCHAR(50) UNIQUE | Número de expediente |
| nombre | VARCHAR(100) | Nombre |
| apellido_paterno | VARCHAR(100) | Apellido paterno |
| fecha_nacimiento | DATE | Fecha de nacimiento |
| edad | INT | Edad calculada automáticamente |
| sexo | ENUM('M','F') | Sexo |
| curp | VARCHAR(18) | CURP único |
| telefono | VARCHAR(20) | Teléfono |
| email | VARCHAR(150) | Email |
| direccion | TEXT | Dirección completa |
| tipo_sangre | ENUM | Tipo de sangre |
| alergias | TEXT | Alergias conocidas |
| activo | BOOLEAN | Paciente activo/inactivo |

**Características especiales:**
- La edad se calcula automáticamente mediante trigger
- Soft delete mediante campo `activo`

### Tabla: `contactos_emergencia`
Contactos de emergencia del paciente.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_contacto | INT PK | Identificador único |
| id_paciente | INT FK | Paciente relacionado |
| nombre_completo | VARCHAR(200) | Nombre del contacto |
| parentesco | VARCHAR(50) | Relación con el paciente |
| telefono | VARCHAR(20) | Teléfono principal |
| es_principal | BOOLEAN | Contacto principal |

---

## Módulo de Visitas

### Tabla: `visitas`
Registro de cada consulta médica.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_visita | INT PK | Identificador único |
| id_paciente | INT FK | Paciente |
| id_doctor | INT FK | Doctor que atiende |
| fecha_visita | DATETIME | Fecha y hora de la visita |
| tipo_visita | ENUM | Primera Vez, Seguimiento, Urgencia, Control |
| motivo_consulta | TEXT | Motivo de la consulta |
| diagnostico | TEXT | Diagnóstico |
| plan_tratamiento | TEXT | Plan de tratamiento |
| proxima_cita | DATE | Fecha de próxima cita |
| estatus | ENUM | Programada, En Curso, Completada, Cancelada |

### Tabla: `datos_clinicos`
Signos vitales tomados en cada visita.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_dato_clinico | INT PK | Identificador único |
| id_visita | INT FK | Visita relacionada |
| peso | DECIMAL(5,2) | Peso en kg |
| talla | DECIMAL(5,2) | Talla en cm |
| imc | DECIMAL(5,2) | IMC calculado automáticamente |
| circunferencia_abdominal | DECIMAL(5,2) | En cm |
| presion_arterial_sistolica | INT | PA sistólica en mmHg |
| presion_arterial_diastolica | INT | PA diastólica en mmHg |
| frecuencia_cardiaca | INT | FC en lpm |
| temperatura | DECIMAL(4,2) | Temperatura en °C |
| glucosa_capilar | DECIMAL(5,2) | Glucosa capilar en mg/dL |

**Características especiales:**
- El IMC se calcula automáticamente mediante trigger: `IMC = peso / (talla/100)²`

---

## Módulo de Análisis Clínicos

### Tabla: `analisis_glucosa`
Control glucémico del paciente.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_analisis | INT PK | Identificador único |
| id_visita | INT FK | Visita relacionada |
| fecha_analisis | DATE | Fecha del análisis |
| glucosa_ayunas | DECIMAL(5,2) | Glucosa en ayunas (mg/dL) |
| glucosa_postprandial_2h | DECIMAL(5,2) | Glucosa 2h postprandial |
| hemoglobina_glicosilada | DECIMAL(4,2) | HbA1c (%) |
| interpretacion_glucosa_ayunas | ENUM | Normal, Precaución, Alerta |
| interpretacion_hba1c | ENUM | Normal, Precaución, Alerta |

**Rangos de interpretación:**

**Glucosa en ayunas:**
- 🟢 Normal: 70-100 mg/dL
- 🟡 Precaución: 100-126 mg/dL (Prediabetes)
- 🔴 Alerta: ≥126 mg/dL (Diabetes) o <70 mg/dL (Hipoglucemia)

**HbA1c:**
- 🟢 Normal: <5.7%
- 🟡 Precaución: 5.7-6.4% (Prediabetes)
- 🔴 Alerta: ≥6.5% (Diabetes)

### Tabla: `analisis_perfil_renal`
Función renal del paciente.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| creatinina_serica | DECIMAL(5,2) | Creatinina (mg/dL) |
| tasa_filtracion_glomerular | DECIMAL(5,2) | TFG (mL/min/1.73m²) |
| urea | DECIMAL(5,2) | Urea (mg/dL) |
| microalbuminuria | DECIMAL(6,2) | Microalbuminuria (mg/24h) |
| relacion_albumina_creatinina | DECIMAL(6,2) | ACR (mg/g) |
| interpretacion_tfg | ENUM | Normal, Precaución, Alerta |

**Rangos TFG:**
- 🟢 Normal: ≥90 mL/min/1.73m²
- 🟡 Precaución: 60-89 (ERC Estadio 2)
- 🔴 Alerta: <60 (ERC Estadio 3+)

### Tabla: `analisis_perfil_lipidico`
Perfil de lípidos.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| colesterol_total | DECIMAL(5,2) | Colesterol total (mg/dL) |
| ldl | DECIMAL(5,2) | LDL colesterol (mg/dL) |
| hdl | DECIMAL(5,2) | HDL colesterol (mg/dL) |
| trigliceridos | DECIMAL(6,2) | Triglicéridos (mg/dL) |

**Rangos:**
- **Colesterol total**: <200 (deseable), 200-239 (límite alto), ≥240 (alto)
- **LDL**: <100 (óptimo), 100-159 (límite alto), ≥160 (alto)
- **HDL**: ≥40 hombres, ≥50 mujeres (deseable)
- **Triglicéridos**: <150 (normal), 150-199 (límite alto), ≥200 (alto)

### Otras Tablas de Análisis
- `analisis_electrolitos`: Na, K, Cl, HCO3, Ca, P, Mg
- `analisis_hepaticos`: ALT, AST, fosfatasa alcalina, bilirrubinas, albúmina
- `analisis_cardiovascular`: Troponina, BNP, NT-proBNP, homocisteína
- `analisis_otros`: Vitamina D, TSH, T4, hemograma, cetonas, péptido C

---

## Módulo de Medicamentos

### Tabla: `medicamentos_catalogo`
Catálogo de medicamentos disponibles.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_medicamento | INT PK | Identificador único |
| nombre_generico | VARCHAR(200) | Nombre genérico |
| nombre_comercial | VARCHAR(200) | Nombre comercial |
| categoria | ENUM | Tipo de medicamento |
| presentacion | VARCHAR(100) | Presentación (mg, mL, etc.) |

**Categorías:**
- Antidiabético Oral
- Insulina
- Antihipertensivo
- Estatina
- Antiagregante
- Otro

### Tabla: `tratamientos`
Medicamentos prescritos al paciente.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_tratamiento | INT PK | Identificador único |
| id_paciente | INT FK | Paciente |
| id_medicamento | INT FK | Medicamento del catálogo |
| dosis | VARCHAR(100) | Dosis prescrita |
| frecuencia | VARCHAR(100) | Frecuencia de administración |
| fecha_inicio | DATE | Fecha de inicio |
| fecha_fin | DATE | Fecha de fin (si aplica) |
| activo | BOOLEAN | Tratamiento activo |

---

## Módulo de Control y Seguimiento

### Tabla: `glucometrias`
Bitácora diaria de glucosa del paciente.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| id_glucometria | INT PK | Identificador único |
| id_paciente | INT FK | Paciente |
| fecha_hora | DATETIME | Fecha y hora de la medición |
| glucosa | DECIMAL(5,2) | Glucosa en mg/dL |
| momento | ENUM | Ayunas, Preprandial, Postprandial, etc. |

**Momentos de medición:**
- Ayunas
- Preprandial
- Postprandial
- Antes de dormir
- Madrugada
- Otro

### Tabla: `hipoglucemias`
Registro de eventos de hipoglucemia.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| fecha_hora | DATETIME | Fecha y hora del evento |
| glucosa | DECIMAL(5,2) | Nivel de glucosa |
| sintomas | TEXT | Síntomas presentados |
| severidad | ENUM | Leve, Moderada, Severa |
| tratamiento_aplicado | TEXT | Tratamiento dado |

---

## Sistema de Interpretación Automática

### Tabla: `rangos_referencia`
Define los valores normales para cada parámetro clínico.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| parametro | VARCHAR(100) | Nombre del parámetro |
| unidad | VARCHAR(20) | Unidad de medida |
| valor_minimo_normal | DECIMAL(10,2) | Límite inferior normal |
| valor_maximo_normal | DECIMAL(10,2) | Límite superior normal |
| valor_minimo_precaucion | DECIMAL(10,2) | Límite inferior precaución |
| valor_maximo_precaucion | DECIMAL(10,2) | Límite superior precaución |

### Tabla: `interpretaciones`
Reglas de interpretación automática.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| parametro | VARCHAR(100) | Parámetro a interpretar |
| condicion | VARCHAR(50) | Tipo de condición |
| valor_referencia | DECIMAL(10,2) | Valor de referencia |
| nivel_alerta | ENUM | Normal, Precaución, Alerta |
| mensaje | TEXT | Mensaje de interpretación |
| recomendacion | TEXT | Recomendación clínica |

**Condiciones disponibles:**
- `menor_que`: Valor < referencia
- `mayor_que`: Valor > referencia
- `mayor_igual`: Valor ≥ referencia
- `entre`: Valor entre dos referencias

### Sistema de Semáforos

El sistema utiliza tres niveles de alerta:

| Color | Nivel | Significado | Acción |
|-------|-------|-------------|--------|
| 🟢 | Normal | Valores dentro de rango normal | Mantener control |
| 🟡 | Precaución | Valores en zona de precaución | Monitoreo cercano |
| 🔴 | Alerta | Valores fuera de rango | Atención inmediata |

---

## Vistas y Consultas Útiles

### Vista: `vista_pacientes_activos`
Lista de pacientes activos con su última visita.

```sql
SELECT * FROM vista_pacientes_activos;
```

**Columnas:**
- numero_expediente
- nombre_completo
- edad, sexo
- telefono, celular
- ultima_visita
- total_visitas

### Vista: `vista_analisis_recientes`
Análisis clínicos recientes con interpretación.

```sql
SELECT * FROM vista_analisis_recientes
WHERE id_paciente = ?
ORDER BY fecha_analisis DESC;
```

### Vista: `vista_tratamientos_activos`
Tratamientos activos de todos los pacientes.

```sql
SELECT * FROM vista_tratamientos_activos
WHERE id_paciente = ?;
```

---

## Instalación

### 1. Crear la base de datos

```bash
mysql -u root -p < database_schema.sql
```

### 2. Cargar datos iniciales

```bash
mysql -u root -p clinica_diabetes < rangos_referencia.sql
```

### 3. Crear usuario administrador

```sql
-- Hashear la contraseña en tu aplicación primero
INSERT INTO usuarios (id_rol, nombre, apellido_paterno, email, password_hash, activo)
VALUES (1, 'Admin', 'Sistema', 'admin@clinica.com', 'HASH_AQUI', TRUE);
```

---

## Mantenimiento

### Respaldos Recomendados

```bash
# Respaldo completo
mysqldump -u root -p clinica_diabetes > backup_$(date +%Y%m%d).sql

# Respaldo solo estructura
mysqldump -u root -p --no-data clinica_diabetes > estructura.sql

# Respaldo solo datos
mysqldump -u root -p --no-create-info clinica_diabetes > datos.sql
```

### Optimización

```sql
-- Analizar tablas
ANALYZE TABLE pacientes, visitas, analisis_glucosa;

-- Optimizar tablas
OPTIMIZE TABLE pacientes, visitas, analisis_glucosa;
```

---

## Notas Importantes

1. **Seguridad**: Las contraseñas DEBEN ser hasheadas con bcrypt antes de almacenar
2. **Triggers**: Los triggers calculan automáticamente IMC y edad
3. **Soft Deletes**: Usar campo `activo` en lugar de DELETE
4. **Auditoría**: Todos los registros tienen `created_at`, `updated_at`, `created_by`
5. **Integridad**: Las claves foráneas garantizan integridad referencial
6. **Índices**: Optimizan búsquedas frecuentes

---

## Soporte para Aplicación Web

### Endpoints Sugeridos

- `GET /api/pacientes` - Lista de pacientes
- `POST /api/pacientes` - Crear paciente
- `GET /api/pacientes/{id}` - Detalle de paciente
- `GET /api/pacientes/{id}/visitas` - Visitas del paciente
- `POST /api/visitas` - Registrar visita
- `POST /api/analisis/glucosa` - Registrar análisis
- `GET /api/analisis/{id}/interpretacion` - Obtener interpretación
- `GET /api/graficas/glucosa/{id}` - Datos para gráfica de glucosa
- `GET /api/tratamientos/{id}` - Tratamientos del paciente

### Gráficas Recomendadas

1. **Glucosa en el tiempo**: Line chart con glucosa_ayunas vs fecha
2. **HbA1c trimestral**: Line chart con HbA1c vs fecha
3. **Peso e IMC**: Dual axis chart
4. **Presión arterial**: Line chart con sistólica y diastólica
5. **Perfil lipídico**: Bar chart con colesterol, LDL, HDL, triglicéridos
