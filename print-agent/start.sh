#!/bin/bash
# Raabiha Print Agent — Linux Startup Script
# Jalankan: bash start.sh
# Atau tambahkan ke autostart: cp raabiha-agent.service /etc/systemd/system/

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
AGENT_DIR="$SCRIPT_DIR"

echo "======================================"
echo "  Raabiha Print Agent"
echo "======================================"
echo ""

# Cek Node.js
if ! command -v node &> /dev/null; then
    echo "[ERROR] Node.js tidak terinstall!"
    echo "        Install: https://nodejs.org"
    exit 1
fi

# Cek dependencies
if [ ! -d "$AGENT_DIR/node_modules" ]; then
    echo "[INFO] Install dependencies..."
    cd "$AGENT_DIR" && npm install --omit=dev
fi

# Jalankan agent
echo "[INFO] Menjalankan Print Agent..."
echo "[INFO] Tekan Ctrl+C untuk berhenti"
echo ""

cd "$AGENT_DIR" && node src/index.js
