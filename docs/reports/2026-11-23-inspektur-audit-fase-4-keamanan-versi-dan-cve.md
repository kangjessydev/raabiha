# Laporan: Inspektur — Audit Forensik Fase 4 (Versi PHP, Laravel, Dependensi & Indikasi CVE/Malware)

- **Tanggal:** 2026-11-23
- **Mode:** Audit-Only (100% Read-Only — Tidak mengubah satu baris kode atau paket pun)
- **Cakupan Fase 4:** Verifikasi objektif versi PHP/Laravel/Filament/Livewire/Curator, pemindaian CVE resmi (`composer audit` & `npm audit`), dan pemindaian indikasi webshell/malware di kode lokal.

---

## 1. Versi Stack Terpasang (Bukti `composer.lock` & CLI Lokal)
- **PHP Lokal:** `8.4.1` (`composer.json` mensyaratkan `"php": "^8.3"`)
- **`laravel/framework`:** `v13.12.0`
- **`filament/filament`:** `v5.6.6`
- **`livewire/livewire`:** `v4.3.1`
- **`awcodes/filament-curator`:** `v5.0.7`
- **`spatie/laravel-permission`:** `7.4.2`
- **`symfony/http-foundation`, `http-kernel`, `routing`:** `v8.1.0`
- **`league/commonmark`:** `2.8.2`

---

## 2. Hasil Resmi `composer audit` (49 Security Advisories di 15 Paket)
> **Kesimpulan Objektif:** Klaim sebelumnya bahwa versi stack *"tidak masalah"* **tidak akurat**. Berdasarkan `composer audit` per 2026-11-23, `composer.lock` proyek ini memiliki **49 advisory keamanan** (1 Critical, 13 High, 21 Medium, 4 Low, 10 Unrated/Symfony):

### [CRITICAL — 1 Advisory]
- **`laravel/framework` (`v13.12.0`) — `CVE-2026-114509` (2026-10-23):**
  *Laravel TokenGuard authentication bypass through non-string API credentials.*

### [HIGH — 13 Advisories]
- **`filament/filament` (`v5.6.6`) — `CVE-2026-77567`:** MFA bypass when recovery codes are enabled.
- **`symfony/http-foundation` (`v8.1.0`) — `CVE-2026-108082`:** ReDoS in `HeaderUtils::split()` reachable through `Cache-Control` header.
- **`ueberdosis/tiptap-php` — `CVE-2026-47110`:** Input validation vulnerability resulting in DoS *(dipakai oleh RichEditor Filament)*.
- **`guzzlehttp/guzzle` — `CVE-2026-69246`:** Noncanonical host can bypass host-based checks.
- **`league/commonmark` (`2.8.2`) — 9 advisories (termasuk `CVE-2026-71488` & `PKSA-kr3s-894t-g5w2`):** XSS bypass dan Quadratic-time DoS pada parser Markdown.

### [MEDIUM — 21 Advisories (Sorotan yang Relevan Langsung)]
- **`awcodes/filament-curator` (`v5.0.7`) — `CVE-2026-69241` (2026-11-17):** *Stored XSS via unsanitized SVG upload served inline* & **`CVE-2026-63389`:** *Cross-tenant media metadata disclosure*.
- **`livewire/livewire` (`v4.3.1`) — `CVE-2026-81887`:** *DOM-based XSS during client-side state handling* & **`PKSA-xhv3-nr94-h8db`:** *Authorization bypass in `#[Authorize]`*.
- **`laravel/framework` (`v13.12.0`) — `CVE-2026-114508` (2026-10-23):** *Relationship inclusion can trigger unauthorized data destruction*.
- **`symfony/mime` — `CVE-2026-45070`:** *Email Header Injection*.
- Serta beberapa advisory di `guzzlehttp/guzzle`, `guzzlehttp/psr7`, `filament/filament`, dan `league/commonmark`.

### [LOW & UNRATED — 14 Advisories]
- Termasuk 9 patch keamanan Symfony 8.1 (`symfony/http-foundation`, `http-kernel`, `routing`, `html-sanitizer`) tertanggal 2026-11-04 dan 2 advisory Low di `laravel/framework`.

---

## 3. Hasil Resmi `npm audit --omit=dev`
- **4 High Severity Vulnerabilities:** Seluruhnya berasal dari rantai dependensi `puppeteer` ➔ `chromium-bidi` ➔ `zod < 4.6.6` (`GHSA-mrhx-c7xc-m3w5` — memory exhaustion).

---

## 4. Pemindaian Indikasi Malware / Webshell (Kode Lokal)
- **Daftar file `.php` di `public/`:** Hanya ada `public/index.php` (543 bytes, normal). Tidak ditemukan file PHP asing/webshell.
- **Pola eksekusi berbahaya (`eval(`, `base64_decode(`, `shell_exec(`, `passthru(`, `system(`):** Tidak ditemukan di `public/`, `bootstrap/`, maupun `routes/`.
- **Keterkaitan dengan Keluhan Gambar & Kanal:** Masalah gambar putus, varian tidak nyambung, dan kanal POS/E-Commerce bocor **terbukti 100% disebabkan bug logika kode internal (Fase 1–3), bukan malware**.

---

## 5. Belum Terverifikasi (Transparansi Batas Audit Lokal)
- Versi PHP aktual yang berjalan di server produksi VPS (`202.155.157.95`) dan pemindaian file asing langsung di direktori `storage/app/public/` atau crontab VPS **belum diverifikasi** karena fase ini bersifat audit lokal Read-Only tanpa koneksi SSH.

---

## Status Fase
- **Fase 4 Selesai** (100% Read-Only).
- **Fase Terakhir:** **Fase 5 — Ringkasan Efek Samping, Peta Duplikasi, & Daftar File Gemuk (> 300 Baris)** + penyusunan Roadmap Perbaikan Terpadu untuk bahan diskusi/eksekusi nanti.
