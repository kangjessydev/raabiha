/**
 * WebSocket Server
 * Menerima print jobs dari browser POS
 * 
 * Protocol:
 *   Client kirim JSON: { "type": "print", "data": "<base64 ESC/POS>" }
 *   Client kirim JSON: { "type": "status" }
 *   Server balas JSON: { "type": "status", "connected": true/false, ... }
 *   Server balas JSON: { "type": "print_ok" }
 *   Server balas JSON: { "type": "error", "message": "..." }
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

    // Broadcast status ke semua client
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

    // Listen to printer events untuk broadcast
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

        // Heartbeat untuk keep connection alive
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
                        // Data dikirim sebagai base64
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

        ws.on('close', () => {
            console.log('[WS] Client terputus');
        });

        ws.on('error', (err) => {
            console.error('[WS] Error:', err.message);
        });
    });

    // Heartbeat check setiap 30 detik
    const heartbeat = setInterval(() => {
        wss.clients.forEach(ws => {
            if (!ws.isAlive) {
                ws.terminate();
                return;
            }
            ws.isAlive = false;
            ws.ping();
        });
    }, HEARTBEAT_INTERVAL_MS);

    wss.on('close', () => clearInterval(heartbeat));

    return wss;
}

module.exports = { createServer };
