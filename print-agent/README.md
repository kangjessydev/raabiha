# Raabiha Print Agent

Program jembatan antara browser POS dan printer Bluetooth Classic (SPP).

## Cara Kerja

```
[https://raabiha.com/pos] → ws://localhost:8765 → [Print Agent] → [Printer BT]
```

## Setup Linux (untuk developer/testing)

### 1. Install dependencies

```bash
sudo apt install bluez bluetooth
npm install
```

### 2. Pair printer ke OS

```bash
bluetoothctl
> scan on
> pair XX:XX:XX:XX:XX:XX   # ganti dengan MAC printer
> trust XX:XX:XX:XX:XX:XX
> exit
```

### 3. Konfigurasi

Buat file `~/.raabiha-agent/config.json`:

```json
{
  "printer_mac": "XX:XX:XX:XX:XX:XX",
  "printer_name": "Xantri RPP02N",
  "serial_port": "/dev/rfcomm0",
  "ws_port": 8765
}
```

### 4. Jalankan

```bash
bash start.sh
# atau
node src/index.js
```

---

## Setup Windows (untuk PC kasir klien)

### 1. Pair printer ke Windows

- Buka **Settings > Bluetooth & devices**
- Klik "Add device" → pilih printer → pair
- Buka **Control Panel > Hardware and Sound > Devices and Printers**
- Klik kanan printer → Properties → **Services** tab → catat nama service
- Atau: Bluetooth Settings → **More Bluetooth options** → **COM Ports** tab → catat COM port **Outgoing**

### 2. Konfigurasi

Buat file `C:\Users\<username>\.raabiha-agent\config.json`:

```json
{
  "printer_mac": "",
  "printer_name": "EPPOS",
  "serial_port": "COM3",
  "ws_port": 8765
}
```

> Ganti `COM3` dengan COM port yang muncul di langkah 1.

### 3. Jalankan

Double-click `start.bat`

### 4. Auto-start saat Windows nyala (opsional, dilakukan teknisi)

```batch
:: Jalankan sebagai administrator
schtasks /create /tn "RaabihaAgent" /tr "C:\raabiha-agent\start.bat" /sc onlogon /rl highest /f
```

---

## Protocol WebSocket

Semua pesan JSON.

**Browser → Agent:**
```json
{ "type": "print", "data": "<base64 ESC/POS data>" }
{ "type": "status" }
```

**Agent → Browser:**
```json
{ "type": "status", "connected": true, "mac": "XX:XX:XX", "name": "RPP02N" }
{ "type": "print_ok", "bytes": 512 }
{ "type": "error", "message": "Printer tidak terhubung" }
```
