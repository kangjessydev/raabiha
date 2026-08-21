/**
 * Config manager
 * Simpan/baca konfigurasi printer dari file JSON
 */

const fs = require('fs');
const path = require('path');
const os = require('os');

// Config disimpan di home directory user
const CONFIG_DIR = path.join(os.homedir(), '.raabiha-agent');
const CONFIG_PATH = path.join(CONFIG_DIR, 'config.json');

const DEFAULTS = {
    printer_mac: process.env.PRINTER_MAC || '',
    printer_name: process.env.PRINTER_NAME || '',
    // Linux: /dev/rfcomm0 (auto-bind), Windows: COM3, COM4, dsb
    serial_port: process.env.SERIAL_PORT || '',
    ws_port: parseInt(process.env.WS_PORT || '8765', 10),
};

function loadConfig() {
    // Env vars override segalanya (berguna untuk Docker/CI)
    if (process.env.PRINTER_MAC) {
        return {
            ...DEFAULTS,
            printer_mac: process.env.PRINTER_MAC,
            printer_name: process.env.PRINTER_NAME || 'Printer',
            ws_port: parseInt(process.env.WS_PORT || '8765', 10),
        };
    }

    // Baca dari file config
    if (fs.existsSync(CONFIG_PATH)) {
        try {
            const raw = fs.readFileSync(CONFIG_PATH, 'utf8');
            const config = JSON.parse(raw);
            return { ...DEFAULTS, ...config };
        } catch (err) {
            console.warn('[CONFIG] Gagal baca config file, pakai defaults:', err.message);
        }
    }

    return { ...DEFAULTS };
}

function saveConfig(config) {
    try {
        if (!fs.existsSync(CONFIG_DIR)) {
            fs.mkdirSync(CONFIG_DIR, { recursive: true });
        }
        fs.writeFileSync(CONFIG_PATH, JSON.stringify(config, null, 2), 'utf8');
        console.log('[CONFIG] Config disimpan:', CONFIG_PATH);
    } catch (err) {
        console.error('[CONFIG] Gagal simpan config:', err.message);
    }
}

module.exports = { loadConfig, saveConfig, CONFIG_PATH };
