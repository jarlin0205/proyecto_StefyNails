const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const http = require('http');

/**
 * CONFIGURACIÓN
 */
const CONFIG = {
    API_BASE_URL: 'http://localhost/api/bot',
    BOT_PORT: 3000,
    CHROME_ARGS: ['--no-sandbox', '--disable-setuid-sandbox']
};

// GESTIÓN DE ESTADO (En memoria)
const userStates = {};
const STATES = {
    IDLE: 'IDLE',
    AWAITING_RESCHEDULE: 'AWAITING_RESCHEDULE'
};

/**
 * UTILERÍAS
 */

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
        headers: { 'Content-Type': 'application/json' }
    };
    if (data) options.body = JSON.stringify(data);

    try {
        const response = await fetch(url, options);
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Error en la API');
        return result;
    } catch (error) {
        console.error(`❌ Error API [${endpoint}]:`, error.message);
        throw error;
    }
}

/**
 * CLIENTE WHATSAPP
 */
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        args: CONFIG.CHROME_ARGS
    }
});

client.on('qr', (qr) => {
    console.log('--- POR FAVOR ESCANEA EL QR ---');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('✅ Bot de Stefy Nails conectado y listo.');
});

client.on('message', async (msg) => {
    const body = msg.body.trim().toUpperCase();
    const sender = msg.from.split('@')[0];

    // Inicializar estado
    if (!userStates[sender]) userStates[sender] = { state: STATES.IDLE };
    const userState = userStates[sender];

    // Comandos Globales
    if (body === 'MENU' || body === 'AYUDA') {
        try {
            const res = await callLaravelApi(`get-link?phone=${sender}`, 'GET');
            if (res.success) {
                userState.state = STATES.IDLE;
                return msg.reply(`🌟 *Bienvenido al Bot de Stefy Nails* 🌟\n\nHola *${res.customer_name}*, podemos ayudarte a gestionar tu cita con estos comandos:\n\n1️⃣ *CONFIRMAR*\n2️⃣ *CANCELAR*\n3️⃣ *REPROGRAMAR*\n\n_Escribe "MENU" para volver a ver esto._`);
            } else {
                return msg.reply(`🌸 *¡Hola!* 🌸\n\nNo encontramos una cita activa vinculada a este número. ¡Nos encantaría atenderte! ✨\n\nPuedes agendar tu cita fácilmente aquí:\n🔗 http://3.12.104.67\n\n¡Te esperamos! 💖`);
            }
        } catch (err) {
            // Si hay error en la API (ej: 404), sugerir agendar
            return msg.reply(`🌸 *¡Hola!* 🌸\n\nParece que no tienes citas activas. ¡Te invitamos a agendar una en nuestra web! ✨\n\n🔗 http://3.12.104.67\n\n¡Gracias! 💖`);
        }
    }

    // Estado: Esperando Reprogramación
    if (userState.state === STATES.AWAITING_RESCHEDULE) {
        if (body === 'CANCELAR' || body === 'SALIR') {
            userState.state = STATES.IDLE;
            return msg.reply('❌ Reprogramación cancelada.');
        }

        const isoDate = parseDateTimeToISO(msg.body);
        if (!isoDate) return msg.reply('❌ Formato no válido. Usa: *DD/MM 02:30 PM*');

        try {
            const res = await callLaravelApi('reschedule', 'POST', {
                phone: sender,
                date: isoDate,
                reason: 'Reprogramado vía WhatsApp'
            });
            userState.state = STATES.IDLE;
            msg.reply(`📅 *Cita Reprogramada*\n${res.message}`);
        } catch (err) {
            msg.reply(`❌ Error: ${err.message}`);
        }
        return;
    }

    // Comandos en IDLE
    if (body.startsWith('CONFIRMAR') || body === '1') {
        try {
            const res = await callLaravelApi('status', 'POST', { phone: sender, status: 'confirmed' });
            msg.reply(`✅ *Cita Confirmada*\n${res.message}`);
        } catch (err) {
            msg.reply(`❌ Error: ${err.message}`);
        }
    } else if (body.startsWith('CANCELAR') || body === '2') {
        try {
            const res = await callLaravelApi('status', 'POST', { phone: sender, status: 'cancelled' });
            msg.reply('✅ *Cita cancelada con éxito*');
        } catch (err) {
            msg.reply(`❌ Error: ${err.message}`);
        }
    } else if (body.startsWith('REPROGRAMAR') || body === '3') {
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
});

/**
 * SERVIDOR DE NOTIFICACIONES (Puro HTTP)
 * Reemplaza a Express
 */
const server = http.createServer((req, res) => {
    if (req.method === 'POST' && req.url === '/send-message') {
        let body = '';
        req.on('data', chunk => { body += chunk.toString(); });
        req.on('end', async () => {
            try {
                const { phone, message } = JSON.parse(body);
                if (!phone || !message) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    return res.end(JSON.stringify({ error: 'Faltan datos' }));
                }

                const cleanPhone = phone.replace(/[^0-9]/g, '');
                const chatId = `${cleanPhone}@c.us`;

                if (await client.isRegisteredUser(chatId)) {
                    await client.sendMessage(chatId, message);
                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: true }));
                } else {
                    res.writeHead(404, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: 'No registrado' }));
                }
            } catch (err) {
                res.writeHead(500, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'Error del servidor', details: err.message }));
            }
        });
    } else {
        res.writeHead(404);
        res.end();
    }
});

server.listen(CONFIG.BOT_PORT, () => {
    console.log(`🚀 Servidor HTTP del bot en puerto ${CONFIG.BOT_PORT}`);
});

client.initialize();
