# Guía de Configuración y Despliegue - Stefy Nails

Esta guía detalla los pasos para configurar tu repositorio local y el servidor en la instancia de AWS EC2.

## 0. Configuración del Repositorio Local
Si ya tienes los archivos en tu maquina pero no están vinculados a GitHub, sigue estos pasos en tu terminal local (dentro de la carpeta del proyecto):

1. **Inicializar Git**:
   ```bash
   git init
   ```

2. **Vincular al Repositorio Remoto**:
   ```bash
   git remote add origin https://github.com/jarlin0205/proyecto_StefyNails.git
   ```

3. **Configurar tu Identidad** (Si Git te pide quién eres):
   ```bash
   git config --global user.email "tu@email.com"
   git config --global user.name "Tu Nombre"
   ```

4. **Solución a error de carpeta anidada** (Si sale error en `proyecto_StefyNails/`):
   Si al hacer `git add .` recibes un error sobre esta carpeta, es porque tiene su propio historial oculto. Bórralo para que el repositorio principal lo reconozca:
   ```bash
   # En Windows (Powershell)
   Remove-Item -Recurse -Force proyecto_StefyNails\.git
   ```

5. **Subir tus cambios por primera vez**:
   ```bash
   git add .
   git commit -m "Primer commit: Estructura del proyecto"
   git push -u origin master
   ```

## 1. Acceso al Servidor
Para conectarte al servidor desde tu terminal local:
```bash
ssh -i "claves_acceso_ser.pem" ubuntu@ec2-18-222-97-39.us-east-2.compute.amazonaws.com
```

## 2. Preparación del Entorno (Ubuntu 22.04+)
Actualiza los paquetes e instala las dependencias necesarias:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php-fpm php-mysql php-xml php-mbstring php-curl php-zip unzip git nginx mysql-server
```

### Instalar Composer:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

### Instalar Node.js y NPM (para el Bot):
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

## 3. Configuración del Repositorio
Clona el proyecto en el directorio web:
```bash
cd /var/www
sudo git clone https://github.com/jarlin0205/proyecto_StefyNails.git stefynails
sudo chown -R ubuntu:www-data stefynails
cd stefynails
```

## 4. Configuración de Laravel
Instala las dependencias y configura el entorno:
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
7. **Instalar dependencias de Frontend y Compilar Assets** (Crucial para evitar error de Vite):
   ```bash
   npm install
   npm run build
   ```

8. **Optimización de Laravel**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
```

### Configurar `.env`:
Edita el archivo `.env` con los datos de tu base de datos (MySQL) y la URL.
```bash
nano .env
# Ajustar obligatoriamente:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# APP_URL=http://tu-dominio-o-ip
# DB_DATABASE=stefynails
# DB_USERNAME=stefy_user
# DB_PASSWORD=tu_password_seguro
```

## 5. Configuración de la Base de Datos y Permisos Finales
Es mucho más seguro y compatible crear un usuario específico en lugar de usar `root`.

1. **Entrar a MySQL**:
   ```bash
   sudo mysql -u root
   ```

2. **Crear base de datos y usuario** (Copia y pega cada línea):
   ```sql
   UPDATE DATABASE stefynails;
   ALTER USER 'stefyuser'@'localhost' IDENTIFIED BY 'admin123';
   GRANT ALL PRIVILEGES ON stefynails.* TO 'stefyuser'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

3. **Correr migraciones**:
   ```bash
   # Primero damos permisos temporales para que no falle el log
   sudo chmod -R 777 storage bootstrap/cache
   
   # Corremos la migración
   php artisan migrate --force

   # RE-APLICAR PERMISOS CORRECTOS (Para Nginx)
   sudo chown -R www-data:www-data storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

4. **Crear usuario Administrador**:
   Tienes dos opciones para crear tu cuenta de acceso:
   
   **Opción A (Datos por defecto):**
   ```bash
   # Crea un usuario: admin@stefynails.com / password
   php artisan db:seed --class=AdminUserSeeder
   ```

   **Opción B (Datos personalizados vía Tinker):**
   ```bash
   # Ejecuta esto y reemplaza los datos
   php artisan tinker --execute="App\Models\User::create(['name' => 'TuNombre', 'email' => 'tu@correo.com', 'password' => bcrypt('tu_password_seguro'), 'email_verified_at' => now()])"
   ```

## 6. Configuración del Bot de WhatsApp
El bot requiere dependencias de Puppeteer en Linux:
```bash
cd whatsapp-bot
npm install
# Instalar dependencias para el navegador de Puppeteer:
sudo apt install -y libgbm-dev libnss3 libatk-bridge2.0-0 libgtk-3-0 libasound2
```

### Instalar y configurar PM2 (para mantener el bot encendido):
```bash
sudo npm install -g pm2
pm2 start index.js --name "stefy-bot"
pm2 save
pm2 startup
```

## 7. Configuración de Nginx
Crea un archivo de configuración para el sitio:
```bash
sudo nano /etc/nginx/sites-available/stefynails
```

Copia y pega esta configuración estándar (ajusta `server_name` y la versión de PHP si es necesario):
```nginx
server {
    listen 80;
    server_name ec2-18-222-97-39.us-east-2.compute.amazonaws.com; # O tu IP/Dominio
    root /var/www/html/StefyNails/stefynails/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock; # Ajusta a tu versión (8.1, 8.2, 8.3)
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Activa el sitio y reinicia Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/stefynails /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

> [!TIP]
> **Si recibes el error:** `could not build server_names_hash, you should increase server_names_hash_bucket_size: 64`
>
> 1. Edita el archivo de configuración principal de Nginx:
>    ```bash
>    sudo nano /etc/nginx/nginx.conf
>    ```
> 2. Busca la línea `# server_names_hash_bucket_size 64;` dentro del bloque `http { ... }`.
> 3. Quita el `#` para activarla (o añádela si no existe):
>    ```nginx
>    http {
>        server_names_hash_bucket_size 64;
>        ...
>    }
>    ```
> 4. Guarda y reinicia: `sudo systemctl restart nginx`

---
> [!IMPORTANT]
> Recuerda configurar los **Security Groups** en AWS para permitir el tráfico en los puertos 80 (HTTP) y 3000 (para la comunicación del bot si es necesaria externamente).
