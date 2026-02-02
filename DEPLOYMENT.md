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
```

### Configurar `.env`:
Edita el archivo `.env` con los datos de tu base de datos y la URL:
```bash
nano .env
# Ajustar:
# APP_URL=http://ec2-18-222-97-39.us-east-2.compute.amazonaws.com
# DB_DATABASE=stefynails
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_password
```

### Permisos de Carpeta:
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

## 5. Configuración de la Base de Datos
Crea la base de datos en MySQL y corre las migraciones:
```bash
sudo mysql -u root
# CREATE DATABASE stefynails;
# EXIT;

php artisan migrate --force
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
Pega la configuración estándar de Laravel y luego activa el sitio:
```bash
sudo ln -s /etc/nginx/sites-available/stefynails /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---
> [!IMPORTANT]
> Recuerda configurar los **Security Groups** en AWS para permitir el tráfico en los puertos 80 (HTTP) y 3000 (para la comunicación del bot si es necesaria externamente).
