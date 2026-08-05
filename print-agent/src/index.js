/**
 * Raabiha Print Agent
 * Menjembatani browser POS (WebSocket) ↔ Printer Bluetooth Classic (SPP)
 *
 * Cara pakai:
 *   node src/index.js
 *
 * Tidak perlu konfigurasi manual — auto-deteksi printer Bluetooth yang sudah di-pair.
 */

const { createServer } = require('./server');
const { BluetoothPrinter } = require('./bluetooth');
const { loadConfig } = require('./config');

async function main() {
    console.log('==============================================');
    console.log('  Raabiha Print Agent v2.0.0');
    console.log('  Auto-Scan Edition');
    console.log('==============================================');

    const config = loadConfig();

    console.log(`[INFO] WebSocket port: ${config.ws_port}`);
    if (config.serial_port) {
        console.log(`[INFO] Config tersimpan: ${config.serial_port}`);
    } else {
        console.log('[INFO] Tidak ada config printer, akan auto-scan saat ada client terhubung.');
    }

    // Init Bluetooth printer handler
    const printer = new BluetoothPrinter(config);

    // Init WebSocket server
    const server = createServer(config.ws_port, printer);

    // Coba connect di startup (non-blocking)
    // Kalau gagal, akan auto-retry dan juga bisa di-trigger dari browser
    printer.connect().catch(err => {
        console.warn('[BT] Koneksi awal belum berhasil:', err.message);
        console.warn('[BT] Print Agent tetap jalan. Printer akan ditemukan saat browser klik Sambungkan.');
    });

    // Graceful shutdown
    process.on('SIGINT', async () => {
        console.log('\n[INFO] Mematikan agent...');
        try { await printer.disconnect(); } catch (e) {}
        server.close();
        process.exit(0);
    });

    process.on('SIGTERM', async () => {
        try { await printer.disconnect(); } catch (e) {}
        server.close();
        process.exit(0);
    });
}

main().catch(err => {
    console.error('[FATAL]', err);
    process.exit(1);
});
