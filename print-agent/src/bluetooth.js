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
        this.scanTarget = null;  // 'bluetooth', 'usb', atau null (generic)
        this.currentPath = null; // path port yang sedang aktif
        this.retryCount = 0;     // jumlah reconnect attempt berturut-turut
        this.MAX_RETRIES = 3;    // hentikan auto-reconnect setelah N kali gagal
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
                this.config.serial_port = portInfo.path;
                return portInfo.path;
            }
        }

        console.log('[SCAN] Tidak ada printer yang merespons di semua port.');
        return null;
    }

    /**
     * Auto-scan HANYA port Bluetooth/rfcomm.
     */
    async autoScanBluetooth() {
        console.log('[SCAN] Scanning port Bluetooth saja...');
        const fs = require('fs');
        const ports = [];

        // Linux: coba /dev/rfcomm0, rfcomm1, rfcomm2
        if (this.platform === 'linux') {
            ['/dev/rfcomm0', '/dev/rfcomm1', '/dev/rfcomm2'].forEach(p => {
                if (fs.existsSync(p)) ports.push({ path: p });
            });
        }

        // Tambah dari SerialPort.list() yang mengandung kata BT
        try {
            const allPorts = await SerialPort.list();
            const btKeywords = ['bluetooth', 'bt port', 'rfcomm', 'spp', 'serial port (spp)'];
            allPorts.forEach(p => {
                const desc = ((p.friendlyName || '') + ' ' + (p.manufacturer || '') + ' ' + (p.pnpId || '')).toLowerCase();
                if (btKeywords.some(kw => desc.includes(kw)) && !ports.find(x => x.path === p.path)) {
                    ports.push(p);
                }
            });
        } catch (e) {}

        if (ports.length === 0) {
            console.log('[SCAN] Tidak ada port Bluetooth yang ditemukan.');
            return null;
        }
        console.log('[SCAN] Kandidat port BT:', ports.map(p => p.path).join(', '));

        for (const portInfo of ports) {
            const ok = await this._tryPort(portInfo.path);
            if (ok) {
                console.log(`[SCAN] ✅ Bluetooth printer di: ${portInfo.path}`);
                this.config.serial_port = portInfo.path;
                return portInfo.path;
            }
        }
        console.log('[SCAN] Bluetooth printer tidak merespons.');
        return null;
    }

    /**
     * Auto-scan HANYA port USB printer.
     */
    async autoScanUsb() {
        console.log('[SCAN] Scanning port USB saja...');
        const fs = require('fs');

        if (this.platform === 'linux') {
            const usbPaths = ['/dev/usb/lp0', '/dev/usb/lp1', '/dev/usb/lp2'];
            for (const usbPath of usbPaths) {
                if (fs.existsSync(usbPath)) {
                    console.log(`[SCAN] ✅ USB printer di: ${usbPath}`);
                    this.config.serial_port = usbPath;
                    return usbPath;
                }
            }
            console.log('[SCAN] /dev/usb/lp* tidak ditemukan. Pastikan kabel USB terhubung.');
            return null;
        }

        // Windows: cari COM port dengan keyword USB printer
        try {
            const allPorts = await SerialPort.list();
            const usbKeywords = ['usb', 'printer', 'usbprint'];
            const usbPorts = allPorts.filter(p => {
                const desc = ((p.friendlyName || '') + ' ' + (p.manufacturer || '') + ' ' + (p.pnpId || '')).toLowerCase();
                return usbKeywords.some(kw => desc.includes(kw));
            });
            for (const portInfo of usbPorts) {
                const ok = await this._tryPort(portInfo.path);
                if (ok) {
                    console.log(`[SCAN] ✅ USB printer di: ${portInfo.path}`);
                    this.config.serial_port = portInfo.path;
                    return portInfo.path;
                }
            }
        } catch (e) {}
        console.log('[SCAN] USB printer tidak ditemukan.');
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

        const device = this._getSerialPath() || '/dev/rfcomm0';
        const devNum = device.replace('/dev/rfcomm', '') || '0';
        const fs = require('fs');

        console.log(`[BT] Binding ${mac} ke ${device}...`);
        try {
            // Lepas binding lama jika ada
            try { await execAsync(`sudo fuser -k ${device} 2>/dev/null || true`); } catch (e) {}
            try { await execAsync(`sudo rfcomm release ${devNum} 2>/dev/null || true`); } catch (e) {}

            await new Promise(r => setTimeout(r, 800));
            await execAsync(`sudo rfcomm bind ${devNum} ${mac} 1`);

            // Tunggu device muncul di /dev (kernel butuh waktu buat node-nya)
            let waited = 0;
            while (!fs.existsSync(device) && waited < 3000) {
                await new Promise(r => setTimeout(r, 200));
                waited += 200;
            }

            if (!fs.existsSync(device)) {
                throw new Error(`Device ${device} tidak muncul setelah rfcomm bind. Pastikan printer sudah di-pair dan Bluetooth aktif.`);
            }

            // chmod dengan retry karena device node kadang butuh sesaat setelah muncul
            let chmodOk = false;
            for (let i = 0; i < 3; i++) {
                try {
                    await execAsync(`sudo chmod 666 ${device}`);
                    chmodOk = true;
                    break;
                } catch (chmodErr) {
                    await new Promise(r => setTimeout(r, 300));
                }
            }
            if (!chmodOk) {
                console.warn(`[BT] chmod ${device} gagal, lanjut saja (mungkin sudah punya akses)`);
            }

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
            // Linux: bind rfcomm hanya jika target adalah Bluetooth
            if (this.platform === 'linux' && this.config.printer_mac && this.scanTarget !== 'usb') {
                await this._bindRfcomm();
                await new Promise(r => setTimeout(r, 1000));
            }

            let serialPath = this._getSerialPath();

            // Auto-scan jika belum ada path yang dikonfigurasi
            if (!serialPath) {
                let scanFn;
                if (this.scanTarget === 'bluetooth') {
                    console.log('[BT] Target Bluetooth — scan port rfcomm/BT...');
                    scanFn = this.autoScanBluetooth.bind(this);
                } else if (this.scanTarget === 'usb') {
                    console.log('[USB] Target USB — scan port /dev/usb/lp*...');
                    scanFn = this.autoScanUsb.bind(this);
                } else {
                    console.log('[BT] Tidak ada target spesifik, auto-scan semua port...');
                    scanFn = this.autoScan.bind(this);
                }

                serialPath = await scanFn();

                if (!serialPath) {
                    this.connecting = false;
                    this.emit('disconnected');
                    const hint = this.scanTarget === 'usb'
                        ? 'Printer USB tidak ditemukan. Pastikan kabel USB terhubung dan printer menyala.'
                        : 'Printer Bluetooth tidak ditemukan. Pastikan printer sudah di-pair dan menyala.';
                    throw new Error(hint);
                }

                this.config.serial_port = serialPath;
                saveConfig(this.config);
                console.log(`[BT] Config disimpan: serial_port = ${serialPath}`);
            }

            this.currentPath = serialPath;
            console.log(`[SERIAL] Membuka ${serialPath} @ ${BAUD_RATE} baud...`);

            const fs = require('fs');
            if (serialPath.startsWith('/dev/usb/lp')) {
                // USB raw device — gunakan EventEmitter proper agar event close/error berfungsi
                const { EventEmitter: EvEm } = require('events');
                const usbEmitter = new EvEm();
                const stream = fs.createWriteStream(serialPath, { flags: 'a' });

                this.port = {
                    isOpen: true,
                    path: serialPath,
                    write: (data, cb) => { stream.write(data, cb); },
                    drain: (cb) => { if (cb) cb(null); },
                    close: (cb) => {
                        this.port.isOpen = false;
                        try { stream.end(); } catch (e) {}
                        usbEmitter.emit('close');
                        if (cb) cb();
                    },
                    on: (event, handler) => usbEmitter.on(event, handler),
                };

                stream.on('error', (err) => { usbEmitter.emit('error', err); });
                stream.on('close', () => {
                    if (this.port) this.port.isOpen = false;
                    usbEmitter.emit('close');
                });

                // USB lp device: tidak perlu verify write (DLE EOT bisa bingungkan printer)
                console.log('[SERIAL] ✅ USB printer terbuka!');
            } else {
                this.port = new SerialPort({
                    path: serialPath,
                    baudRate: BAUD_RATE,
                    autoOpen: false,
                });

                await new Promise((resolve, reject) => {
                    this.port.open((err) => err ? reject(err) : resolve());
                });

                // Verifikasi dengan test write (penting untuk rfcomm di Linux)
                await new Promise((resolve, reject) => {
                    const statusQuery = Buffer.from([0x10, 0x04, 0x01]);
                    this.port.write(statusQuery, (writeErr) => {
                        if (writeErr) {
                            this.port.close(() => {});
                            return reject(new Error('Printer tidak merespons (write error). Pastikan printer menyala.'));
                        }
                        // Harus di-drain untuk memastikan data benar-benar terkirim keluar dari OS buffer
                        this.port.drain((drainErr) => {
                            if (drainErr) {
                                this.port.close(() => {});
                                return reject(new Error('Printer tidak merespons (drain error). Pastikan printer menyala.'));
                            }
                            resolve();
                        });
                    });
                });

                console.log('[SERIAL] ✅ Port terbuka dan terverifikasi!');
            }

            this.connected = true;
            this.connecting = false;
            this.retryCount = 0; // reset retry counter saat sukses
            this.emit('connected');
            this._startHeartbeat();

            this.port.on('close', () => {
                console.warn('[SERIAL] Port ditutup!');
                this._stopHeartbeat();
                this.connected = false;
                this.port = null;
                this.currentPath = null;
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
    async rescan(targetPort = null) {
        console.log('[SCAN] Rescan diminta...');

        if (this.port && this.port.isOpen) {
            try { await new Promise(r => this.port.close(() => r())); } catch (e) {}
        }
        this.connected = false;
        this.connecting = false;
        this.port = null;
        this.currentPath = null;
        if (this.reconnectTimer) {
            clearTimeout(this.reconnectTimer);
            this.reconnectTimer = null;
        }

        // Jika ada targetPort → set langsung (tidak di-reset)
        // Jika tidak → reset ke '' agar connect() auto-scan sesuai scanTarget
        this.config.serial_port = targetPort || '';

        return this.connect();
    }

    _scheduleReconnect() {
        if (this.reconnectTimer) return;

        this.retryCount++;
        if (this.retryCount > this.MAX_RETRIES) {
            console.warn(`[BT] Printer tidak merespons setelah ${this.MAX_RETRIES} percobaan. Auto-reconnect dihentikan.`);
            console.warn('[BT] Silakan hubungkan kembali secara manual dari halaman POS.');
            this.retryCount = 0; // reset agar bisa coba lagi dari UI
            return;
        }

        console.log(`[BT] Retry ${this.retryCount}/${this.MAX_RETRIES} dalam ${RECONNECT_DELAY_MS / 1000}s...`);
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
                                if (this.port && this.port.isOpen) this.port.close();
                                return reject(new Error('Write gagal: ' + err.message));
                            }
                            this.port.drain((drainErr) => {
                                if (drainErr) {
                                    if (this.port && this.port.isOpen) this.port.close();
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
                this.lastWriteTime = Date.now();
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
            // Hanya untuk koneksi Bluetooth (rfcomm), tidak untuk USB
            if (this.platform === 'linux' && this.config.printer_mac && this.currentPath && this.currentPath.startsWith('/dev/rfcomm')) {
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
            if (this.isWriting || (this.currentPath && this.currentPath.startsWith('/dev/usb/lp'))) return;

            // Tambahkan cooldown (tunggu 3 detik setelah print selesai sebelum ping lagi)
            if (this.lastWriteTime && Date.now() - this.lastWriteTime < 3000) return;


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
            serial_port: this.currentPath || this.config.serial_port || null,
            scanTarget: this.scanTarget || null,
            platform: this.platform,
        };
    }

    /**
     * List semua perangkat Bluetooth yang sudah di-pair di sistem.
     * Linux: parse `bluetoothctl devices`
     * Windows: parse list dari SerialPort dengan keyword BT
     * @returns {Promise<Array<{mac: string, name: string}>>}
     */
    async listPairedDevices() {
        if (this.platform === 'linux') {
            try {
                const { stdout } = await execAsync('bluetoothctl devices 2>/dev/null');
                const devices = [];
                stdout.split('\n').forEach(line => {
                    const match = line.match(/^Device\s+([0-9A-Fa-f:]{17})\s+(.+)$/);
                    if (match) {
                        devices.push({ mac: match[1].trim(), name: match[2].trim() });
                    }
                });
                return devices;
            } catch (e) {
                console.warn('[BT] Gagal list paired devices:', e.message);
                return [];
            }
        }

        // Windows: tidak ada cara mudah tanpa extra tooling,
        // kembalikan daftar port COM dengan kata BT di deskripsinya
        try {
            const ports = await SerialPort.list();
            const btKeywords = ['bluetooth', 'rfcomm', 'bt port', 'spp'];
            return ports
                .filter(p => {
                    const desc = ((p.friendlyName || '') + ' ' + (p.manufacturer || '')).toLowerCase();
                    return btKeywords.some(kw => desc.includes(kw));
                })
                .map(p => ({ mac: p.path, name: p.friendlyName || p.path }));
        } catch (e) {
            return [];
        }
    }
}

module.exports = { BluetoothPrinter };
