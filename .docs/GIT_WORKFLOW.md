# Git Workflow & Syarat Pengembangan

Dalam proyek Raabiha E-Commerce ini, kita menerapkan alur kerja Git yang ketat untuk memastikan stabilitas kode (terutama setelah migrasi ke Laravel/Livewire).

## Aturan Branching

1. **`main`**: Branch utama yang selalu stabil. Hanya di-update melalui merge yang sudah diuji secara menyeluruh.
2. **`development`**: Branch integrasi utama. Semua kode baru harus berpusat di branch ini sebelum rilis.
3. **`feature/*`** atau **`fix/*`**: Branch pengerjaan khusus. Setiap pengembangan fitur baru atau perombakan sistem **WAJIB** dikerjakan di branch terpisah.
   - Contoh: `feature/livewire-product-detail`, `feature/cart-checkout`, `fix/filament-sidebar-sticky`.

## Prosedur Pengembangan (SOP)

Setiap kali melakukan perubahan besar (seperti mengubah static JS menjadi Livewire, atau integrasi API baru), ikuti prosedur ini:

1. **Mulai dari Development**: 
   Pastikan berada di branch `development`.
   ```bash
   git checkout development
   ```
2. **Buat Branch Fitur Baru**:
   Gunakan penamaan yang logis dan jelas.
   ```bash
   git checkout -b feature/<nama-fitur>
   ```
3. **Kerjakan dan Uji (Iterasi)**:
   - Lakukan koding dan perubahan file.
   - Jika terjadi error yang sulit di-revert atau berantakan, kembalilah ke development (`git checkout development`), hapus branch fitur yang rusak (`git branch -D feature/<nama-fitur>`), dan ulangi dari langkah 2.
4. **Commit & Push (Opsional)**:
   Jika fitur selesai dan berjalan normal di lokal, lakukan commit dengan pesan yang deskriptif.
5. **Merge ke Development**:
   Jika uji coba sukses 100%, pindah kembali ke `development` lalu *merge*.
   ```bash
   git checkout development
   git merge feature/<nama-fitur>
   ```

*Aturan ini wajib ditaati oleh AI pada setiap sesi pengembangan untuk mencegah kerusakan kode akibat eksperimen yang gagal (terutama terkait integrasi Livewire/Filament).*
