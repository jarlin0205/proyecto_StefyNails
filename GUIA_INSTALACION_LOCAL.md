# 🚀 Guía de Instalación Local - StefyNails

Esta guía te ayudará a configurar y ejecutar el proyecto **StefyNails** en tu entorno local de desarrollo.

---

## 📋 Requisitos Previos

Antes de comenzar, asegúrate de tener instalados los siguientes programas:

- ✅ **PHP 8.2 o superior** - [Descargar PHP](https://www.php.net/downloads)
- ✅ **Composer** - [Descargar Composer](https://getcomposer.org/download/)
- ✅ **Node.js y NPM** (versión 16 o superior) - [Descargar Node.js](https://nodejs.org/)
- ✅ **MySQL o MariaDB** - [Descargar MySQL](https://dev.mysql.com/downloads/)
- ✅ **Git** - [Descargar Git](https://git-scm.com/downloads)

### Verificar Instalaciones

Puedes verificar que todo esté instalado correctamente ejecutando estos comandos:

```bash
php -v
composer -v
node -v
npm -v
mysql --version
```

---

## 🔧 Pasos de Instalación

### **1. Instalar Dependencias de PHP**

Abre una terminal en el directorio del proyecto y ejecuta:

```bash
composer install
```

Este comando instalará todas las dependencias de Laravel especificadas en `composer.json`.

---

### **2. Configurar Variables de Entorno**

Si no existe el archivo `.env`, créalo copiando el archivo de ejemplo:

```bash
copy .env.example .env
```

Luego, genera la clave de aplicación de Laravel:

```bash
php artisan key:generate
```

---

### **3. Configurar Base de Datos**

1. **Crea una base de datos** en MySQL para el proyecto (ejemplo: `stefynails_db`)

2. **Edita el archivo `.env`** y configura los datos de tu base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stefynails_db
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

> **Nota:** Reemplaza `tu_usuario` y `tu_contraseña` con tus credenciales de MySQL.

---

### **4. Ejecutar Migraciones**

Ejecuta las migraciones para crear las tablas en la base de datos:

```bash
php artisan migrate
```

Si también deseas poblar la base de datos con datos de prueba (seeders):

```bash
php artisan db:seed
```

---

### **5. Instalar Dependencias de Node.js**

Instala las dependencias de frontend:

```bash
npm install
```

---

## ▶️ Ejecutar el Proyecto

### **Opción 1: Modo Desarrollo Completo (Recomendado)**

El proyecto incluye un script que ejecuta todos los servicios necesarios en paralelo:

```bash
composer dev
```

Este comando iniciará automáticamente:
- 🖥️ **Servidor Laravel** en `http://localhost:8000`
- 📦 **Queue Worker** (procesa trabajos en cola)
- ⚡ **Vite Dev Server** (compilación en tiempo real del frontend)

---

### **Opción 2: Ejecutar Servicios por Separado**

Si prefieres tener más control, puedes ejecutar cada servicio en terminales separadas:

#### **Terminal 1 - Servidor Laravel:**
```bash
php artisan serve
```

#### **Terminal 2 - Vite (Frontend):**
```bash
npm run dev
```

#### **Terminal 3 - Queue Worker (Opcional):**
```bash
php artisan queue:listen --tries=1
```

---

## 🌐 Acceder a la Aplicación

Una vez que los servicios estén corriendo, abre tu navegador web y visita:

```
http://localhost:8000
```

---

## 🔄 Bot de WhatsApp (Opcional)

Si necesitas ejecutar el bot de WhatsApp, utiliza el archivo batch incluido:

```bash
conectar_bot.bat
```

> **Nota:** Asegúrate de tener configuradas las credenciales necesarias para el bot.

---

## 🛠️ Comandos Útiles

### Limpiar caché de configuración:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Ejecutar tests:
```bash
composer test
```
o
```bash
php artisan test
```

### Compilar assets para producción:
```bash
npm run build
```

### Ver logs en tiempo real:
```bash
php artisan pail
```

---

## ❓ Solución de Problemas Comunes

### Error: "No application encryption key has been specified"
**Solución:**
```bash
php artisan key:generate
```

### Error de permisos en storage o bootstrap/cache
**Solución (Linux/Mac):**
```bash
chmod -R 775 storage bootstrap/cache
```

**Solución (Windows):** Asegúrate de que tu usuario tenga permisos de escritura en esas carpetas.

### Error: "SQLSTATE[HY000] [1045] Access denied"
**Solución:** Verifica que las credenciales en `.env` sean correctas.

### El frontend no se actualiza
**Solución:**
```bash
npm run dev
```
Y asegúrate de que Vite esté corriendo.

---

## 📚 Documentación Adicional

- [Documentación de Laravel](https://laravel.com/docs)
- [Documentación de Vite](https://vitejs.dev/)
- [Documentación de Tailwind CSS](https://tailwindcss.com/docs)

---

## 📝 Notas Importantes

- Siempre asegúrate de tener el servidor de base de datos (MySQL) corriendo antes de ejecutar el proyecto.
- Para desarrollo, se recomienda usar `composer dev` ya que ejecuta todos los servicios necesarios.
- No olvides crear la base de datos antes de ejecutar las migraciones.
- El archivo `.env` contiene información sensible y NO debe ser compartido ni subido al repositorio.

---

**¡Listo! Ahora puedes desarrollar en StefyNails localmente** 🎉
