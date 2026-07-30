/**
 * Bluetooth Classic SPP Handler
 * 
 * Linux:   Bind ke /dev/rfcomm0 via `rfcomm bind`, lalu pakai serialport
 * Windows: Pair via Bluetooth Settings → muncul COM port → pakai serialport
 * 
 * Pendekatan ini lebih stabil daripada native Bluetooth library
 * karena menggunakan OS Bluetooth stack langsung.
 */

const EventEmitter = require('events');
const { exec } = require('child_process');
const { promisify } = require('util');
const execAsync = promisify(exec);
const { SerialPort } = require('serialport');
const os = require('os');

const RECONNECT_DELAY_MS = 5000;
const BAUD_RATE = 9600; // Standard SPP baud rate untuk thermal printer

class BluetoothPrinter extends EventEmitter {
    constructor(config) {
        super();
        this.config = config;
        this.port = null;
        this.connected = false;
        this.connecting = false;
        this.reconnectTimer = null;
        this.platform = os.platform(); // 'linux', 'win32', 'darwin'
    }

    /**
     * Tentukan path serial port berdasarkan platform
     * Linux: /dev/rfcomm0 (setelah bind)
     * Windows: COM3, COM4, dsb (dari config)
     */
    _getSerialPath() {
        if (this.platform === 'win32') {
            // Windows: COM port yang diset di config atau scan otomatis
            return this.config.serial_port || null;
        } else {
            // Linux/Mac: rfcomm device
            return this.config.serial_port || '/dev/rfcomm0';
        }
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
            // Release & kill semak-semak file descriptor yang menggantung
            try {
                await execAsync(`sudo fuser -k ${device} 2>/dev/null || true`);
            } catch (e) {}
            try {
                await execAsync(`sudo rfcomm release ${devNum} 2>/dev/null || true`);
            } catch (e) {}

            // Tunggu 800ms agar kernel selesai melepaskan socket
            await new Promise(r => setTimeout(r, 800));

            // Bind
            await execAsync(`sudo rfcomm bind ${devNum} ${mac} 1`);

            // Beri izin baca/tulis ke port agar nodejs bisa akses tanpa sudo
            await execAsync(`sudo chmod 666 ${device}`);
            console.log(`[BT] ✅ rfcomm bind berhasil: ${device}`);

            // Set port ke mode raw agar kernel tidak merusak byte ESC/POS
            try {
                await execAsync(`sudo stty -F ${device} raw -echo -echoe -echok`);
            } catch (sttyErr) {
                console.warn(`[BT] stty warning (non-fatal): ${sttyErr.message.split('\n')[0]}`);
            }
        } catch (err) {
            console.error('[BT] rfcomm bind gagal:', err.message);
            console.error('[BT] Pastikan bluez-utils terinstall: sudo apt install bluez');
            throw err;
        }
    }

    /**
     * Connect ke printer
     */
    async connect() {
        if (this.connecting || this.connected) return;
        this.connecting = true;

        try {
            // Linux: bind rfcomm dulu
            if (this.platform === 'linux' && this.config.printer_mac) {
                await this._bindRfcomm();
                // Tunggu sebentar agar rfcomm siap
                await new Promise(r => setTimeout(r, 1000));
            }

            const serialPath = this._getSerialPath();
            if (!serialPath) {
                throw new Error('Serial port tidak dikonfigurasi. Set serial_port di config.json (contoh: COM3 untuk Windows)');
            }

            console.log(`[SERIAL] Membuka ${serialPath} @ ${BAUD_RATE} baud...`);

            this.port = new SerialPort({
                path: serialPath,
                baudRate: BAUD_RATE,
                autoOpen: false,
            });

            await new Promise((resolve, reject) => {
                this.port.open((err) => {
                    if (err) {
                        reject(err);
                    } else {
                        resolve();
                    }
                });
            });

            console.log('[SERIAL] ✅ Port terbuka!');
            this.connected = true;
            this.connecting = false;
            this.emit('connected');

            this.port.on('close', () => {
                console.warn('[SERIAL] Port ditutup!');
                this.connected = false;
                this.port = null;
                this.emit('disconnected');
                this._scheduleReconnect();
            });

            this.port.on('error', (err) => {
                console.error('[SERIAL] Error:', err.message);
            });

        } catch (err) {
            console.error('[BT] Gagal connect:', err.message);
            this.connecting = false;
            this._scheduleReconnect();
            throw err;
        }
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
     * Kirim data ESC/POS ke printer
     * @param {Buffer} data
     * @returns {Promise<void>}
     */
    async write(data) {
        if (!this.connected || !this.port) {
            throw new Error('Printer tidak terhubung');
        }
        
        // Mutex/Queue agar print job tidak saling tindih (overlap) jika dipanggil beruntun
        if (!this.writeQueue) this.writeQueue = Promise.resolve();
        
        const currentTask = this.writeQueue.then(async () => {
            const chunkSize = 32;
            for (let i = 0; i < data.length; i += chunkSize) {
                const chunk = data.slice(i, i + chunkSize);
                await new Promise((resolve, reject) => {
                    this.port.write(chunk, (err) => {
                        if (err) return reject(new Error('Write gagal: ' + err.message));
                        
                        this.port.drain((drainErr) => {
                            if (drainErr) return reject(new Error('Drain gagal: ' + drainErr.message));
                            // Delay 50ms per 32 bytes (kecepatan aman ~640 bytes/sec)
                            // Ini sangat penting karena Bluetooth printer murah seringkali tidak punya Flow Control
                            // sehingga jika dikirim terlalu cepat, buffer internalnya meluap dan cetakan jadi garis-garis/macet.
                            setTimeout(resolve, 50);
                        });
                    });
                });
            }
            console.log(`[SERIAL] ✅ ${data.length} bytes terkirim (chunked)`);
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
            try {
                await new Promise((resolve) => this.port.close(() => resolve()));
            } catch (e) {}
        }
        // Release rfcomm (Linux)
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

    getStatus() {
        return {
            connected: this.connected,
            connecting: this.connecting,
            mac: this.config.printer_mac,
            name: this.config.printer_name,
            serial_port: this._getSerialPath(),
            platform: this.platform,
        };
    }
}

module.exports = { BluetoothPrinter };
