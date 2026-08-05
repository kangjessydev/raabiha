/**
 * WebSocket Server
 * Menerima print jobs dari browser POS
 *
 * Protocol:
 *   Client → { "type": "print", "data": "<base64 ESC/POS>" }
 *   Client → { "type": "status" }
 *   Client → { "type": "scan" }          ← baru: minta scan ulang printer
 *   Server → { "type": "status", "connected": true/false, ... }
 *   Server → { "type": "print_ok" }
 *   Server → { "type": "scan_result", "found": true/false, "port": "COM3" }
 *   Server → { "type": "error", "message": "..." }
 */

const WebSocket = require('ws');

const HEARTBEAT_INTERVAL_MS = 30000;

function createServer(port, printer) {
    const wss = new WebSocket.Server({
        port,
        // Allow connections dari semua origin (HTTPS raabiha.com + localhost)
        verifyClient: () => true
    });

    console.log(`[WS] WebSocket server berjalan di ws://localhost:${port}`);

    function broadcastStatus() {
        const status = {
            type: 'status',
            ...printer.getStatus(),
            timestamp: Date.now()
        };
        wss.clients.forEach(client => {
            if (client.readyState === WebSocket.OPEN) {
                client.send(JSON.stringify(status));
            }
        });
    }

    printer.on('connected', broadcastStatus);
    printer.on('disconnected', broadcastStatus);

    wss.on('connection', (ws, req) => {
        const origin = req.headers.origin || 'unknown';
        console.log(`[WS] Client terhubung dari: ${origin}`);

        // Kirim status langsung saat connect
        ws.send(JSON.stringify({
            type: 'status',
            ...printer.getStatus(),
            timestamp: Date.now()
        }));

        ws.isAlive = true;
        ws.on('pong', () => { ws.isAlive = true; });

        ws.on('message', async (raw) => {
            let msg;
            try {
                msg = JSON.parse(raw.toString());
            } catch {
                ws.send(JSON.stringify({ type: 'error', message: 'Format pesan tidak valid (harus JSON)' }));
                return;
            }

            console.log(`[WS] Pesan diterima: type=${msg.type}`);

            switch (msg.type) {

                case 'status':
                    ws.send(JSON.stringify({
                        type: 'status',
                        ...printer.getStatus(),
                        timestamp: Date.now()
                    }));
                    break;

                case 'config':
                    // Browser kirim MAC address atau COM port manual
                    if (!msg.value) {
                        ws.send(JSON.stringify({ type: 'error', message: 'Alamat tidak boleh kosong' }));
                        return;
                    }

                    ws.send(JSON.stringify({ type: 'scanning' }));

                    try {
                        const val = msg.value.trim();
                        const isMac = /^([0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/.test(val);

                        if (isMac) {
                            // MAC address → set untuk rfcomm bind (Linux)
                            printer.config.printer_mac = val;
                            printer.config.serial_port = ''; // reset supaya bind ulang
                            console.log(`[WS] Config manual: MAC = ${val}`);
                        } else {
                            // COM port atau serial path → langsung pakai
                            printer.config.serial_port = val;
                            printer.config.printer_mac = ''; // clear MAC supaya skip rfcomm bind
                            console.log(`[WS] Config manual: port = ${val}`);
                        }

                        await printer.rescan();

                        ws.send(JSON.stringify({
                            type: 'config_result',
                            found: printer.connected,
                            connected: printer.connected,
                            name: printer.config.printer_name || val,
                            ...printer.getStatus()
                        }));
                        broadcastStatus();
                    } catch (err) {
                        ws.send(JSON.stringify({
                            type: 'config_result',
                            found: false,
                            connected: false,
                            message: err.message
                        }));
                    }
                    break;

                case 'scan':
                    // Browser minta scan ulang printer (misal: printer baru di-pair)
                    ws.send(JSON.stringify({ type: 'scanning' }));
                    try {
                        await printer.rescan();
                        ws.send(JSON.stringify({
                            type: 'scan_result',
                            found: printer.connected,
                            port: printer.config.serial_port || null,
                            ...printer.getStatus()
                        }));
                        broadcastStatus();
                    } catch (err) {
                        ws.send(JSON.stringify({
                            type: 'scan_result',
                            found: false,
                            port: null,
                            message: err.message
                        }));
                    }
                    break;

                case 'print':
                    if (!msg.data) {
                        ws.send(JSON.stringify({ type: 'error', message: 'Data cetak kosong' }));
                        return;
                    }

                    if (!printer.connected) {
                        ws.send(JSON.stringify({ type: 'error', message: 'Printer tidak terhubung' }));
                        return;
                    }

                    try {
                        const buffer = Buffer.from(msg.data, 'base64');
                        console.log(`[WS] Mencetak ${buffer.length} bytes...`);
                        await printer.write(buffer);
                        ws.send(JSON.stringify({ type: 'print_ok', bytes: buffer.length }));
                        console.log('[WS] ✅ Cetak berhasil!');
                    } catch (err) {
                        console.error('[WS] Cetak gagal:', err.message);
                        ws.send(JSON.stringify({ type: 'error', message: 'Cetak gagal: ' + err.message }));
                    }
                    break;

                default:
                    ws.send(JSON.stringify({ type: 'error', message: 'Perintah tidak dikenal: ' + msg.type }));
            }
        });

        ws.on('close', () => console.log('[WS] Client terputus'));
        ws.on('error', (err) => console.error('[WS] Error:', err.message));
    });

    // Heartbeat check setiap 30 detik
    const heartbeat = setInterval(() => {
        wss.clients.forEach(ws => {
            if (!ws.isAlive) { ws.terminate(); return; }
            ws.isAlive = false;
            ws.ping();
        });
    }, HEARTBEAT_INTERVAL_MS);

    wss.on('close', () => clearInterval(heartbeat));

    return wss;
}

module.exports = { createServer };
