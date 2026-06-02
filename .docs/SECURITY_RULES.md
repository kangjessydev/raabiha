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

## 3. Aturan Keamanan Laravel & Filament (Strict Rules)
1. **CSRF Protection:** Setiap _request_ form POST/PUT/DELETE di *frontend* **wajib** menggunakan `@csrf` token bawaan Laravel.
2. **Data Validation & Sanitization:** Setiap data *input* dari pengguna ke *database* **wajib** divalidasi menggunakan **Form Requests** atau fitur validasi Livewire/Filament sebelum masuk ke model.
3. **Mass Assignment Protection:** Gunakan perlindungan mass assignment di Model (misalnya via properti `$fillable` atau `$guarded`) untuk mencegah eksploitasi injeksi field.
4. **Data Escaping:** Selalu render data di Blade menggunakan tag ganda `{{ $data }}` agar secara otomatis di-escape oleh `htmlspecialchars`.
5. **Proteksi File:** Pengunggahan media melalui kontrol Filament harus menyertakan aturan validasi tipe MIME (misalnya `image/jpeg, image/png`).
