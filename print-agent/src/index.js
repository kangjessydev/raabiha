/**
 * Raabiha Print Agent
 * Menjembatani browser POS (WebSocket) ↔ Printer Bluetooth Classic (SPP)
 * 
 * Cara pakai:
 *   node src/index.js
 * 
 * Env / args:
 *   PRINTER_MAC=XX:XX:XX:XX:XX:XX  — MAC address printer
 *   PRINTER_NAME=Xantri RPP02N      — nama printer (untuk scan)
 *   WS_PORT=8765                    — port WebSocket (default: 8765)
 */

const { createServer } = require('./server');
const { BluetoothPrinter } = require('./bluetooth');
const { loadConfig, saveConfig } = require('./config');

const isDev = process.argv.includes('--dev');

async function main() {
    console.log('==============================================');
    console.log('  Raabiha Print Agent v1.0.0');
    console.log('==============================================');

    // Load config
    const config = loadConfig();

    if (!config.printer_mac && !config.printer_name) {
        console.error('[ERROR] Printer belum dikonfigurasi!');
        console.error('        Set PRINTER_MAC atau PRINTER_NAME di config.json');
        console.error('        Path config: ' + require('./config').CONFIG_PATH);
        process.exit(1);
    }

    console.log(`[INFO] Printer: ${config.printer_name || 'Unknown'} (${config.printer_mac || 'scan by name'})`);
    console.log(`[INFO] WebSocket port: ${config.ws_port}`);

    // Init Bluetooth
    const printer = new BluetoothPrinter(config);

    // Init WebSocket server
    const server = createServer(config.ws_port, printer);

    // Connect ke printer
    console.log('[BT] Mencari printer...');
    await printer.connect().catch(err => {
        console.warn('[BT] Koneksi awal gagal, akan retry...', err.message);
    });

    // Handle graceful shutdown
    process.on('SIGINT', () => {
        console.log('\n[INFO] Mematikan agent...');
        printer.disconnect();
        server.close();
        process.exit(0);
    });

    process.on('SIGTERM', () => {
        printer.disconnect();
        server.close();
        process.exit(0);
    });
}

main().catch(err => {
    console.error('[FATAL]', err);
    process.exit(1);
});
