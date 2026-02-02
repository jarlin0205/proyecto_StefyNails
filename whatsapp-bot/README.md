## 1. Instalación de Dependencias
Abre una terminal en la carpeta `whatsapp-bot` y ejecuta:
```bash
npm install
```

> [!NOTE]
> **Si tienes error con npm en Windows:**
> Si Windows te bloquea `npm`, intenta usar este comando en la terminal antes de instalar:
> `Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process`
> O usa directamente `npm.cmd install`.

## 2. Iniciar el Bot
No necesitas Docker. Ejecuta el bot directamente con:
```bash
node index.js
```

## 3. Escanear Código QR
- Al iniciar, verás un código QR en la terminal.
- Abre WhatsApp en tu celular -> Dispositivos vinculados -> Vincular un dispositivo.
- Escanea el código QR de la pantalla.

## 4. Comandos Disponibles# Docker provee instrucciones dedicadas para cada sistema operativo.
# Por favor consulta la documentación oficial en https://www.docker.com/get-started/

# Descarga la imagen de Docker de Node.js:
docker pull node:24-alpine

# Crea un contenedor de Node.js e inicia una sesión shell:
docker run -it --rm --entrypoint sh node:24-alpine

# Verifica la versión de Node.js:
node -v # Debería mostrar "v24.13.0".

# Verifica versión de npm:
npm -v # Debería mostrar "11.6.2".
Una vez conectado, el bot responderá a los siguientes comandos (puedes probarlo enviándote un mensaje a ti mismo o desde otro número):

- `CONFIRMAR [ID]` -> Ejemplo: `CONFIRMAR 5`
- `CANCELAR [ID]` -> Ejemplo: `CANCELAR 5`
- `REPROGRAMAR [ID] [DD/MM/AAAA] [HH:MM]` -> Ejemplo: `REPROGRAMAR 5 10/02/2026 15:00`
- `MENU` o `AYUDA` -> Muestra las opciones.

> [!TIP]
> Puedes cambiar el puerto en `index.js` si tu servidor corre en uno distinto al 8000.
