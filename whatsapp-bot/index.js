const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const http = require('http');

/**
 * CONFIGURACIÓN
 */
const CONFIG = {
    API_BASE_URL: 'https://stefynails.online/api/bot',
    BOT_PORT: 3000,
    CHROME_ARGS: ['--no-sandbox', '--disable-setuid-sandbox'],
    ADMIN_PHONE: '573014982961' // Número para alertas de emergencia
};

let consecutiveErrors = 0;
const ERROR_THRESHOLD = 5;

// GESTIÓN DE ESTADO (En memoria)
const userStates = {};
const STATES = {
    IDLE: 'IDLE',
    AWAITING_RESCHEDULE: 'AWAITING_RESCHEDULE'
};

/**
 * UTILERÍAS
 */

// Obtener timestamp formateado para logs
function getTimestamp() {
    return new Date().toLocaleString('es-CO', { timeZone: 'America/Bogota' });
}

// Envoltorio de fetch con timeout
async function fetchWithTimeout(resource, options = {}) {
    const { timeout = 15000 } = options;
    
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeout);
    
    try {
        const response = await fetch(resource, {
            ...options,
            signal: controller.signal
        });
        clearTimeout(id);
        return response;
    } catch (error) {
        clearTimeout(id);
        if (error.name === 'AbortError') {
            throw new Error(`Timeout de red superado (${timeout}ms)`);
        }
        throw error;
    }
}

// Parsea fechas a ISO
function parseDateTimeToISO(input) {
    const text = input.trim().toUpperCase();
    const regex = /(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?\s+(\d{1,2}):(\d{2})(?:\s*(AM|PM))?/;
    const match = text.match(regex);
    if (!match) return null;

    let [_, day, month, year, hours, minutes, meridiem] = match;
    if (!year) year = new Date().getFullYear();
    else if (year.length === 2) year = '20' + year;

    let hh = parseInt(hours);
    if (meridiem) {
        if (meridiem === 'PM' && hh < 12) hh += 12;
        if (meridiem === 'AM' && hh === 12) hh = 0;
    }
    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')} ${hh.toString().padStart(2, '0')}:${minutes}`;
}

// Envío de peticiones a la API Laravel (reemplaza a axios)
async function callLaravelApi(endpoint, method = 'POST', data = null) {
    const url = `${CONFIG.API_BASE_URL}/${endpoint}`;
    const options = {
        method,
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
    };
    if (data) options.body = JSON.stringify(data);

    try {
        const response = await fetchWithTimeout(url, options);

        // Verificar si la respuesta es JSON antes de parsear
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error(`[${getTimestamp()}] ❌ Error API [${endpoint}]: Respuesta no es JSON (Posible error de servidor PHP).`);
            console.error(`📄 Fragmento de respuesta: ${text.substring(0, 100)}...`);
            throw new Error(`El servidor respondió con un error inesperado (no JSON). Estado: ${response.status}`);
        }

        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Error en la API');

        consecutiveErrors = 0; // Resetear al tener éxito
        return result;
    } catch (error) {
        consecutiveErrors++;
        console.error(`[${getTimestamp()}] ❌ Error API [${endpoint}] (${consecutiveErrors}/${ERROR_THRESHOLD}):`, error.message);

        if (consecutiveErrors >= ERROR_THRESHOLD) {
            triggerEmergencyAlert(error.message);
        }

        throw error;
    }
}

/**
 * ALERTAS DE EMERGENCIA
 */
async function triggerEmergencyAlert(errorMsg) {
    console.error(`[${getTimestamp()}] 🚨 DISPARANDO ALERTA DE EMERGENCIA...`);

    // 1. Intentar enviar por WhatsApp al admin
    if (client && client.info) {
        try {
            const chatId = `${CONFIG.ADMIN_PHONE}@c.us`;
            await client.sendMessage(chatId, `🚨 *ALERTA CRÍTICA BOT*\nHe detectado ${consecutiveErrors} fallos consecutivos.\n\nÚltimo Error: ${errorMsg}\n\nPor favor revisa el servidor.`);
            console.log(`[${getTimestamp()}] 📡 Alerta enviada por WhatsApp al Administrador.`);
        } catch (waErr) {
            console.error(`[${getTimestamp()}] ❌ No se pudo enviar alerta por WhatsApp:`, waErr.message);
        }
    }

    // 2. Reportar al endpoint de emergencia de Laravel (para email)
    // Usamos fetch directamente para evitar recursión con callLaravelApi si esta falla
    try {
        await fetchWithTimeout(`${CONFIG.API_BASE_URL}/emergency-report`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                error: errorMsg,
                consecutive_errors: consecutiveErrors,
                timestamp: new Date().toISOString()
            })
        });
        console.log(`[${getTimestamp()}] 📡 Alerta reportada al endpoint de emergencia de Laravel.`);
    } catch (apiErr) {
        console.error(`[${getTimestamp()}] ❌ No se pudo reportar al endpoint de emergencia:`, apiErr.message);
    }
}

/**
 * CLIENTE WHATSAPP
 */
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        args: CONFIG.CHROME_ARGS,
        handleSIGINT: false, // PM2 manejará esto
        handleSIGTERM: false,
        protocolTimeout: 180000 // Aumentado a 3 minutos para mayor estabilidad
    }
});

/**
 * MANEJO DE ERRORES GLOBAL
 * Evita que el proceso muera por errores no capturados
 */
process.on('uncaughtException', (err) => {
    console.error(`[${getTimestamp()}] 💥 Error Crítico No Capturado (Uncaught):`, err.message);
    console.error(err.stack);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error(`[${getTimestamp()}] 💥 Promesa No Manejada (Unhandled Rejection):`, reason);
});

client.on('qr', (qr) => {
    console.log(`[${getTimestamp()}] --- POR FAVOR ESCANEA EL QR ---`);
    qrcode.generate(qr, { small: true });
});

client.on('ready', async () => {
    console.log(`[${getTimestamp()}] ✅ Bot de Stefy Nails conectado y listo.`);

    // Cargar configuración dinámica desde Laravel
    try {
        const config = await callLaravelApi('config', 'GET');
        if (config.success && config.admin_phone) {
            CONFIG.ADMIN_PHONE = config.admin_phone;
            console.log(`[${getTimestamp()}] ⚙️ Configuración cargada. Admin Phone: ${CONFIG.ADMIN_PHONE}`);
        }
    } catch (err) {
        console.error(`[${getTimestamp()}] ⚠️ No se pudo cargar la configuración dinámica, usando valores por defecto.`);
    }
});

/**
 * RECONEXIÓN AUTOMÁTICA
 */
client.on('disconnected', async (reason) => {
    console.log(`[${getTimestamp()}] ⚠️ Cliente de WhatsApp DESCONECTADO:`, reason);
    console.log(`[${getTimestamp()}] 🔄 Intentando reiniciar cliente en 5 segundos...`);

    setTimeout(async () => {
        try {
            await client.initialize();
            console.log(`[${getTimestamp()}] ✅ Re-inicialización enviada.`);
        } catch (err) {
            console.error(`[${getTimestamp()}] ❌ Error al re-inicializar:`, err.message);
        }
    }, 5000);
});

/**
 * WATCHDOG: Reinicio automático si el cliente se queda colgado
 */
let lastHeartbeat = Date.now();
setInterval(async () => {
    if (client) {
        let isHealthy = false;
        try {
            // Verificamos si la página existe antes de evaluar (evita Errores de "browser disconnected")
            if (client.pupPage && !client.pupPage.isClosed()) {
                const healthCheck = client.pupPage.evaluate(() => true);
                const timeout = new Promise((_, reject) => setTimeout(() => reject(new Error('TIMEOUT')), 15000));
                
                await Promise.race([healthCheck, timeout]);
                
                // Si llegamos aquí, el navegador responde
                const state = await client.getState().catch(() => 'ERROR');
                
                if (state !== 'ERROR') {
                    lastHeartbeat = Date.now();
                    isHealthy = true;
                } else {
                    console.warn(`⚠️ Watchdog: Estado de sesión sospechoso (State: ${state})`);
                }
            } else {
                 console.warn(`[${getTimestamp()}] ⚠️ Watchdog: Página de Puppeteer no disponible o cerrada.`);
            }
        } catch (err) {
            console.error(`[${getTimestamp()}] ⚠️ Watchdog Exception:`, err.message);
            // Si el error es de desconexión, no reiniciamos lastHeartbeat, permitiendo el timeout de 6 min
        }
    }

    // Si pasan más de 6 minutos sin un chequeo exitoso, forzar reinicio
    if (Date.now() - lastHeartbeat > 360000) {
        console.error(`[${getTimestamp()}] 🚨 Watchdog: El bot está en estado "zombie" o bloqueado (6 min). Forzando reinicio...`);
        await triggerEmergencyAlert('ESTADO_ZOMBIE_DETECTADO (Watchdog Timeout)');
        
        setTimeout(() => {
            gracefulShutdown('WATCHDOG_TIMEOUT');
        }, 2000);
    }
}, 60000); // Revisar cada minuto

client.on('message_create', async (msg) => {
    try {
        // FILTRO ROBUSTO: Ignorar grupos (@g.us), estados (status@broadcast) y listas de difusión (@broadcast)
        if (msg.from.endsWith('@g.us') || msg.from === 'status@broadcast' || msg.from.endsWith('@broadcast')) {
            return;
        }

        // Si el mensaje es "de mí" (del propio bot), solo procesar si es el comando TEST-ALERTA
        // Esto permite que el usuario pruebe el bot consigo mismo sin entrar en bucles infinitos.
        if (msg.fromMe && msg.body.trim().toUpperCase() !== 'TEST-ALERTA') {
            return;
        }

        // Normalización inteligente: trim, upper case y remover acentos
        const body = msg.body.trim().toUpperCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");

        const sender = msg.from.split('@')[0];

        // Evitar logs basura de status o grupos que se filtren
        if (sender === 'status') return;

        console.log(`[${getTimestamp()}] 📩 Mensaje de ${sender}: "${msg.body}" -> "${body}"`);

        // Inicializar estado
        if (!userStates[sender]) userStates[sender] = { state: STATES.IDLE };
        const userState = userStates[sender];

        // Comandos Globales
        // Comandos Globales
        if (body === 'MENU' || body === 'AYUDA') {
            try {
                const res = await callLaravelApi(`get-link?phone=${sender}`, 'GET');
                if (res.success) {
                    userState.state = STATES.IDLE;
                    return msg.reply(`🌟 *Bienvenido al Bot de Stefy Nails* 🌟\n\nHola *${res.customer_name}*, podemos ayudarte a gestionar tu cita con estos comandos:\n\n1️⃣ *CANCELAR*\n2️⃣ *REPROGRAMAR*\n\n_Escribe "MENU" para volver a ver esto._`);
                } else {
                    return msg.reply(`🌸 *¡Hola!* 🌸\n\nNo encontramos una cita activa vinculada a este número. ¡Nos encantaría atenderte! ✨\n\nPuedes agendar tu cita fácilmente aquí:\n🔗 https://www.stefynails.online\n\n¡Te esperamos! 💖`);
                }
            } catch (err) {
                return msg.reply(`🌸 *¡Hola!* 🌸\n\nParece que no tienes citas activas. ¡Te invitamos a agendar una en nuestra web! ✨\n\n🔗 https://www.stefynails.online\n\n¡Gracias! 💖`);
            }
        }

        // COMANDO DE PRUEBA (Solo para el Admin)
        if (body === 'TEST-ALERTA' && sender === CONFIG.ADMIN_PHONE) {
            msg.reply('🛠️ Iniciando prueba de alerta de emergencia...');
            consecutiveErrors = 5; // Simular umbral alcanzado
            await triggerEmergencyAlert('PRUEBA_MANUAL_DEL_ADMINISTRADOR');
            return msg.reply('✅ Alerta de prueba enviada. Revisa tu WhatsApp (mensaje anterior) y tu correo electrónico.');
        }

        // Estado: Esperando Reprogramación
        if (userState.state === STATES.AWAITING_RESCHEDULE) {
            if (body === 'CANCELAR' || body === 'SALIR') {
                userState.state = STATES.IDLE;
                return msg.reply('❌ Reprogramación cancelada.');
            }

            const isoDate = parseDateTimeToISO(msg.body);
            if (!isoDate) return msg.reply('❌ Formato no válido. Usa: *DD/MM 02:30 PM*');

            const res = await callLaravelApi('reschedule', 'POST', {
                phone: sender,
                date: isoDate,
                reason: 'Reprogramado vía WhatsApp'
            });
            userState.state = STATES.IDLE;
            msg.reply(`📅 *Cita Reprogramada Recuerda Estar 10 minutos antes de tu cita*\n${res.message}`);
            return;
        }

        // Comandos en IDLE
        if (body.startsWith('ASISTIRE')) {
            console.log(`✨ Comando detectado: ASISTIRE`);
            try {
                const res = await callLaravelApi('checkin', 'POST', { phone: sender });
                msg.reply(`${res.message}`);
            } catch (err) {
                msg.reply(`❌ Error: ${err.message}`);
            }
        } else if (body.startsWith('CANCELAR') || body === '1') {
            console.log(`✨ Comando detectado: CANCELAR (1)`);
            try {
                const res = await callLaravelApi('status', 'POST', { phone: sender, status: 'cancelled' });
                msg.reply('✅ *Cita cancelada con éxito*');
            } catch (err) {
                msg.reply(`❌ Error: ${err.message}`);
            }
        } else if (body.startsWith('REPROGRAMAR') || body === '2') {
            console.log(`✨ Comando detectado: REPROGRAMAR (2)`);
            try {
                const res = await callLaravelApi(`get-link?phone=${sender}`, 'GET');
                if (res.success) {
                    msg.reply(`📅 *Reprogramar Cita*\nHola *${res.customer_name}*, usa este enlace:\n🔗 ${res.link}`);
                } else {
                    msg.reply('❌ No encontramos cita activa.');
                }
            } catch (err) {
                msg.reply(`❌ Error: ${err.message}`);
            }
        }
    } catch (globalError) {
        console.error('❌ Error manejando mensaje:', globalError.message);
    }
});

/**
 * SERVIDOR DE NOTIFICACIONES
 */
const server = http.createServer((req, res) => {
    if (req.method === 'POST' && req.url === '/send-message') {
        let body = '';
        req.on('data', chunk => { body += chunk.toString(); });
        req.on('end', async () => {
            try {
                if (!body) throw new Error('Cuerpo de solicitud vacío');
                const parsedBody = JSON.parse(body);
                const { phone, message, pdfUrl, pdfBase64, filename } = parsedBody;

                if (!phone) throw new Error('Teléfono no proporcionado');

                const cleanPhone = phone.replace(/\D/g, '');
                const chatId = `${cleanPhone}@c.us`;

                console.log(`[${getTimestamp()}] 📩 Solicitud recibida para ${cleanPhone}`);

                if (!client || !client.info) {
                    res.writeHead(503, { 'Content-Type': 'application/json' });
                    return res.end(JSON.stringify({ error: 'El bot no está listo' }));
                }

                const isRegistered = await client.isRegisteredUser(chatId);

                if (isRegistered) {
                    if (pdfBase64) {
                        try {
                            const media = new MessageMedia('application/pdf', pdfBase64, filename || 'factura.pdf');
                            await client.sendMessage(chatId, media, { caption: message });
                            console.log(`[${getTimestamp()}] 📡 Factura PDF (Base64) enviada a ${cleanPhone}`);
                        } catch (b64Error) {
                            console.error(`[${getTimestamp()}] ❌ Error enviando PDF Base64:`, b64Error.message);
                            if (message) await client.sendMessage(chatId, message);
                        }
                    } else if (pdfUrl) {
                        try {
                            let fetchUrl = pdfUrl;
                            if (fetchUrl.includes('stefynails.online')) fetchUrl = fetchUrl.replace('https://stefynails.online', 'http://127.0.0.1');

                            console.log(`[${getTimestamp()}] 📡 Descargando PDF desde: ${fetchUrl}`);
                            const media = await MessageMedia.fromUrl(fetchUrl);
                            await client.sendMessage(chatId, media, { caption: message });
                            console.log(`[${getTimestamp()}] 📡 Factura PDF (URL) enviada a ${cleanPhone}`);
                        } catch (mediaError) {
                            console.error(`[${getTimestamp()}] ❌ Error cargando PDF desde URL:`, mediaError.message);
                            if (message) await client.sendMessage(chatId, message);
                        }
                    } else {
                        await client.sendMessage(chatId, message);
                        console.log(`[${getTimestamp()}] 📡 Mensaje de texto enviado a ${cleanPhone}`);
                    }

                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: true }));
                } else {
                    console.log(`[${getTimestamp()}] ❌ Número no registrado en WhatsApp: ${cleanPhone}`);
                    res.writeHead(404, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: 'Número no registrado' }));
                }
            } catch (err) {
                console.error(`[${getTimestamp()}] ❌ Error procesando solicitud:`, err.message);

                // Incrementar contador de errores para fallos en envíos
                consecutiveErrors++;
                if (consecutiveErrors >= ERROR_THRESHOLD) {
                    await triggerEmergencyAlert(`FALLO_ENVIO: [${err.message.substring(0, 50)}] (Contador: ${consecutiveErrors})`);
                }

                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: err.message }));
            }
        });
    } else {
        res.writeHead(404);
        res.end();
    }
});

server.listen(CONFIG.BOT_PORT, () => {
    console.log(`[${getTimestamp()}] 🚀 Servidor HTTP del bot en puerto ${CONFIG.BOT_PORT}`);
});

client.initialize();

/**
 * CIERRE GRACIOSO
 * Asegura que Puppeteer se cierre correctamente al detener el proceso
 */
async function gracefulShutdown(signal) {
    console.log(`\n--- [${getTimestamp()}] Recibida señal ${signal}. Cerrando bot de forma segura... ---`);
    try {
        if (client) {
            await client.destroy();
            console.log(`[${getTimestamp()}] ✅ Cliente de WhatsApp cerrado.`);
        }
        if (server) {
            server.close(() => {
                console.log(`[${getTimestamp()}] ✅ Servidor HTTP cerrado.`);
                process.exit(0);
            });
        } else {
            process.exit(0);
        }
    } catch (err) {
        console.error(`[${getTimestamp()}] ❌ Error durante el cierre:`, err.message);
        process.exit(1);
    }
}

process.on('SIGINT', () => gracefulShutdown('SIGINT'));
process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
