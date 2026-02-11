# Guía de Instalación - Base de Datos Clínica InvestLab

## 🚀 Instalación Automática (Recomendado)

### Opción 1: Usar el Script Automatizado

1. **Abre el Explorador de Windows** y ve a:
   ```
   C:\Users\medin\Desktop\Clinica InvestLab
   ```

2. **Haz doble clic** en el archivo:
   ```
   instalar_base_datos.bat
   ```

3. **Espera** a que el script termine. Verás mensajes indicando el progreso.

4. **¡Listo!** La base de datos estará instalada.

---

## 🖱️ Instalación Manual (Alternativa)

### Opción 2: Usar phpMyAdmin

1. **Abre tu navegador** y ve a:
   ```
   http://localhost/phpmyadmin
   ```

2. **Haz clic** en la pestaña **"SQL"** en la parte superior

3. **Abre el archivo** `database_schema.sql` con el Bloc de notas:
   - Haz clic derecho en `database_schema.sql`
   - Selecciona "Abrir con" → "Bloc de notas"
   - Presiona `Ctrl + A` para seleccionar todo
   - Presiona `Ctrl + C` para copiar

4. **Pega el contenido** en phpMyAdmin:
   - Pega el código en el área de texto grande
   - Haz clic en el botón **"Continuar"** o **"Go"**

5. **Repite el proceso** con el archivo `rangos_referencia.sql`

6. **¡Listo!** La base de datos estará instalada.

---

## ✅ Verificar la Instalación

1. Ve a **phpMyAdmin**: http://localhost/phpmyadmin

2. En el panel izquierdo, busca la base de datos **"clinica_diabetes"**

3. Haz clic en ella y verás **35 tablas**:
   - analisis_cardiovascular
   - analisis_electrolitos
   - analisis_glucosa
   - analisis_hepaticos
   - analisis_otros
   - analisis_perfil_lipidico
   - analisis_perfil_renal
   - anexos
   - antecedentes_familiares
   - ajustes_tratamiento
   - complicaciones_macrovasculares
   - complicaciones_microvasculares
   - contactos_emergencia
   - datos_clinicos
   - educacion_diabetes
   - estilo_vida
   - glucometrias
   - hiperglucemias
   - hipoglucemias
   - interpretaciones
   - medicamentos_catalogo
   - notas_consulta
   - pacientes
   - rangos_referencia
   - roles
   - salud_mental
   - sesiones
   - tratamientos
   - usuarios
   - visitas
   - (y 3 vistas)

---

## 🔧 Solución de Problemas

### Error: "MySQL no encontrado"

Si el script dice que no encuentra MySQL, edita el archivo `instalar_base_datos.bat`:

1. Haz clic derecho en `instalar_base_datos.bat`
2. Selecciona "Editar"
3. Busca la línea:
   ```batch
   set MYSQL_PATH=C:\xampp\mysql\bin
   ```
4. Cambia la ruta según donde instalaste XAMPP. Rutas comunes:
   - `C:\xampp\mysql\bin`
   - `D:\xampp\mysql\bin`
   - `C:\Program Files\xampp\mysql\bin`

### Error: "Access denied"

Si te pide contraseña, edita el archivo `instalar_base_datos.bat`:

1. Busca la línea:
   ```batch
   set DB_PASS=
   ```
2. Cámbiala por:
   ```batch
   set DB_PASS=tu_contraseña
   ```

### XAMPP no está corriendo

1. Abre el **Panel de Control de XAMPP**
2. Haz clic en **"Start"** junto a **MySQL**
3. Espera a que aparezca en verde
4. Ejecuta el script nuevamente

---

## 📊 Próximos Pasos

Una vez instalada la base de datos:

1. **Accede a phpMyAdmin**: http://localhost/phpmyadmin
2. **Explora las tablas** creadas
3. **Revisa los datos iniciales** en:
   - `roles` (2 roles)
   - `rangos_referencia` (50+ rangos)
   - `interpretaciones` (60+ reglas)
   - `medicamentos_catalogo` (20 medicamentos)

4. **Crea tu primer usuario administrador**:
   - Ve a la tabla `usuarios`
   - Haz clic en "Insertar"
   - Llena los campos (recuerda hashear la contraseña en tu aplicación)

---

## 🎯 Credenciales de Acceso

**phpMyAdmin:**
- URL: http://localhost/phpmyadmin
- Usuario: `root`
- Contraseña: (vacía, solo presiona Enter)

**Base de Datos:**
- Nombre: `clinica_diabetes`
- Charset: `utf8mb4`
- Collation: `utf8mb4_unicode_ci`

---

## 📞 ¿Necesitas Ayuda?

Si tienes problemas con la instalación:

1. Verifica que XAMPP esté corriendo
2. Verifica que MySQL esté activo (luz verde en XAMPP)
3. Intenta la instalación manual con phpMyAdmin
4. Revisa los mensajes de error del script

---

¡La base de datos está lista para usarse! 🎉
