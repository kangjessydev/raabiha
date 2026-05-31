# Security & Access Control Rules

*Panduan penting yang wajib diikuti oleh sistem dan AI saat melakukan pemrograman.*

## 1. Manajemen Akses & Role (RBAC)
Sistem ini menggunakan pembagian *role* yang ekstensif. Setiap pembuatan fitur atau halaman **wajib** memeriksa kapabilitas pengguna.
Daftar Role yang ada di sistem:
1. **Administrator** (Akses Penuh)
2. **Co-Administrator** (Akses Terbatas ke sistem inti)
3. **Store Manager** (Pengelolaan operasional toko)
4. **Finance** (Pengelolaan laporan keuangan dan pencairan)
5. **Admin Kasir** (Pembuatan pesanan/Point of Sale)
6. **Stock Manager** (Manajemen inventaris barang)
7. **Customer** (Pelanggan biasa)

## 2. Autentikasi & Pendaftaran
- **Open Registration:** Sistem pendaftaran terbuka untuk umum.
- **SSO (Single Sign-On):** Mendukung login menggunakan Email standar dan **Google Login**.
- *Catatan Developer:* Pastikan endpoint API pendaftaran terlindungi dari *spam bot* (misal: dengan verifikasi token atau CAPTCHA/Akismet di *background*).

## 3. Aturan Keamanan WordPress & Vue (Strict Rules)
1. **REST API Nonce:** Setiap _request_ POST/PUT/DELETE dari Vue ke REST API WordPress **wajib** menyertakan validasi Nonce (`X-WP-Nonce`).
2. **Data Sanitization:** Setiap data *input* dari pengguna ke *database* **wajib** melalui fungsi seperti `sanitize_text_field()`, `intval()`, dll.
3. **Data Escaping:** Setiap data yang dirender ke UI (jika menggunakan PHP fallback) wajib menggunakan `esc_html()`, `esc_attr()`, atau `esc_url()`.
4. **Proteksi File:** Endpoint pengunggahan media wajib memeriksa MIME type asli (tidak hanya bergantung pada ekstensi nama file).
