# Deployment & Build Strategy

## 1. Lingkungan Server & Spesifikasi
- **Provider:** Domainesia
- **Spesifikasi:** VPS Lite (2 Core CPU, 2 GB RAM, 20 GB SSD)
- **Sistem Operasi:** Ubuntu
- **Control Panel:** CloudPanel

## 2. Struktur Domain & Branching
Sistem akan di-deploy ke dua lingkungan (*environment*) yang berbeda:
1. **Staging (`raabiha.web.id`):** 
   - Terhubung dengan branch `development`.
   - Digunakan untuk uji coba (UAT) sebelum rilis publik.
   - Menggunakan API Keys (Xendit/RajaOngkir) mode **Sandbox/Test**.
2. **Production (`raabiha.com`):**
   - Terhubung dengan branch `main`.
   - Hanya menerima kode stabil dari *staging*.
   - Menggunakan API Keys mode **Live**.

## 3. Alur Pembaruan (Update Process via CloudPanel)
Pembaruan kode dilakukan melalui sinkronisasi Git (Git Pull) di masing-masing direktori *vhost* CloudPanel.
**Proses Build & Optimize:** Setiap rilis kode baru wajib menjalankan perintah kompilasi:
```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
npm ci && npm run build
```

## 4. Strategi Backup Otomatis (Cron Job)
Karena kapasitas *disk* terbatas (20GB), *backup* harus diatur menggunakan **Cron Job** bawaan CloudPanel:
1. **Backup Database Harian (Midnight):**
   ```bash
   0 0 * * * /usr/bin/mysqldump -u [db_user] -p[db_pass] [db_name] | gzip > /path/to/backup/db_backup_$(date +\%Y\%m\%d).sql.gz
   ```
2. **Rotasi File:** Menghapus backup yang usianya lebih dari 7 hari secara otomatis agar disk tidak penuh.
3. **Pembersihan Log:** Menjalankan rotasi log server CloudPanel secara rutin.
