#!/bin/bash

# ==============================================================================
# Raabiha E-Commerce: Automated Database Backup & Retention Script
# ==============================================================================
# Deskripsi: Script ini membaca konfigurasi database dari file .env Laravel,
#            melakukan backup database ke folder tujuan, lalu melakukan
#            pembersihan otomatis untuk file backup yang berusia lebih dari 7 hari.
# ==============================================================================

# Lokasi direktori proyek (menunjuk ke root direktori proyek ini)
PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

# Memastikan file .env ada
if [ ! -f "$PROJECT_DIR/.env" ]; then
    echo "[ERROR] File .env tidak ditemukan di $PROJECT_DIR"
    exit 1
fi

# Load variabel dari file .env secara aman
DB_HOST=$(grep -E "^DB_HOST=" "$PROJECT_DIR/.env" | cut -d'=' -f2- | tr -d '\r"')
DB_PORT=$(grep -E "^DB_PORT=" "$PROJECT_DIR/.env" | cut -d'=' -f2- | tr -d '\r"')
DB_DATABASE=$(grep -E "^DB_DATABASE=" "$PROJECT_DIR/.env" | cut -d'=' -f2- | tr -d '\r"')
DB_USERNAME=$(grep -E "^DB_USERNAME=" "$PROJECT_DIR/.env" | cut -d'=' -f2- | tr -d '\r"')
DB_PASSWORD=$(grep -E "^DB_PASSWORD=" "$PROJECT_DIR/.env" | cut -d'=' -f2- | tr -d '\r"')

# Set default values jika kosong di .env
DB_HOST=${DB_HOST:-"127.0.0.1"}
DB_PORT=${DB_PORT:-"3306"}

# Tentukan folder tujuan backup (default: folder 'backups' di luar/sejajar root proyek atau di dalam folder storage non-public)
BACKUP_DIR="$PROJECT_DIR/storage/backups"
LOG_FILE="$BACKUP_DIR/backup.log"
RETENTION_DAYS=7

# Buat folder backup jika belum ada
mkdir -p "$BACKUP_DIR"

# File name format dengan tanggal & waktu
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/db_${DB_DATABASE}_${TIMESTAMP}.sql.gz"

echo "==================================================" >> "$LOG_FILE"
echo "Backup dimulai pada: $(date)" >> "$LOG_FILE"

# Validasi parameter database
if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
    echo "[$(date)] [ERROR] DB_DATABASE atau DB_USERNAME kosong di .env." >> "$LOG_FILE"
    exit 1
fi

# Menjalankan dump database menggunakan mysqldump dan langsung di-compress dengan gzip
# Menggunakan file konfigurasi temporer untuk password agar aman dari ps/process listing
export MYSQL_PWD="$DB_PASSWORD"
mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" | gzip > "$BACKUP_FILE"
EXIT_CODE=$?

unset MYSQL_PWD

if [ $EXIT_CODE -eq 0 ]; then
    echo "[$(date)] [SUCCESS] Backup berhasil disimpan ke: $BACKUP_FILE" >> "$LOG_FILE"
    
    # --- PROSES ROTASI / RETENSI FILE ---
    # Menghapus file backup .sql.gz di direktori backup yang usianya lebih dari $RETENTION_DAYS hari
    echo "[$(date)] Menjalankan rotasi file (menghapus backup > $RETENTION_DAYS hari)..." >> "$LOG_FILE"
    
    # Cari dan hapus
    find "$BACKUP_DIR" -name "db_${DB_DATABASE}_*.sql.gz" -type f -mtime +$RETENTION_DAYS -print -delete >> "$LOG_FILE" 2>&1
    
    echo "[$(date)] Rotasi selesai." >> "$LOG_FILE"
else
    echo "[$(date)] [ERROR] mysqldump gagal dengan exit code $EXIT_CODE" >> "$LOG_FILE"
    # Hapus file backup yang corrupt/setengah jalan jika ada
    rm -f "$BACKUP_FILE"
fi

echo "Backup selesai pada: $(date)" >> "$LOG_FILE"
echo "==================================================" >> "$LOG_FILE"
