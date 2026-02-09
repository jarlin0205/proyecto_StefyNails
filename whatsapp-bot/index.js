const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const axios = require('axios');
const express = require('express');
const app = express();
app.use(express.json());

// CONFIGURACIÓN PRODUCCIÓN
// El bot local enviará comandos a esta URL (Producción en AWS)
const API_BASE_URL = 'http://18.222.97.39/api/bot';

// El servidor de Laravel en AWS enviará notificaciones al bot (Local) 
// vía túnel reverso SSH al puerto 3000 local.

// GESTIÓN DE ESTADO (En memoria)
const userStates = {};

const STATES = {
    IDLE: 'IDLE',
    AWAITING_RESCHEDULE: 'AWAITING_RESCHEDULE'
};

/**
 * Parsea fechas y horas en formato 12h (AM/PM) a ISO (YYYY-MM-DD HH:MM)
 * Formatos soportados: DD/MM/YYYY HH:MM AM/PM, DD-MM HH:MM PM, etc.
 */
function parseDateTimeToISO(input) {
    const text = input.trim().toUpperCase();

    // Regex para capturar: Día, Mes, (Opcional) Año, Hora, Minutos, (Opcional) AM/PM
    const regex = /(\d{1,2})[\/\-](\d{1,2})(?:[\/\-](\d{2,4}))?\s+(\d{1,2}):(\d{2})(?:\s*(AM|PM))?/;
    const match = text.match(regex);

    if (!match) return null;

    let [_, day, month, year, hours, minutes, meridiem] = match;

    // Año por defecto si no se provee
    if (!year) year = new Date().getFullYear();
    else if (year.length === 2) year = '20' + year;

    let hh = parseInt(hours);
    const mm = minutes;

    // Conversión a 24h si hay AM/PM
    if (meridiem) {
        if (meridiem === 'PM' && hh < 12) hh += 12;
        if (meridiem === 'AM' && hh === 12) hh = 0;
    }

    return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')} ${hh.toString().padStart(2, '0')}:${mm}`;
}

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

client.on('qr', (qr) => {
    console.log('POR FAVOR ESCANEA EL CÓDIGO QR PARA INICIAR EL BOT:');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('¡Bot de Stefy Nails está listo y conectado!');
});

client.on('message', async (msg) => {
    const chat = await msg.getChat();
    const body = msg.body.trim().toUpperCase();
    const sender = msg.from.split('@')[0];

    // Inicializar estado si no existe
    if (!userStates[sender]) {
        userStates[sender] = { state: STATES.IDLE };
    }

    const userState = userStates[sender];

    // --- COMANDO GLOBAL MENU ---
    if (body === 'MENU' || body === 'AYUDA') {
        userState.state = STATES.IDLE;
        return msg.reply(`🌟 *Bienvenido al Bot de Stefy Nails* 🌟\n\nPodemos ayudarte a gestionar tu cita con estos comandos:\n\n1️⃣ *CONFIRMAR* - Para asegurar tu asistencia.\n2️⃣ *CANCELAR* - Si no puedes asistir.\n3️⃣ *REPROGRAMAR* - El bot te preguntará la fecha.\n\n_Escribe "MENU" para volver a ver esto._`);
    }

    // Manejo de Estados Especiales
    if (userState.state === STATES.AWAITING_RESCHEDULE) {
        if (body === 'CANCELAR' || body === 'SALIR') {
            userState.state = STATES.IDLE;
            return msg.reply('❌ Reprogramación cancelada. Escribe MENU para ver opciones.');
        }

        const isoDate = parseDateTimeToISO(msg.body);
        if (!isoDate) {
            return msg.reply('❌ Formato de fecha u hora no reconocido.\n\nPor favor intenta así:\n*DD/MM 02:30 PM*\n(Ejemplo: 15/02 04:00 PM)\n\nEscribe *CANCELAR* para salir.');
        }

        try {
            const res = await axios.post(`${API_BASE_URL}/reschedule`, {
                phone: sender,
                date: isoDate,
                reason: 'Reprogramado por el cliente vía WhatsApp (Flujo guiado)'
            });
            userState.state = STATES.IDLE;
            msg.reply(`📅 *Cita Reprogramada*\n${res.data.message}\n\nEscribe *MENU* para ver opciones. ✨`);
        } catch (err) {
            msg.reply('❌ Error: ' + (err.response?.data?.message || 'Horario no disponible o error en el sistema.'));
        }
        return;
    }

    // --- COMANDOS GENERALES (Solo si está en IDLE) ---
    if (body.startsWith('CONFIRMAR') || body === '1') {
        const parts = body.split(' ');
        const id = parts[1];

        try {
            const res = await axios.post(`${API_BASE_URL}/status`, {
                id: id || null,
                phone: sender,
                status: 'confirmed'
            });
            msg.reply(`✅ *Cita Confirmada*\n${res.data.message}\n\nEscribe *MENU* para ver opciones.`);
        } catch (err) {
            msg.reply('❌ Error: ' + (err.response?.data?.message || 'No se encontró cita para confirmar.'));
        }
        return;
    }

    if (body.startsWith('CANCELAR') || body === '2') {
        const parts = body.split(' ');
        const id = parts[1];

        try {
            const res = await axios.post(`${API_BASE_URL}/status`, {
                id: id || null,
                phone: sender,
                status: 'cancelled'
            });
            msg.reply(`🗑️ *Cita Cancelada*\n${res.data.message}\n\nSi deseas agendar una nueva cita, escribe *MENU*.`);
        } catch (err) {
            msg.reply('❌ Error: ' + (err.response?.data?.message || 'No se encontró cita para cancelar.'));
        }
        return;
    }

    if (body.startsWith('REPROGRAMAR') || body === '3') {
        try {
            const res = await axios.get(`${API_BASE_URL}/get-link?phone=${sender}`);
            if (res.data.success) {
                msg.reply(`📅 *Reprogramar Cita* 📅\n\nHola *${res.data.customer_name}*, para elegir un nuevo horario por favor ingresa al siguiente enlace:\n\n🔗 ${res.data.link}\n\nAllí podrás ver los horarios disponibles en tiempo real. ✨`);
            } else {
                msg.reply('❌ No encontramos una cita activa para reprogramar. Si deseas agendar una nueva cita, por favor visita nuestra web.');
            }
        } catch (err) {
            msg.reply('❌ Error: ' + (err.response?.data?.message || 'No se encontró cita para reprogramar.'));
        }
        return;
    }
});

// API PARA ENVIAR MENSAJES DESDE LARAVEL
app.post('/send-message', async (req, res) => {
    const { phone, message } = req.body;
    if (!phone || !message) return res.status(400).json({ error: 'Faltan datos' });

    try {
        // Limpiar el número (solo dígitos)
        const cleanPhone = phone.replace(/[^0-9]/g, '');

        // Formatear como chatId de WhatsApp
        const chatId = cleanPhone.includes('@c.us') ? cleanPhone : `${cleanPhone}@c.us`;

        console.log(`📤 Enviando mensaje a: ${chatId}`);

        // Verificar que el número existe en WhatsApp antes de enviar
        const numberExists = await client.isRegisteredUser(chatId);
        if (!numberExists) {
            console.error(`❌ El número ${chatId} no está registrado en WhatsApp`);
            return res.status(404).json({ error: 'Número no registrado en WhatsApp' });
        }

        await client.sendMessage(chatId, message);
        console.log(`✅ Mensaje enviado exitosamente a ${chatId}`);
        res.json({ success: true });
    } catch (err) {
        console.error('❌ Error enviando mensaje:', err);
        res.status(500).json({ error: 'No se pudo enviar el mensaje', details: err.message });
    }
});

const BOT_PORT = 3000; // El bot escuchará en el puerto 3000
app.listen(BOT_PORT, () => {
    console.log(`Servidor de notificaciones del bot corriendo en puerto ${BOT_PORT}`);
});

client.initialize();
