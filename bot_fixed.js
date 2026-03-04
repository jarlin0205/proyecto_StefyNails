const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const http = require('http');

/**
 * CONFIGURACI├ôN
 */
const CONFIG = {
    API_BASE_URL: 'http://localhost/api/bot',
    BOT_PORT: 3000,
    CHROME_ARGS: ['--no-sandbox', '--disable-setuid-sandbox']
};

// GESTI├ôN DE ESTADO (En memoria)
const userStates = {};
const STATES = {
    IDLE: 'IDLE',
    AWAITING_RESCHEDULE: 'AWAITING_RESCHEDULE'
};

/**
 * UTILER├ìAS
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

// Env├¡o de peticiones a la API Laravel (reemplaza a axios)
async function callLaravelApi(endpoint, method = 'POST', data = null) {
    const url = `${CONFIG.API_BASE_URL}/${endpoint}`;
    const options = {
        method,
        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' }
    };
    if (data) options.body = JSON.stringify(data);

    try {
        const response = await fetch(url, options);

        // Verificar si la respuesta es JSON antes de parsear
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error(`ÔØî Error API [${endpoint}]: Respuesta no es JSON (Posible error de servidor PHP).`);
            throw new Error('El servidor respondi├│ con un error inesperado (no JSON).');
        }

        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Error en la API');
        return result;
    } catch (error) {
        console.error(`ÔØî Error API [${endpoint}]:`, error.message);
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
        args: CONFIG.CHROME_ARGS,
        handleSIGINT: false, // PM2 manejará esto
        handleSIGTERM: false,
        protocolTimeout: 60000
    }
});

/**
 * MANEJO DE ERRORES GLOBAL
 * Evita que el proceso muera por errores no capturados
 */
process.on('uncaughtException', (err) => {
    console.error('­ƒÆÑ Error Cr├¡tico No Capturado (Uncaught):', err.message);
    console.error(err.stack);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('­ƒÆÑ Promesa No Manejada (Unhandled Rejection):', reason);
});

client.on('qr', (qr) => {
    console.log('--- POR FAVOR ESCANEA EL QR ---');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('Ô£à Bot de Stefy Nails conectado y listo.');
});

/**
 * RECONEXI├ôN AUTOM├üTICA
 */
client.on('disconnected', async (reason) => {
    console.log('ÔÜá´©Å Cliente de WhatsApp DESCONECTADO:', reason);
    console.log('­ƒöä Intentando reiniciar cliente en 5 segundos...');

    setTimeout(async () => {
        try {
            await client.initialize();
            console.log('Ô£à Re-inicializaci├│n enviada.');
        } catch (err) {
            console.error('ÔØî Error al re-inicializar:', err.message);
        }
    }, 5000);
});

client.on('message', async (msg) => {
    try {
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
                    return msg.reply(`­ƒîƒ *Bienvenido al Bot de Stefy Nails* ­ƒîƒ\n\nHola *${res.customer_name}*, podemos ayudarte a gestionar tu cita con estos comandos:\n\n1´©ÅÔâú *CONFIRMAR*\n2´©ÅÔâú *CANCELAR*\n3´©ÅÔâú *REPROGRAMAR*\n\n_Escribe "MENU" para volver a ver esto._`);
                } else {
                    return msg.reply(`­ƒî© *┬íHola!* ­ƒî©\n\nNo encontramos una cita activa vinculada a este n├║mero. ┬íNos encantar├¡a atenderte! Ô£¿\n\nPuedes agendar tu cita f├ícilmente aqu├¡:\n­ƒöù http://3.12.104.67\n\n┬íTe esperamos! ­ƒÆû`);
                }
            } catch (err) {
                return msg.reply(`­ƒî© *┬íHola!* ­ƒî©\n\nParece que no tienes citas activas. ┬íTe invitamos a agendar una en nuestra web! Ô£¿\n\n­ƒöù http://3.12.104.67\n\n┬íGracias! ­ƒÆû`);
            }
        }

        // Estado: Esperando Reprogramaci├│n
        if (userState.state === STATES.AWAITING_RESCHEDULE) {
            if (body === 'CANCELAR' || body === 'SALIR') {
                userState.state = STATES.IDLE;
                return msg.reply('ÔØî Reprogramaci├│n cancelada.');
            }

            const isoDate = parseDateTimeToISO(msg.body);
            if (!isoDate) return msg.reply('ÔØî Formato no v├ílido. Usa: *DD/MM 02:30 PM*');

            try {
                const res = await callLaravelApi('reschedule', 'POST', {
                    phone: sender,
                    date: isoDate,
                    reason: 'Reprogramado v├¡a WhatsApp'
                });
                userState.state = STATES.IDLE;
                msg.reply(`­ƒôà *Cita Reprogramada Recuerda Estar 10 minutos antes de tu cita  *\n${res.message}`);
            } catch (err) {
                msg.reply(`ÔØî Error: ${err.message}`);
            }
            return;
        }

        // Comandos en IDLE
        if (body.startsWith('CONFIRMAR') || body === '1') {
            try {
                const res = await callLaravelApi('status', 'POST', { phone: sender, status: 'confirmed' });
                msg.reply(`Ô£à *Cita Confirmada Recuerda Estar 10 minutos antes de tu cita*\n${res.message}`);
            } catch (err) {
                msg.reply(`ÔØî Error: ${err.message}`);
            }
        } else if (body.startsWith('CANCELAR') || body === '2') {
            try {
                const res = await callLaravelApi('status', 'POST', { phone: sender, status: 'cancelled' });
                msg.reply('Ô£à *Cita cancelada con ├®xito*');
            } catch (err) {
                msg.reply(`ÔØî Error: ${err.message}`);
            }
        } else if (body.startsWith('REPROGRAMAR') || body === '3') {
            try {
                const res = await callLaravelApi(`get-link?phone=${sender}`, 'GET');
                if (res.success) {
                    msg.reply(`­ƒôà *Reprogramar Cita*\nHola *${res.customer_name}*, usa este enlace:\n­ƒöù ${res.link}`);
                } else {
                    msg.reply('ÔØî No encontramos cita activa.');
                }
            } catch (err) {
                msg.reply(`ÔØî Error: ${err.message}`);
            }
        }
    } catch (globalError) {
        console.error('ÔØî Error manejando mensaje:', globalError.message);
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
                const parsedBody = JSON.parse(body);
                const { phone, message, pdfUrl, pdfBase64, filename } = parsedBody;

                if (!phone || (!message && !pdfUrl)) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    return res.end(JSON.stringify({ error: 'Faltan datos' }));
                }

                if (!client.info || !client.info.wid) {
                    res.writeHead(503, { 'Content-Type': 'application/json' });
                    return res.end(JSON.stringify({ error: 'El bot no est├í listo o est├í desconectado' }));
                }

                const cleanPhone = phone.replace(/\D/g, '');
                const chatId = `${cleanPhone}@c.us`;

                console.log(`­ƒô® Solicitud recibida para ${cleanPhone}`);

                const isRegistered = await client.isRegisteredUser(chatId);

                if (isRegistered) {
                    if (pdfBase64) {
                        try {
                            const media = new MessageMedia('application/pdf', pdfBase64, filename || 'factura.pdf');
                            await client.sendMessage(chatId, media, { caption: message });
                            console.log(`­ƒôí Factura PDF (Base64) enviada a ${cleanPhone}`);
                        } catch (b64Error) {
                            console.error('ÔØî Error enviando PDF Base64:', b64Error.message);
                            if (message) await client.sendMessage(chatId, message);
                        }
                    } else if (pdfUrl) {
                        try {
                            // REESCRITURA INTERNA: El servidor no suele poder verse a s├¡ mismo por IP p├║blica
                            let fetchUrl = pdfUrl;
                            if (fetchUrl.includes('3.12.104.67')) {
                                fetchUrl = fetchUrl.replace('3.12.104.67', '127.0.0.1');
                            }

                            console.log(`­ƒôí Intentando descargar PDF desde: ${fetchUrl}`);
                            const media = await MessageMedia.fromUrl(fetchUrl);
                            await client.sendMessage(chatId, media, { caption: message });
                            console.log(`­ƒôí Factura PDF (URL) enviada a ${cleanPhone}`);
                        } catch (mediaError) {
                            console.error('ÔØî Error cargando PDF desde URL:', mediaError.message);
                            console.error('JSON Error:', JSON.stringify(mediaError));
                            console.error('URL fallida:', pdfUrl);
                            if (message) await client.sendMessage(chatId, message);
                        }
                    } else {
                        await client.sendMessage(chatId, message);
                        console.log(`­ƒôí Mensaje de texto enviado a ${cleanPhone}`);
                    }

                    res.writeHead(200, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ success: true }));
                } else {
                    res.writeHead(404, { 'Content-Type': 'application/json' });
                    res.end(JSON.stringify({ error: 'N├║mero no registrado' }));
                }
            } catch (err) {
                console.error('ÔØî Error en /send-message:', err.message);
                res.writeHead(500, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: 'Error interno', details: err.message }));
            }
        });
    } else {
        res.writeHead(404);
        res.end();
    }
});

server.listen(CONFIG.BOT_PORT, () => {
    console.log(`­ƒÜÇ Servidor HTTP del bot en puerto ${CONFIG.BOT_PORT}`);
});

client.initialize();

/**
 * CIERRE GRACIOSO
 * Asegura que Puppeteer se cierre correctamente al detener el proceso
 */
async function gracefulShutdown(signal) {
    console.log(`\n--- Recibida se├▒al ${signal}. Cerrando bot de forma segura... ---`);
    try {
        if (client) {
            await client.destroy();
            console.log('Ô£à Cliente de WhatsApp cerrado.');
        }
        if (server) {
            server.close(() => {
                console.log('Ô£à Servidor HTTP cerrado.');
                process.exit(0);
            });
        } else {
            process.exit(0);
        }
    } catch (err) {
        console.error('ÔØî Error durante el cierre:', err.message);
        process.exit(1);
    }
}

process.on('SIGINT', () => gracefulShutdown('SIGINT'));
process.on('SIGTERM', () => gracefulShutdown('SIGTERM'));
