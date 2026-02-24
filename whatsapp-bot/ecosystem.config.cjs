module.exports = {
    apps: [
        {
            name: 'stefy-bot',
            script: 'index.js',
            watch: false,
            instances: 1,
            autorestart: true,
            max_memory_restart: '300M',
            restart_delay: 5000, // Esperar 5 segundos antes de reiniciar si falla
            env: {
                NODE_ENV: 'production',
                PORT: 3000
            }
        }
    ]
};
