/**
 * Bluetooth Classic SPP Handler — dengan Auto-Scan COM Port
 *
 * Tidak perlu konfigurasi manual! Auto-deteksi port Bluetooth.
 *
 * Linux:   Bind ke /dev/rfcomm0 via `rfcomm bind`, lalu pakai serialport
 * Windows: Pair printer di Bluetooth Settings → Windows buat COM port otomatis
 *          → Auto-scan deteksi COM port tersebut tanpa perlu tahu nomornya
 */

const EventEmitter = require('events');
const { exec } = require('child_process');
const { promisify } = require('util');
const execAsync = promisify(exec);
const { SerialPort } = require('serialport');
const os = require('os');
const { saveConfig } = require('./config');

const RECONNECT_DELAY_MS = 5000;
const BAUD_RATE = 9600;

class BluetoothPrinter extends EventEmitter {
    constructor(config) {
        super();
        this.config = config;
        this.port = null;
        this.connected = false;
        this.connecting = false;
        this.reconnectTimer = null;
        this.platform = os.platform();
    }

    _getSerialPath() {
        if (this.platform === 'win32') {
            return this.config.serial_port || null;
        } else {
            return this.config.serial_port || '/dev/rfcomm0';
        }
    }

    /**
     * Dapatkan daftar semua COM port yang tersedia di sistem.
     * @returns {Promise<Array>}
     */
    async listPorts() {
        try {
            const ports = await SerialPort.list();
            if (this.platform === 'linux') {
                const fs = require('fs');
                ['/dev/usb/lp0', '/dev/usb/lp1', '/dev/usb/lp2'].forEach(lp => {
                    if (fs.existsSync(lp)) {
                        ports.push({ path: lp, friendlyName: 'USB Thermal Printer (' + lp + ')' });
                    }
                });
            }
            return ports;
        } catch (err) {
            console.error('[SCAN] Gagal list port:', err.message);
            return [];
        }
    }

    /**
     * Auto-scan semua COM port yang tersedia, cari yang punya
     * deskripsi mengandung kata Bluetooth/BT/RFCOMM.
     * Kalau tidak ditemukan lewat deskripsi, coba semua port satu per satu.
     * @returns {Promise<string|null>} path port yang berhasil
     */
    async autoScan() {
        console.log('[SCAN] Memulai auto-scan port printer...');
        const ports = await this.listPorts();

        if (ports.length === 0) {
            console.log('[SCAN] Tidak ada port serial yang ditemukan.');
            return null;
        }

        console.log(`[SCAN] Ditemukan ${ports.length} port:`);
        ports.forEach(p => console.log(`  - ${p.path} | ${p.friendlyName || p.manufacturer || 'unknown'}`));

        // Prioritas 1: port yang deskripsinya mengandung kata BT/Bluetooth
        const btKeywords = ['bluetooth', 'bt port', 'rfcomm', 'spp', 'serial port (spp)'];
        const btPorts = ports.filter(p => {
            const desc = ((p.friendlyName || '') + ' ' + (p.manufacturer || '') + ' ' + (p.pnpId || '')).toLowerCase();
            return btKeywords.some(kw => desc.includes(kw));
        });

        // Urutan coba: BT port dulu, baru sisanya
        const portsToTry = btPorts.length > 0
            ? [...btPorts, ...ports.filter(p => !btPorts.includes(p))]
            : ports;

        if (btPorts.length > 0) {
            console.log('[SCAN] Port Bluetooth kandidat:', btPorts.map(p => p.path).join(', '));
        }

        // Coba satu per satu
        for (const portInfo of portsToTry) {
            const ok = await this._tryPort(portInfo.path);
            if (ok) {
                console.log(`[SCAN] ✅ Printer ditemukan di: ${portInfo.path}`);
                return portInfo.path;
            }
        }

        console.log('[SCAN] Tidak ada printer yang merespons di semua port.');
        return null;
    }

    /**
     * Coba buka port dan tulis data ke sana.
     * Kalau bisa dibuka dan ditulis = printer ada di sini.
     * @param {string} path
     * @returns {Promise<boolean>}
     */
    _tryPort(path) {
        return new Promise((resolve) => {
            if (path.startsWith('/dev/usb/lp')) {
                const fs = require('fs');
                resolve(fs.existsSync(path));
                return;
            }

            const timeout = setTimeout(() => {
                try { testPort.close(); } catch (e) {}
                resolve(false);
            }, 1500);

            let testPort;
            try {
                testPort = new SerialPort({ path, baudRate: BAUD_RATE, autoOpen: false });
            } catch (e) {
                clearTimeout(timeout);
                resolve(false);
                return;
            }

            testPort.open((err) => {
                if (err) {
                    clearTimeout(timeout);
                    resolve(false);
                    return;
                }
                // Kirim DLE EOT — ESC/POS real-time status query (harmless)
                const statusQuery = Buffer.from([0x10, 0x04, 0x01]);
                testPort.write(statusQuery, (writeErr) => {
                    clearTimeout(timeout);
                    testPort.close(() => {});
                    resolve(!writeErr);
                });
            });
        });
    }

    /**
     * Bind Bluetooth printer ke rfcomm device (Linux only)
     */
    async _bindRfcomm() {
        if (this.platform !== 'linux') return;

        const mac = this.config.printer_mac;
        if (!mac) {
            console.warn('[BT] MAC address tidak ada, skip rfcomm bind');
            return;
        }

        const device = this._getSerialPath();
        const devNum = device.replace('/dev/rfcomm', '') || '0';

        console.log(`[BT] Binding ${mac} ke ${device}...`);
        try {
            try { await execAsync(`sudo fuser -k ${device} 2>/dev/null || true`); } catch (e) {}
            try { await execAsync(`sudo rfcomm release ${devNum} 2>/dev/null || true`); } catch (e) {}

            await new Promise(r => setTimeout(r, 800));
            await execAsync(`sudo rfcomm bind ${devNum} ${mac} 1`);
            await execAsync(`sudo chmod 666 ${device}`);
            console.log(`[BT] ✅ rfcomm bind berhasil: ${device}`);

            try {
                await execAsync(`sudo stty -F ${device} raw -echo -echoe -echok`);
            } catch (sttyErr) {
                console.warn(`[BT] stty warning (non-fatal): ${sttyErr.message.split('\n')[0]}`);
            }
        } catch (err) {
            console.error('[BT] rfcomm bind gagal:', err.message);
            throw err;
        }
    }

    /**
     * Connect ke printer.
     * Kalau serial_port sudah ada di config → langsung pakai.
     * Kalau belum → auto-scan semua COM port.
     */
    async connect() {
        if (this.connecting || this.connected) return;
        this.connecting = true;

        try {
            // Linux: bind rfcomm dulu jika ada MAC
            if (this.platform === 'linux' && this.config.printer_mac) {
                await this._bindRfcomm();
                await new Promise(r => setTimeout(r, 1000));
            }

            let serialPath = this._getSerialPath();

            // Auto-scan jika belum ada path yang dikonfigurasi
            if (!serialPath) {
                console.log('[BT] Tidak ada serial_port di config, memulai auto-scan...');
                serialPath = await this.autoScan();

                if (!serialPath) {
                    this.connecting = false;
                    this.emit('disconnected');
                    throw new Error('Printer tidak ditemukan. Pastikan printer sudah di-pair di Bluetooth Settings dan dalam keadaan menyala.');
                }

                // Simpan ke config agar startup berikutnya langsung konek
                this.config.serial_port = serialPath;
                saveConfig(this.config);
                console.log(`[BT] Config otomatis disimpan: serial_port = ${serialPath}`);
            }

            console.log(`[SERIAL] Membuka ${serialPath} @ ${BAUD_RATE} baud...`);

            const fs = require('fs');
            if (serialPath.startsWith('/dev/usb/lp')) {
                const stream = fs.createWriteStream(serialPath, { flags: 'a' });
                this.port = {
                    isOpen: true,
                    write: (data, cb) => {
                        stream.write(data, cb);
                    },
                    drain: (cb) => {
                        if (cb) cb(null);
                    },
                    close: (cb) => {
                        try { stream.end(); } catch (e) {}
                        if (cb) cb();
                    },
                    on: () => {}
                };
            } else {
                this.port = new SerialPort({
                    path: serialPath,
                    baudRate: BAUD_RATE,
                    autoOpen: false,
                });

                await new Promise((resolve, reject) => {
                    this.port.open((err) => err ? reject(err) : resolve());
                });
            }

            // Verifikasi koneksi dengan test write (penting untuk Linux rfcomm karena open() selalu sukses)
            await new Promise((resolve, reject) => {
                const statusQuery = Buffer.from([0x10, 0x04, 0x01]);
                this.port.write(statusQuery, (err) => {
                    if (err) {
                        this.port.close(() => {});
                        reject(new Error('Printer tidak merespons. Pastikan printer menyala dan alamat benar.'));
                    } else {
                        resolve();
                    }
                });
            });

            console.log('[SERIAL] ✅ Port terbuka dan terverifikasi!');
            this.connected = true;
            this.connecting = false;
            this.emit('connected');
            this._startHeartbeat();

            this.port.on('close', () => {
                console.warn('[SERIAL] Port ditutup!');
                this._stopHeartbeat();
                this.connected = false;
                this.port = null;
                this.emit('disconnected');
                this._scheduleReconnect();
            });

            this.port.on('error', (err) => {
                console.error('[SERIAL] Error:', err.message);
                this._stopHeartbeat();
                if (this.port && this.port.isOpen) {
                    this.port.close();
                }
            });

        } catch (err) {
            console.error('[BT] Gagal connect:', err.message);
            this.connecting = false;
            this._scheduleReconnect();
            throw err;
        }
    }

    /**
     * Paksa re-scan — dipanggil dari server.js saat browser minta scan ulang
     * (misal: printer baru di-pair, atau ganti printer)
     */
    async rescan() {
        console.log('[SCAN] Rescan diminta...');
        // Reset path agar connect() akan scan ulang
        this.config.serial_port = '';

        if (this.port && this.port.isOpen) {
            try { await new Promise(r => this.port.close(() => r())); } catch (e) {}
        }
        this.connected = false;
        this.connecting = false;
        this.port = null;
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }

        return this.connect();
    }

    _scheduleReconnect() {
        if (this.reconnectTimer) return;
        console.log(`[BT] Retry dalam ${RECONNECT_DELAY_MS / 1000}s...`);
        this.reconnectTimer = setTimeout(() => {
            this.reconnectTimer = null;
            this.connect().catch(() => {});
        }, RECONNECT_DELAY_MS);
    }

    /**
     * Kirim data ESC/POS ke printer (chunked untuk printer murah tanpa flow control)
     */
    async write(data) {
        if (!this.connected || !this.port) {
            throw new Error('Printer tidak terhubung');
        }

        if (!this.writeQueue) this.writeQueue = Promise.resolve();

        const currentTask = this.writeQueue.then(async () => {
            this.isWriting = true;
            try {
                const chunkSize = 1024; // 1 KB
                
                for (let i = 0; i < data.length; i += chunkSize) {
                    const chunk = data.slice(i, i + chunkSize);
                    
                    await new Promise((resolve, reject) => {
                        this.port.write(chunk, (err) => {
                            if (err) {
                                if (this.port.isOpen) this.port.close();
                                return reject(new Error('Write gagal: ' + err.message));
                            }
                            this.port.drain((drainErr) => {
                                if (drainErr) {
                                    if (this.port.isOpen) this.port.close();
                                    return reject(new Error('Drain gagal: ' + drainErr.message));
                                }
                                
                                // Jika ini bukan chunk terakhir, tunggu 500ms
                                if (i + chunkSize < data.length) {
                                    setTimeout(resolve, 500);
                                } else {
                                    resolve(); // Chunk terakhir langsung selesai
                                }
                            });
                        });
                    });
                }
                console.log(`[SERIAL] ✅ ${data.length} bytes terkirim (Leaky Bucket Pacing, 1KB/500ms)`);
            } finally {
                this.isWriting = false;
            }
        });

        this.writeQueue = currentTask.catch(() => {});
        await currentTask;
    }

    async disconnect() {
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }
        if (this.port && this.port.isOpen) {
            try { await new Promise(r => this.port.close(() => r())); } catch (e) {}
        }
        if (this.platform === 'linux' && this.config.printer_mac) {
            const device = this._getSerialPath();
            const devNum = device.replace('/dev/rfcomm', '') || '0';
            try {
                await execAsync(`sudo fuser -k ${device} 2>/dev/null || true`);
                await execAsync(`sudo rfcomm release ${devNum} 2>/dev/null || true`);
            } catch (e) {}
        }
        this.connected = false;
        this.connecting = false;
    }

    _startHeartbeat() {
        this._stopHeartbeat();
        // Ping printer setiap 3 detik untuk memantau status fisik printer
        this.heartbeatTimer = setInterval(async () => {
            if (!this.connected || !this.port) return;

            // Di Linux, tanyakan langsung ke kernel Bluetooth via `rfcomm info`
            if (this.platform === 'linux' && this.config.printer_mac) {
                const device = this._getSerialPath();
                const devNum = device.replace('/dev/rfcomm', '') || '0';
                try {
                    const { stdout } = await execAsync(`rfcomm info ${devNum} 2>&1 || true`);
                    // Jika output rfcomm info mengandung 'closed' atau tidak terhubung
                    if (stdout.includes('closed') || !stdout.includes('connected')) {
                        console.warn('[BT Heartbeat] Kernel melapor printer terputus (rfcomm closed)!');
                        this._stopHeartbeat();
                        if (this.port && this.port.isOpen) {
                            try { this.port.close(); } catch (e) {}
                        }
                        this.connected = false;
                        this.port = null;
                        this.emit('disconnected');
                        return;
                    }
                } catch (e) {}
            }

            // Jika sedang ada proses cetak aktif atau menggunakan USB raw character device, lewatkan write ping
            if (this.isWriting || this._getSerialPath().startsWith('/dev/usb/lp')) return;

            const pingBuf = Buffer.from([0x10, 0x04, 0x01]);
            this.port.write(pingBuf, (err) => {
                if (err) {
                    console.warn('[BT Heartbeat] Printer terputus:', err.message);
                    this._stopHeartbeat();
                    if (this.port && this.port.isOpen) {
                        try { this.port.close(); } catch (e) {}
                    }
                    this.connected = false;
                    this.port = null;
                    this.emit('disconnected');
                }
            });
        }, 3000);
    }

    _stopHeartbeat() {
        if (this.heartbeatTimer) {
            clearInterval(this.heartbeatTimer);
            this.heartbeatTimer = null;
        }
    }

    getStatus() {
        return {
            connected: this.connected,
            connecting: this.connecting,
            mac: this.config.printer_mac || null,
            name: this.config.printer_name || null,
            serial_port: this.config.serial_port || null,
            platform: this.platform,
        };
    }
}

module.exports = { BluetoothPrinter };
