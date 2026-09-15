---
title: "Manual Técnico y de Despliegue - Sistema Botica"
author: "Departamento de IT"
date: "2026"
---

# Manual Técnico y de Despliegue: Sistema Botica

![Tech Stack](file:///C:/Users/victo/.gemini/antigravity/brain/5549e511-1703-421c-a361-9f975aa7d9b4/botica_tech_stack_1779018776644.png)

## 1. Tecnologías de Desarrollo

El **Sistema Botica** ha sido construido utilizando una arquitectura robusta, escalable y mantenible basada en el patrón de diseño **MVC (Modelo-Vista-Controlador)** puro, asegurando un alto rendimiento sin la carga de frameworks pesados de backend.

### Backend & Lógica de Negocio
* **Lenguaje:** PHP 8.x (Nativo)
* **Arquitectura:** MVC Puro (App, Controllers, Models, Views)
* **Gestión de Base de Datos:** PDO (PHP Data Objects) para mayor seguridad.
* **Autenticación:** Sistema de sesiones nativo de PHP con encriptación de contraseñas (`Bcrypt`).

### Base de Datos
* **Motor:** MySQL 8.x / MariaDB
* **Estructura:** Base de datos relacional (`botica_db`) optimizada con índices y claves foráneas.

### Frontend & Interfaz de Usuario
* **Estructura y Estilos:** HTML5, Vanilla CSS y **Bootstrap 5**.
* **Iconografía:** Bootstrap Icons.
* **Interactividad:** JavaScript (Vanilla JS).
* **Gráficos y Dashboards:** Chart.js para visualización de reportes y estadísticas.

---

## 2. Funcionalidades del Sistema

El ERP de la botica es un sistema integral que abarca desde la venta hasta la auditoría administrativa:

1. **Gestión de Usuarios y Roles:** Control de acceso estricto con roles (Administrador, Farmacéutico, Cajero, Almacenero), y perfiles personalizables.
2. **Catálogo Maestro de Productos:** Control detallado de medicamentos, unidades de medida, fraccionamiento, categorías, laboratorios y advertencias de receta médica.
3. **Gestión de Inventario (Kardex):** Control de ingresos, egresos, mermas, lotes y fechas de vencimiento (estrategia FEFO).
4. **Punto de Venta (POS) y Caja:** Interfaz rápida de ventas, control de caja chica (apertura, arqueo, cierre), emisión de tickets, boletas y facturas.
5. **Módulo de Compras:** Registro de proveedores, órdenes de compra y actualización automática de stock y costos.
6. **Módulo de Clientes:** Base de datos de clientes y sistema de fidelización por puntos.
7. **Notificaciones y Alertas Sanitarias:** Alertas automáticas para bajo stock y lotes próximos a vencer.
8. **Reportes y Estadísticas:** Gráficos de ventas, utilidades, y exportación de información administrativa.
9. **Configuración General:** Ajustes del sistema y **Facturación Electrónica (SUNAT UBL 2.1)**.
10. **Auditoría y Seguridad:** Registro inmutable de ingresos, salidas y operaciones críticas (anulaciones, ediciones).

---

## 3. Guía de Instalación Local (Paso a Paso)

![Local Installation](file:///C:/Users/victo/.gemini/antigravity/brain/5549e511-1703-421c-a361-9f975aa7d9b4/botica_local_install_1779018936052.png)

Siga estas instrucciones para montar el sistema en una computadora nueva:

### Paso 1: Descargar el Entorno de Desarrollo
Para que la computadora procese PHP y MySQL, necesitas instalar un servidor local. Se recomienda encarecidamente **Laragon** o en su defecto **XAMPP**.
1. Descarga **Laragon Full** desde [laragon.org/download](https://laragon.org/download/).
2. Ejecuta el instalador y sigue las instrucciones por defecto (Siguiente > Siguiente > Instalar).

### Paso 2: Ubicar los Archivos del Proyecto
1. Ve a la carpeta donde se instaló Laragon (por lo general `C:\laragon\www\`). Si usas XAMPP, será `C:\xampp\htdocs\`.
2. Pega toda la carpeta del proyecto en este directorio (asegúrate de que la carpeta se llame `sistema-botica`).

### Paso 3: Configurar la Base de Datos
1. Abre la aplicación de **Laragon** y presiona el botón **Iniciar Todo** (Start All).
2. Abre tu navegador y escribe `http://localhost/phpmyadmin` o haz clic en el botón "Base de datos" en Laragon para abrir **HeidiSQL**.
3. Crea una nueva base de datos llamada `botica_db` con el cotejamiento `utf8mb4_unicode_ci`.
4. Selecciona la base de datos recién creada, busca la opción **Importar**, selecciona el archivo `bk_basededatos.sql` (ubicado en la raíz del proyecto) y ejecuta la importación.

### Paso 4: Configurar Conexión
1. Con un editor de código (como VS Code o el Bloc de notas), abre el archivo `app/config/database.php` del proyecto.
2. Verifica que las credenciales sean correctas:
   - `host`: `localhost`
   - `db_name`: `botica_db`
   - `username`: `root`
   - `password`: ` ` (vacío en Laragon/XAMPP por defecto).

### Paso 5: Puesta en Marcha
Si usas Laragon, abre tu navegador web y dirígete a `http://sistema-botica.test`. Si usas XAMPP, entra a `http://localhost/sistema-botica/public/`. 
Ingresa con el usuario **admin** y la contraseña establecida por defecto para empezar a operar.

---

## 4. Hosting Web Sugerido

Para un entorno de producción, recomendamos contratar **Hostinger** (Plan Premium o Business Web Hosting). 

**¿Por qué Hostinger?**
- **Panel de Control:** Su hPanel es increíblemente intuitivo, más moderno y rápido que el cPanel tradicional.
- **Rendimiento:** Discos NVMe SSD y tecnología LiteSpeed Web Server, ideal para PHP.
- **Costo / Beneficio:** Certificado SSL gratuito, dominio gratis el primer año, y copias de seguridad automáticas a un precio muy accesible.
- *(Alternativas válidas: Banahosting o Namecheap).*

---

## 5. Despliegue en Servidor (Paso a Paso)

![Cloud Deployment](file:///C:/Users/victo/.gemini/antigravity/brain/5549e511-1703-421c-a361-9f975aa7d9b4/botica_cloud_deploy_1779018947378.png)

Una vez que tengas tu cuenta y dominio en Hostinger, sigue estos pasos:

### Paso 1: Exportar tu Base de Datos Local
1. En tu servidor local (HeidiSQL / phpMyAdmin), selecciona `botica_db`.
2. Ve a la pestaña de "Exportar" y descarga el archivo `.sql` de tu estructura y datos completos.

### Paso 2: Crear Base de Datos en Hostinger
1. Entra a tu panel de Hostinger (hPanel) y ve a la sección **Bases de Datos > Gestión de bases de datos**.
2. Crea una nueva base de datos. Asigna un nombre (ej. `u123_botica`), un usuario (`u123_admin`) y una contraseña segura.
3. Guarda estos tres datos, los necesitarás en breve.
4. Haz clic en "Entrar a phpMyAdmin" dentro del mismo panel e importa tu archivo `.sql` exportado en el Paso 1.

### Paso 3: Subir los Archivos del Proyecto
1. En tu computadora local, comprime el contenido del proyecto en un archivo `.zip`.
2. Ve a Hostinger, a la sección **Archivos > Administrador de Archivos**.
3. Ingresa a la carpeta `public_html`.
4. Sube tu archivo `.zip` y extráelo allí mismo.

### Paso 4: Ajustar Rutas y Seguridad
Dado que es una aplicación MVC, es crucial proteger el código fuente.
- Asegúrate de que el archivo `.htaccess` ubicado en la raíz redirija todo el tráfico a la carpeta `public/` (el sistema ya incluye esto).
- Opcionalmente, puedes configurar el document root de tu dominio directamente hacia la subcarpeta `public/` desde la configuración del hosting.

### Paso 5: Conectar Producción
1. En el Administrador de Archivos, edita el archivo `app/config/database.php`.
2. Reemplaza los datos locales por los del hosting:
   - `host`: `localhost` (suele mantenerse así en Hostinger)
   - `db_name`: `TuNuevoNombreBD` (ej. `u123_botica`)
   - `username`: `TuNuevoUsuario` (ej. `u123_admin`)
   - `password`: `TuNuevaContraseña`
3. Guarda el archivo. ¡Tu sistema de botica ya está vivo y funcionando en la web!
