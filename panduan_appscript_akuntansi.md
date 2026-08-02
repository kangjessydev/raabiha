# Google Apps Script — Akuntansi Raabiha (Desain Ulang)
**Toko Baju & Konveksi | Multi-Channel: POS, Web, Shopee, Lazada, Tokopedia, TikTok Shop**

Versi ini dirancang ulang dari nol dengan dua tujuan:
1. **Langsung pakai** sebagai alat akuntansi semi-otomatis di Google Sheets
2. **Blueprint migrasi** — struktur kolom mencerminkan skema database untuk modul web Laravel/Filament nantinya

---

## Arsitektur Sheet (12 Sheet, Bukan 29)

| # | Sheet | Diisi Oleh | Keterangan |
|---|---|---|---|
| 1 | **Dashboard** | Otomatis | KPI + ringkasan per channel |
| 2 | **COA** | Setup awal | Daftar kode akun — jarang diubah |
| 3 | **INPUT: Penjualan** | Akuntan | Rekap penjualan per channel & tanggal |
| 4 | **INPUT: Pembelian & Produksi** | Akuntan | Beli bahan baku, restok, biaya konveksi |
| 5 | **INPUT: Beban & Kas Keluar** | Akuntan | Gaji, listrik, sewa, iklan, dll |
| 6 | **INPUT: Jurnal Penyesuaian** | Akuntan | Penyusutan, koreksi, stok opname |
| 7 | **Buku Besar** | Otomatis | Rincian mutasi per akun |
| 8 | **Laba Rugi** | Otomatis | P&L + breakdown per channel |
| 9 | **Posisi Keuangan** | Otomatis | Neraca Aset vs Pasiva |
| 10 | **Arus Kas** | Otomatis | Cash Flow Statement |
| 11 | **Perubahan Ekuitas** | Otomatis | Laporan perubahan modal |
| 12 | **Kalkulator Pajak** | Otomatis | PPh Final UMKM 0,5% per bulan |

---

## Petunjuk Penggunaan

1. Buka [Google Sheets](https://sheets.google.com) baru, beri judul **Akuntansi Raabiha 2026**
2. Klik **Ekstensi** → **Apps Script**
3. Hapus semua kode bawaan, **paste** seluruh kode di bawah ini
4. **Simpan**, pilih fungsi **`setupAkuntansiRaabiha`**, klik **Jalankan (Run)**
5. Berikan izin akses, tunggu hingga selesai

---

## Kode Google Apps Script

```javascript
/**
 * Sistem Akuntansi Semi-Otomatis — Raabiha Clothing & Konveksi
 * Multi-Channel: POS Toko | Web Raabiha | Shopee | Lazada | Tokopedia | TikTok Shop
 *
 * Dirancang sebagai:
 *   1. Alat akuntansi mandiri di Google Sheets
 *   2. Blueprint skema database untuk modul web Laravel/Filament
 *
 * Kolom di sheet INPUT mencerminkan kolom tabel di database.
 * Kode akun COA akan menjadi primary key tabel chart_of_accounts.
 */

// ─────────────────────────────────────────────────────────
// KONSTANTA
// ─────────────────────────────────────────────────────────

const C = {
  // Warna utama
  INK:       '#0f172a',   // latar header gelap
  INK2:      '#1e293b',   // latar header gelap sekunder
  WHITE:     '#ffffff',
  SLATE:     '#94a3b8',   // teks subjudul
  MUTED:     '#64748b',   // teks keterangan

  // Warna per channel (untuk identifikasi visual)
  POS:       '#065f46',   // hijau tua — POS toko
  WEB:       '#1e3a8a',   // biru tua — web Raabiha
  SHOPEE:    '#7c2d12',   // oranye — Shopee
  LAZADA:    '#4c1d95',   // ungu — Lazada
  TOKOPEDIA: '#14532d',   // hijau — Tokopedia
  TIKTOK:    '#0f172a',   // hitam — TikTok

  // Warna status laporan
  OK:        '#bbf7d0',   // hijau muda — laba / total positif
  WARN:      '#fef08a',   // kuning — perhatian
  ERR:       '#fecaca',   // merah muda — selisih / negatif
  GRAY:      '#f1f5f9',   // abu-abu muda — subtotal
  LGRAY:     '#e2e8f0',   // abu-abu — header seksi
};

// Channel penjualan (akan menjadi ENUM di database)
const CHANNELS = ['POS Toko', 'Web Raabiha', 'Shopee', 'Lazada', 'Tokopedia', 'TikTok Shop'];

// ─────────────────────────────────────────────────────────
// ENTRY POINT
// ─────────────────────────────────────────────────────────

function setupAkuntansiRaabiha() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();

  // Buat / bersihkan sheet dalam urutan yang diinginkan
  buildCOA(ss);
  SpreadsheetApp.flush();

  buildInputPenjualan(ss);
  buildInputPembelianProduksi(ss);
  buildInputBeban(ss);
  buildInputPenyesuaian(ss);
  buildBukuBesar(ss);
  buildLabaRugi(ss);
  buildPosisiKeuangan(ss);
  buildArusKas(ss);
  buildPerubahanEkuitas(ss);
  buildKalkulatorPajak(ss);
  buildDashboard(ss);  // terakhir agar bisa referensi semua sheet

  // Hapus sheet bawaan (Sheet1 / _tmp) jika ada dan terdapat sheet lain
  const defaultSheet = ss.getSheetByName('Sheet1');
  if (defaultSheet && ss.getSheets().length > 1) {
    ss.deleteSheet(defaultSheet);
  }
  const tmpSheet = ss.getSheetByName('_tmp');
  if (tmpSheet && ss.getSheets().length > 1) {
    ss.deleteSheet(tmpSheet);
  }

  // Aktifkan Dashboard
  ss.setActiveSheet(ss.getSheetByName('Dashboard'));

  SpreadsheetApp.getUi().alert(
    'Selesai!\n\n' +
    'Mulai dengan:\n' +
    '1. Periksa & sesuaikan daftar akun di sheet COA\n' +
    '2. Isi penjualan harian di sheet INPUT: Penjualan\n' +
    '3. Isi pengeluaran di sheet INPUT: Beban & Kas Keluar\n\n' +
    'Semua laporan otomatis terhitung.'
  );
}

// ─────────────────────────────────────────────────────────
// HELPER
// ─────────────────────────────────────────────────────────

function makeSheet(ss, name) {
  let sh = ss.getSheetByName(name);
  if (sh) {
    sh.clear();
  } else {
    sh = ss.insertSheet(name);
  }
  return sh;
}

function title(sheet, text, rows, cols, bg) {
  const r = sheet.getRange(1, 1, rows, cols).merge();
  r.setValue(text)
   .setFontWeight('bold').setFontSize(14)
   .setHorizontalAlignment('center').setVerticalAlignment('middle')
   .setBackground(bg || C.INK).setFontColor(C.WHITE);
  sheet.setRowHeight(1, 40);
  return r;
}

function subTitle(sheet, row, text, cols) {
  sheet.getRange(row, 1, 1, cols).merge()
    .setValue(text).setFontSize(9).setFontColor(C.MUTED).setWrap(true);
}

function headerRow(sheet, row, labels, bg) {
  const r = sheet.getRange(row, 1, 1, labels.length);
  r.setValues([labels])
   .setFontWeight('bold').setBackground(bg || C.INK2).setFontColor(C.WHITE)
   .setVerticalAlignment('middle');
  sheet.setRowHeight(row, 32);
  return r;
}

function setRupiah(range) {
  range.setNumberFormat('#,##0');
}

function setPersen(range) {
  range.setNumberFormat('0.00%');
}

function setTanggal(range) {
  range.setNumberFormat('dd/mm/yyyy');
}

function sectionHeader(sheet, row, text, cols, bg) {
  sheet.getRange(row, 1, 1, cols).merge()
    .setValue(text).setFontWeight('bold')
    .setBackground(bg || C.LGRAY).setFontColor(C.INK);
}

function totalRow(sheet, row, label, cols, formulaCol, formulaStr, bg) {
  sheet.getRange(row, 1, 1, cols).setBackground(bg || C.GRAY);
  sheet.getRange(row, 1).setValue(label).setFontWeight('bold');
  if (formulaStr) {
    sheet.getRange(row, formulaCol).setFormula(formulaStr).setFontWeight('bold')
      .setNumberFormat('#,##0').setBackground(bg || C.GRAY);
  }
}

function getCOARule(ss) {
  const shCOA = ss.getSheetByName('COA');
  const coaRange = shCOA.getRange('A4:A60');
  return SpreadsheetApp.newDataValidation()
    .requireValueInRange(coaRange, true)
    .build();
}

// ─────────────────────────────────────────────────────────
// 1. COA — CHART OF ACCOUNTS
// Akan menjadi tabel `chart_of_accounts` di database
// ─────────────────────────────────────────────────────────

function buildCOA(ss) {
  const sh = makeSheet(ss, 'COA');

  title(sh, 'DAFTAR AKUN (CHART OF ACCOUNTS)', 1, 6);
  subTitle(sh, 2, 'Kode akun ini digunakan di seluruh sheet INPUT. Jangan ubah kode akun yang sudah dipakai di jurnal.', 6);

  headerRow(sh, 3, ['kode_akun', 'nama_akun', 'kategori', 'sub_kategori', 'normal_balance', 'laporan'], C.INK2);

  const coa = [
    // ── ASET ──
    ['1-101', 'Kas Toko (POS)',               'Aset', 'Aset Lancar',   'DB', 'Neraca'],
    ['1-102', 'Kas Bank BCA',                 'Aset', 'Aset Lancar',   'DB', 'Neraca'],
    ['1-103', 'Kas Bank Mandiri',             'Aset', 'Aset Lancar',   'DB', 'Neraca'],
    ['1-104', 'Kas Bank Lainnya',             'Aset', 'Aset Lancar',   'DB', 'Neraca'],
    ['1-201', 'Piutang Shopee',               'Aset', 'Piutang Dagang','DB', 'Neraca'],
    ['1-202', 'Piutang Lazada',               'Aset', 'Piutang Dagang','DB', 'Neraca'],
    ['1-203', 'Piutang Tokopedia',            'Aset', 'Piutang Dagang','DB', 'Neraca'],
    ['1-204', 'Piutang TikTok Shop',          'Aset', 'Piutang Dagang','DB', 'Neraca'],
    ['1-205', 'Piutang Web Raabiha',          'Aset', 'Piutang Dagang','DB', 'Neraca'],
    ['1-206', 'Piutang Pelanggan Lainnya',    'Aset', 'Piutang Dagang','DB', 'Neraca'],
    ['1-301', 'Persediaan Bahan Baku',        'Aset', 'Persediaan',    'DB', 'Neraca'],
    ['1-302', 'Persediaan Barang Dalam Proses','Aset','Persediaan',    'DB', 'Neraca'],
    ['1-303', 'Persediaan Barang Jadi',       'Aset', 'Persediaan',    'DB', 'Neraca'],
    ['1-401', 'Peralatan & Mesin Jahit',      'Aset', 'Aset Tetap',    'DB', 'Neraca'],
    ['1-402', 'Akumulasi Penyusutan Peralatan','Aset','Aset Tetap',    'CR', 'Neraca'],
    ['1-403', 'Kendaraan Operasional',        'Aset', 'Aset Tetap',    'DB', 'Neraca'],
    ['1-404', 'Akumulasi Penyusutan Kendaraan','Aset','Aset Tetap',    'CR', 'Neraca'],
    // ── KEWAJIBAN ──
    ['2-101', 'Hutang Dagang / Supplier',     'Kewajiban', 'Kewajiban Lancar','CR','Neraca'],
    ['2-102', 'Hutang Bank',                  'Kewajiban', 'Kewajiban Jangka Panjang','CR','Neraca'],
    ['2-103', 'Hutang Pajak (PPh)',           'Kewajiban', 'Kewajiban Lancar','CR','Neraca'],
    // ── EKUITAS ──
    ['3-101', 'Modal Pemilik',                'Ekuitas', 'Modal',      'CR', 'Neraca'],
    ['3-102', 'Prive Pemilik',                'Ekuitas', 'Prive',      'DB', 'Neraca'],
    ['3-103', 'Saldo Laba Ditahan',           'Ekuitas', 'Retained Earnings','CR','Neraca'],
    // ── PENDAPATAN (dipisah per channel untuk analisis) ──
    ['4-101', 'Penjualan — POS Toko',         'Pendapatan', 'Penjualan','CR','Laba Rugi'],
    ['4-102', 'Penjualan — Web Raabiha',      'Pendapatan', 'Penjualan','CR','Laba Rugi'],
    ['4-103', 'Penjualan — Shopee',           'Pendapatan', 'Penjualan','CR','Laba Rugi'],
    ['4-104', 'Penjualan — Lazada',           'Pendapatan', 'Penjualan','CR','Laba Rugi'],
    ['4-105', 'Penjualan — Tokopedia',        'Pendapatan', 'Penjualan','CR','Laba Rugi'],
    ['4-106', 'Penjualan — TikTok Shop',      'Pendapatan', 'Penjualan','CR','Laba Rugi'],
    ['4-201', 'Pendapatan Jasa Konveksi',     'Pendapatan', 'Jasa',    'CR','Laba Rugi'],
    ['4-901', 'Diskon Penjualan',             'Pendapatan', 'Pengurang','DB','Laba Rugi'],
    ['4-902', 'Retur Penjualan',              'Pendapatan', 'Pengurang','DB','Laba Rugi'],
    ['4-903', 'Biaya Jasa Marketplace',       'Pendapatan', 'Pengurang','DB','Laba Rugi'],
    // ── HPP ──
    ['5-101', 'HPP — Penjualan Barang Jadi',  'HPP', 'HPP Toko',      'DB', 'Laba Rugi'],
    ['5-201', 'Biaya Bahan Baku Konveksi',    'HPP', 'Biaya Produksi','DB', 'Laba Rugi'],
    ['5-202', 'Biaya Ongkos Jahit & Potong',  'HPP', 'Biaya Produksi','DB', 'Laba Rugi'],
    ['5-203', 'Biaya Aksesoris & Packing Produksi','HPP','Biaya Produksi','DB','Laba Rugi'],
    // ── BEBAN OPERASIONAL ──
    ['6-101', 'Beban Gaji Karyawan',          'Beban', 'Operasional',  'DB', 'Laba Rugi'],
    ['6-102', 'Beban Sewa Tempat',            'Beban', 'Operasional',  'DB', 'Laba Rugi'],
    ['6-103', 'Beban Listrik, Air & Internet','Beban', 'Operasional',  'DB', 'Laba Rugi'],
    ['6-104', 'Beban Pemasaran & Iklan Digital','Beban','Operasional', 'DB', 'Laba Rugi'],
    ['6-105', 'Beban Ongkos Kirim / Kurir',   'Beban', 'Operasional',  'DB', 'Laba Rugi'],
    ['6-106', 'Beban Penyusutan Aset',        'Beban', 'Operasional',  'DB', 'Laba Rugi'],
    ['6-107', 'Beban Administrasi & Bank',    'Beban', 'Operasional',  'DB', 'Laba Rugi'],
    ['6-108', 'Beban Perlengkapan Toko',      'Beban', 'Operasional',  'DB', 'Laba Rugi'],
    ['6-109', 'Beban Lain-lain',              'Beban', 'Lain-lain',    'DB', 'Laba Rugi'],
  ];

  sh.getRange(4, 1, coa.length, 6).setValues(coa);

  // Warna per kategori
  coa.forEach((row, i) => {
    const rowNum = 4 + i;
    let bg = C.WHITE;
    if (row[0].startsWith('1-')) bg = '#f0fdf4';
    if (row[0].startsWith('2-')) bg = '#fef2f2';
    if (row[0].startsWith('3-')) bg = '#eff6ff';
    if (row[0].startsWith('4-')) bg = '#f0fdf4';
    if (row[0].startsWith('5-')) bg = '#fff7ed';
    if (row[0].startsWith('6-')) bg = '#fdf4ff';
    sh.getRange(rowNum, 1, 1, 6).setBackground(bg);
  });

  sh.setColumnWidth(1, 90);
  sh.setColumnWidth(2, 260);
  sh.setColumnWidth(3, 110);
  sh.setColumnWidth(4, 180);
  sh.setColumnWidth(5, 110);
  sh.setColumnWidth(6, 100);
}

// ─────────────────────────────────────────────────────────
// 2. INPUT: PENJUALAN
// → Untuk POS & Web: diisi otomatis oleh sistem web nanti
// → Untuk Marketplace: diisi manual akuntan per rekap settlement
// Akan menjadi tabel `sales_journal_entries` di database
// ─────────────────────────────────────────────────────────

function buildInputPenjualan(ss) {
  const sh = makeSheet(ss, 'INPUT: Penjualan');

  title(sh, 'INPUT — JURNAL PENJUALAN', 1, 12, C.INK);
  subTitle(sh, 2,
    'POS Toko & Web Raabiha: akan otomatis terisi dari sistem web. ' +
    'Marketplace (Shopee/Lazada/Tokopedia/TikTok): isi manual per rekap settlement dari dashboard marketplace. ' +
    '1 baris = 1 transaksi atau 1 rekap harian per channel.',
    12);
  sh.setRowHeight(2, 45);

  headerRow(sh, 3, [
    'tanggal',           // DATE — menjadi kolom di DB
    'no_referensi',      // VARCHAR — no. nota POS, no. order web, atau no. settlement marketplace
    'channel',           // ENUM: pos|web|shopee|lazada|tokopedia|tiktok
    'kode_akun_kas',     // FK → chart_of_accounts.kode_akun (kas/bank/piutang yang menerima)
    'nama_akun_kas',     // auto dari VLOOKUP
    'kode_akun_penjualan', // FK → chart_of_accounts.kode_akun (4-101 dst)
    'nama_akun_penjualan', // auto dari VLOOKUP
    'gross_sales',       // DECIMAL — total penjualan kotor
    'diskon',            // DECIMAL — diskon platform / voucher
    'biaya_marketplace', // DECIMAL — komisi + biaya admin + biaya iklan platform
    'net_sales',         // DECIMAL — net (auto: gross - diskon - biaya)
    'keterangan',        // TEXT
  ]);

  // Contoh baris template per channel
  const examples = [
    ['=TODAY()-1', 'POS-20260101', 'POS Toko',     '1-101', '', '4-101', '', 500000, 0, 0, '', 'Penjualan kasir harian'],
    ['=TODAY()-1', 'WEB-20260101', 'Web Raabiha',  '1-205', '', '4-102', '', 300000, 0, 0, '', 'Penjualan web Raabiha'],
    ['=TODAY()-7', 'SPE-JUN-W1',   'Shopee',       '1-201', '', '4-103', '', 2500000,150000,175000,'','Settlement Shopee minggu ke-1 Juni'],
    ['=TODAY()-7', 'LZD-JUN-W1',   'Lazada',       '1-202', '', '4-104', '', 1200000,50000,84000,'', 'Settlement Lazada minggu ke-1 Juni'],
    ['=TODAY()-7', 'TKP-JUN-W1',   'Tokopedia',    '1-203', '', '4-105', '', 950000, 20000,47500,'', 'Settlement Tokopedia minggu ke-1 Juni'],
    ['=TODAY()-7', 'TTK-JUN-W1',   'TikTok Shop',  '1-204', '', '4-106', '', 1800000,100000,126000,'','Settlement TikTok Shop minggu ke-1 Juni'],
  ];
  sh.getRange(4, 1, examples.length, 12).setValues(examples);

  // Rumus auto nama akun
  const lastDataRow = 500;
  for (let r = 4; r <= lastDataRow; r++) {
    sh.getRange(r, 5).setFormula(
      `=IF(D${r}="","",IFERROR(VLOOKUP(D${r},COA!A:B,2,FALSE),"-"))`
    );
    sh.getRange(r, 7).setFormula(
      `=IF(F${r}="","",IFERROR(VLOOKUP(F${r},COA!A:B,2,FALSE),"-"))`
    );
    sh.getRange(r, 11).setFormula(
      `=IF(H${r}="","",H${r}-I${r}-J${r})`
    );
  }

  setTanggal(sh.getRange('A4:A' + lastDataRow));
  setRupiah(sh.getRange('H4:K' + lastDataRow));

  // Validasi dropdown channel & kode akun
  const channelRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(CHANNELS, true).build();
  sh.getRange('C4:C' + lastDataRow).setDataValidation(channelRule);

  const coaRule = getCOARule(ss);
  sh.getRange('D4:D' + lastDataRow).setDataValidation(coaRule);
  sh.getRange('F4:F' + lastDataRow).setDataValidation(coaRule);

  // Baris ringkasan per channel di bawah
  const sumRow = lastDataRow + 2;
  // Extend sheet jika perlu
  if (sh.getMaxRows() < sumRow + CHANNELS.length + 2) {
    sh.insertRowsAfter(sh.getMaxRows(), sumRow + CHANNELS.length + 2 - sh.getMaxRows());
  }
  sh.getRange(sumRow, 1, 1, 12).setBackground(C.INK2).setFontColor(C.WHITE).setFontWeight('bold');
  sh.getRange(sumRow, 1).setValue('RINGKASAN PER CHANNEL');

  CHANNELS.forEach((ch, i) => {
    const r = sumRow + 1 + i;
    sh.getRange(r, 1).setValue(ch);
    sh.getRange(r, 8).setFormula(`=SUMIF(C4:C${lastDataRow};"${ch}";H4:H${lastDataRow})`).setNumberFormat('#,##0');
    sh.getRange(r, 9).setFormula(`=SUMIF(C4:C${lastDataRow};"${ch}";I4:I${lastDataRow})`).setNumberFormat('#,##0');
    sh.getRange(r, 10).setFormula(`=SUMIF(C4:C${lastDataRow};"${ch}";J4:J${lastDataRow})`).setNumberFormat('#,##0');
    sh.getRange(r, 11).setFormula(`=SUMIF(C4:C${lastDataRow};"${ch}";K4:K${lastDataRow})`).setNumberFormat('#,##0');
    sh.getRange(r, 1, 1, 12).setBackground(C.GRAY);
  });

  sh.setColumnWidth(1, 100); sh.setColumnWidth(2, 150); sh.setColumnWidth(3, 130);
  sh.setColumnWidth(4, 110); sh.setColumnWidth(5, 200); sh.setColumnWidth(6, 120);
  sh.setColumnWidth(7, 200); sh.setColumnWidths(8, 4, 130); sh.setColumnWidth(12, 250);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 3. INPUT: PEMBELIAN & PRODUKSI KONVEKSI
// Akan menjadi tabel `purchase_journal_entries` di database
// ─────────────────────────────────────────────────────────

function buildInputPembelianProduksi(ss) {
  const sh = makeSheet(ss, 'INPUT: Pembelian & Produksi');

  title(sh, 'INPUT — PEMBELIAN & BIAYA PRODUKSI KONVEKSI', 1, 10, C.SHOPEE);
  subTitle(sh, 2,
    'Gunakan untuk: (1) Beli bahan baku konveksi dari supplier, ' +
    '(2) Restok baju jadi dari supplier, ' +
    '(3) Bayar ongkos jahit/potong per batch produksi.',
    10);
  sh.setRowHeight(2, 40);

  headerRow(sh, 3, [
    'tanggal',
    'no_faktur',
    'jenis',           // ENUM: bahan_baku | barang_jadi | ongkos_jahit | aksesoris
    'supplier',
    'keterangan_item',
    'qty',
    'satuan',          // ENUM: meter | pcs | lusin | kodi | batch
    'harga_satuan',
    'total',           // auto: qty * harga_satuan
    'status_bayar',    // ENUM: tunai | hutang
    'kode_akun_debet', // akun yang bertambah: 1-301 bahan, 1-303 barang jadi, 5-202 ongkos jahit
    'nama_akun_debet', // auto VLOOKUP
    'kode_akun_kredit',// 1-101 (tunai) atau 2-101 (hutang)
    'nama_akun_kredit',// auto VLOOKUP
  ], C.SHOPEE);

  // Validasi dropdown
  const jenisRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Bahan Baku', 'Barang Jadi (Supplier)', 'Ongkos Jahit', 'Aksesoris'], true).build();
  sh.getRange('C4:C500').setDataValidation(jenisRule);

  const satuanRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['pcs', 'meter', 'lusin', 'kodi', 'roll', 'pack', 'batch'], true).build();
  sh.getRange('G4:G500').setDataValidation(satuanRule);

  const bayarRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Tunai', 'Hutang Dagang'], true).build();
  sh.getRange('J4:J500').setDataValidation(bayarRule);

  const coaRule = getCOARule(ss);
  sh.getRange('K4:K500').setDataValidation(coaRule);
  sh.getRange('M4:M500').setDataValidation(coaRule);

  // Rumus otomatis
  for (let r = 4; r <= 500; r++) {
    sh.getRange(r, 9).setFormula(`=IF(F${r}="";"";F${r}*H${r})`);
    sh.getRange(r, 12).setFormula(`=IF(K${r}="";"";IFERROR(VLOOKUP(K${r};COA!A:B;2;FALSE);"-"))`);
    sh.getRange(r, 14).setFormula(`=IF(M${r}="";"";IFERROR(VLOOKUP(M${r};COA!A:B;2;FALSE);"-"))`);
  }

  setTanggal(sh.getRange('A4:A500'));
  setRupiah(sh.getRange('H4:I500'));
  sh.setColumnWidths(1, 14, 140);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 4. INPUT: BEBAN & KAS KELUAR
// Akan menjadi tabel `expense_journal_entries` di database
// ─────────────────────────────────────────────────────────

function buildInputBeban(ss) {
  const sh = makeSheet(ss, 'INPUT: Beban & Kas Keluar');

  title(sh, 'INPUT — BEBAN OPERASIONAL & KAS KELUAR', 1, 8, C.TIKTOK);
  subTitle(sh, 2,
    'Catat semua pengeluaran operasional: gaji, sewa, listrik, iklan, kurir, dll. ' +
    '1 baris = 1 bukti/transaksi. Pilih kode akun dari COA.',
    8);
  sh.setRowHeight(2, 40);

  headerRow(sh, 3, [
    'tanggal',
    'no_bukti',
    'kode_akun_beban',     // 6-xxx dari COA
    'nama_akun_beban',     // auto VLOOKUP
    'keterangan_detail',
    'nominal',
    'sumber_kas',          // ENUM: Kas Toko | Bank BCA | Bank Mandiri
    'keterangan',
  ]);

  // Validasi dropdown sumber kas & kode akun
  const kasRule = SpreadsheetApp.newDataValidation()
    .requireValueInList(['Kas Toko (POS)', 'Kas Bank BCA', 'Kas Bank Mandiri', 'Kas Bank Lainnya'], true).build();
  sh.getRange('G4:G500').setDataValidation(kasRule);

  const coaRule = getCOARule(ss);
  sh.getRange('C4:C500').setDataValidation(coaRule);

  // Rumus nama akun
  for (let r = 4; r <= 500; r++) {
    sh.getRange(r, 4).setFormula(`=IF(C${r}="";"";IFERROR(VLOOKUP(C${r};COA!A:B;2;FALSE);"-"))`);
  }

  setTanggal(sh.getRange('A4:A500'));
  setRupiah(sh.getRange('F4:F500'));

  // Ringkasan per kategori beban (di bawah data, baris 503)
  const DATA_MAX = 500;
  const sumRow = DATA_MAX + 3;
  // Extend sheet jika belum cukup panjang
  if (sh.getMaxRows() < sumRow + 12) {
    sh.insertRowsAfter(sh.getMaxRows(), sumRow + 12 - sh.getMaxRows());
  }

  sectionHeader(sh, sumRow, 'RINGKASAN TOTAL BEBAN PER AKUN (OTOMATIS)', 8, C.INK2);
  sh.getRange(sumRow, 1, 1, 8).setFontColor(C.WHITE);

  const bebanAkuns = [
    ['6-101','Beban Gaji'],['6-102','Beban Sewa'],['6-103','Beban Listrik'],
    ['6-104','Beban Pemasaran'],['6-105','Beban Kurir'],['6-106','Beban Penyusutan'],
    ['6-107','Beban Admin & Bank'],['6-108','Beban Perlengkapan'],['6-109','Beban Lain-lain'],
  ];
  bebanAkuns.forEach(([kode, nama], i) => {
    const r = sumRow + 1 + i;
    sh.getRange(r, 1).setValue(kode);
    sh.getRange(r, 2).setValue(nama);
    sh.getRange(r, 6).setFormula(`=SUMIF(C4:C${DATA_MAX};"${kode}";F4:F${DATA_MAX})`).setNumberFormat('#,##0');
    sh.getRange(r, 1, 1, 8).setBackground(C.GRAY);
  });
  const totalR = sumRow + 1 + bebanAkuns.length;
  sh.getRange(totalR, 1, 1, 8).setBackground(C.OK).setFontWeight('bold');
  sh.getRange(totalR, 1).setValue('TOTAL SEMUA BEBAN');
  sh.getRange(totalR, 6).setFormula(`=SUM(F${sumRow+1}:F${totalR-1})`).setNumberFormat('#,##0');

  sh.setColumnWidth(1, 100); sh.setColumnWidth(2, 130); sh.setColumnWidth(3, 120);
  sh.setColumnWidth(4, 220); sh.setColumnWidth(5, 250); sh.setColumnWidth(6, 130);
  sh.setColumnWidth(7, 160); sh.setColumnWidth(8, 200);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 5. INPUT: JURNAL PENYESUAIAN (AJP)
// ─────────────────────────────────────────────────────────

function buildInputPenyesuaian(ss) {
  const sh = makeSheet(ss, 'INPUT: Jurnal Penyesuaian');

  title(sh, 'INPUT — JURNAL PENYESUAIAN (AKHIR PERIODE)', 1, 8, '#7c3aed');
  subTitle(sh, 2,
    'Diisi akhir bulan/tahun untuk: penyusutan aset, stok opname (selisih persediaan), ' +
    'biaya dibayar dimuka, reklasifikasi, dan koreksi jurnal.',
    8);
  sh.setRowHeight(2, 40);

  headerRow(sh, 3, [
    'tanggal',
    'no_ajp',
    'keterangan_penyesuaian',
    'kode_akun_debet',
    'nama_akun_debet',    // auto
    'kode_akun_kredit',
    'nama_akun_kredit',   // auto
    'nominal',
  ], '#7c3aed');

  for (let r = 4; r <= 500; r++) {
    sh.getRange(r, 5).setFormula(`=IF(D${r}="";"";IFERROR(VLOOKUP(D${r};COA!A:B;2;FALSE);"-"))`);
    sh.getRange(r, 7).setFormula(`=IF(F${r}="";"";IFERROR(VLOOKUP(F${r};COA!A:B;2;FALSE);"-"))`);
  }

  // Contoh baris penyusutan
  sh.getRange(4, 1, 1, 8).setValues([[
    new Date(), 'AJP-001',
    'Penyusutan mesin jahit bulan ini',
    '6-106', '', '1-402', '',
    500000
  ]]);

  // Validasi dropdown kode akun debet & kredit
  const coaRule = getCOARule(ss);
  sh.getRange('D4:D500').setDataValidation(coaRule);
  sh.getRange('F4:F500').setDataValidation(coaRule);

  setTanggal(sh.getRange('A4:A500'));
  setRupiah(sh.getRange('H4:H500'));
  sh.setColumnWidths(1, 8, 170);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 6. BUKU BESAR
// ─────────────────────────────────────────────────────────

function buildBukuBesar(ss) {
  const sh = makeSheet(ss, 'Buku Besar');

  title(sh, 'BUKU BESAR (GENERAL LEDGER)', 1, 7);
  sh.getRange('A2:G2').merge().setValue('Pilih kode akun di sel B3 untuk melihat mutasi akun tersebut.')
    .setFontColor(C.MUTED).setFontSize(9);

  sh.getRange('A3').setValue('PILIH KODE AKUN:').setFontWeight('bold');
  sh.getRange('B3').setValue('1-101').setBackground(C.WARN).setFontWeight('bold');
  
  // Dropdown pencarian kode akun dari COA
  const coaRule = getCOARule(ss);
  sh.getRange('B3').setDataValidation(coaRule);

  sh.getRange('C3').setValue('Nama Akun:').setFontWeight('bold');
  sh.getRange('D3').setFormula('=IFERROR(VLOOKUP(B3;COA!A:B;2;FALSE);"Kode tidak ditemukan")').setFontWeight('bold');

  headerRow(sh, 4, ['tanggal', 'no_referensi', 'keterangan', 'debet', 'kredit', 'saldo', 'sumber']);

  // QUERY gabungan dari semua sheet INPUT
  // Karena Google Sheets tidak bisa QUERY lintas sheet langsung,
  // kita gunakan FILTER pada masing-masing sheet dan gabungkan manual.
  sh.getRange('A5').setFormula(
    '=IFERROR(' +
    'QUERY({' +
    // Dari INPUT: Penjualan (channel=kode kas debet)
    '{"Penjualan",\'INPUT: Penjualan\'!A4:A1000,\'INPUT: Penjualan\'!B4:B1000,\'INPUT: Penjualan\'!C4:C1000,\'INPUT: Penjualan\'!K4:K1000,0};' +
    '{"Beban",\'INPUT: Beban & Kas Keluar\'!A4:A1000,\'INPUT: Beban & Kas Keluar\'!B4:B1000,\'INPUT: Beban & Kas Keluar\'!E4:E1000,0,\'INPUT: Beban & Kas Keluar\'!F4:F1000};' +
    '{"Penyesuaian",\'INPUT: Jurnal Penyesuaian\'!A4:A500,\'INPUT: Jurnal Penyesuaian\'!B4:B500,\'INPUT: Jurnal Penyesuaian\'!C4:C500,\'INPUT: Jurnal Penyesuaian\'!H4:H500,0}' +
    '}' +
    ',"SELECT Col2,Col3,Col4,Col5,Col6 WHERE Col2 IS NOT NULL ORDER BY Col2")' +
    ',"Belum ada data")'
  );

  sh.setColumnWidth(1, 90); sh.setColumnWidth(2, 120); sh.setColumnWidth(3, 280);
  sh.setColumnWidths(4, 3, 130); sh.setColumnWidth(7, 110);
}

// ─────────────────────────────────────────────────────────
// 7. LABA RUGI
// ─────────────────────────────────────────────────────────

function buildLabaRugi(ss) {
  const sh = makeSheet(ss, 'Laba Rugi');

  title(sh, 'LAPORAN LABA RUGI', 1, 8);
  sh.getRange('A2:H2').merge().setValue('RAABIHA CLOTHING & KONVEKSI — Periode: ' + new Date().getFullYear())
    .setHorizontalAlignment('center').setFontSize(11);

  // Header kolom: Label | POS | Web | Shopee | Lazada | Tokopedia | TikTok | TOTAL
  headerRow(sh, 3, ['Uraian', 'POS Toko', 'Web Raabiha', 'Shopee', 'Lazada', 'Tokopedia', 'TikTok Shop', 'TOTAL']);

  // Helper: SUMIF pada INPUT: Penjualan per channel
  const penjCols = { 'POS Toko': 2, 'Web Raabiha': 3, 'Shopee': 4, 'Lazada': 5, 'Tokopedia': 6, 'TikTok Shop': 7 };
  function salesFormula(channel, field) {
    // field: K=net_sales, H=gross_sales, I=diskon, J=biaya_marketplace
    const colMap = { net: 'K', gross: 'H', diskon: 'I', biaya: 'J' };
    const col = colMap[field];
    return `=SUMIF('INPUT: Penjualan'!C4:C1000,"${channel}",'INPUT: Penjualan'!${col}4:${col}1000)`;
  }
  function bebanFormula(kodeAkun) {
    return `=SUMIF('INPUT: Beban & Kas Keluar'!C4:C1000,"${kodeAkun}",'INPUT: Beban & Kas Keluar'!F4:F1000)+SUMIF('INPUT: Jurnal Penyesuaian'!D4:D500,"${kodeAkun}",'INPUT: Jurnal Penyesuaian'!H4:H500)`;
  }

  let row = 4;

  // ── PENDAPATAN ──
  sectionHeader(sh, row, 'PENDAPATAN PENJUALAN', 8, C.INK2);
  sh.getRange(row, 1, 1, 8).setFontColor(C.WHITE);
  row++;

  ['POS Toko', 'Web Raabiha', 'Shopee', 'Lazada', 'Tokopedia', 'TikTok Shop'].forEach((ch, i) => {
    sh.getRange(row, 1).setValue('  Penjualan ' + ch);
    sh.getRange(row, 2 + i).setFormula(salesFormula(ch, 'gross')).setNumberFormat('#,##0');
    sh.getRange(row, 8).setFormula(`=SUM(B${row}:G${row})`).setNumberFormat('#,##0');
    row++;
  });

  // Pendapatan Konveksi
  sh.getRange(row, 1).setValue('  Pendapatan Jasa Konveksi');
  sh.getRange(row, 8).setFormula(`=SUMIF('INPUT: Penjualan'!F4:F1000;"4-201";'INPUT: Penjualan'!K4:K1000)`).setNumberFormat('#,##0');
  row++;

  const grossRow = row;
  totalRow(sh, row, 'GROSS REVENUE', 8, 8, `=SUM(H5:H${row-1})`, C.LGRAY);
  row++;

  // Pengurang pendapatan
  sectionHeader(sh, row, 'PENGURANG PENDAPATAN', 8);
  row++;
  sh.getRange(row, 1).setValue('  Diskon Penjualan');
  CHANNELS.forEach((ch, i) => {
    sh.getRange(row, 2 + i).setFormula(salesFormula(ch, 'diskon')).setNumberFormat('#,##0');
  });
  sh.getRange(row, 8).setFormula(`=SUM(B${row}:G${row})`).setNumberFormat('#,##0');
  const diskonRow = row; row++;

  sh.getRange(row, 1).setValue('  Biaya Jasa Marketplace');
  ['POS Toko', 'Web Raabiha', 'Shopee', 'Lazada', 'Tokopedia', 'TikTok Shop'].forEach((ch, i) => {
    sh.getRange(row, 2 + i).setFormula(salesFormula(ch, 'biaya')).setNumberFormat('#,##0');
  });
  sh.getRange(row, 8).setFormula(`=SUM(B${row}:G${row})`).setNumberFormat('#,##0');
  const mktRow = row; row++;

  const netRow = row;
  totalRow(sh, row, 'NET REVENUE (PENDAPATAN BERSIH)', 8, 8,
    `=H${grossRow}-H${diskonRow}-H${mktRow}`, C.OK);
  row += 2;

  // ── HPP ──
  sectionHeader(sh, row, 'HARGA POKOK PENJUALAN & BIAYA PRODUKSI', 8, '#7c2d12');
  sh.getRange(row, 1, 1, 8).setFontColor(C.WHITE);
  row++;

  const hppItems = [
    ['  HPP Penjualan Barang Jadi', `=SUMIF('INPUT: Pembelian & Produksi'!C4:C1000,"Barang Jadi (Supplier)",'INPUT: Pembelian & Produksi'!I4:I1000)`],
    ['  Biaya Bahan Baku Konveksi', `=SUMIF('INPUT: Pembelian & Produksi'!C4:C1000,"Bahan Baku",'INPUT: Pembelian & Produksi'!I4:I1000)`],
    ['  Biaya Ongkos Jahit & Potong', `=SUMIF('INPUT: Pembelian & Produksi'!C4:C1000,"Ongkos Jahit",'INPUT: Pembelian & Produksi'!I4:I1000)`],
    ['  Biaya Aksesoris Produksi', `=SUMIF('INPUT: Pembelian & Produksi'!C4:C1000,"Aksesoris",'INPUT: Pembelian & Produksi'!I4:I1000)`],
  ];
  const hppStartRow = row;
  hppItems.forEach(([label, formula]) => {
    sh.getRange(row, 1).setValue(label);
    sh.getRange(row, 8).setFormula(formula).setNumberFormat('#,##0');
    row++;
  });
  const hppTotalRow = row;
  totalRow(sh, row, 'TOTAL HPP & BIAYA PRODUKSI', 8, 8, `=SUM(H${hppStartRow}:H${row-1})`, C.LGRAY);
  row++;

  const grossProfitRow = row;
  sh.getRange(row, 1, 1, 8).setFontWeight('bold').setBackground(C.OK).setFontSize(11);
  sh.getRange(row, 1).setValue('LABA KOTOR (GROSS PROFIT)');
  sh.getRange(row, 8).setFormula(`=H${netRow}-H${hppTotalRow}`).setNumberFormat('#,##0');
  row += 2;

  // ── BEBAN OPERASIONAL ──
  sectionHeader(sh, row, 'BEBAN OPERASIONAL', 8, C.TIKTOK);
  sh.getRange(row, 1, 1, 8).setFontColor(C.WHITE);
  row++;

  const bebanItems = [
    ['  Beban Gaji Karyawan',       bebanFormula('6-101')],
    ['  Beban Sewa Tempat',         bebanFormula('6-102')],
    ['  Beban Listrik, Air & Internet', bebanFormula('6-103')],
    ['  Beban Pemasaran & Iklan',   bebanFormula('6-104')],
    ['  Beban Ongkos Kirim / Kurir',bebanFormula('6-105')],
    ['  Beban Penyusutan Aset',     bebanFormula('6-106')],
    ['  Beban Administrasi & Bank', bebanFormula('6-107')],
    ['  Beban Perlengkapan Toko',   bebanFormula('6-108')],
    ['  Beban Lain-lain',           bebanFormula('6-109')],
  ];
  const bebanStartRow = row;
  bebanItems.forEach(([label, formula]) => {
    sh.getRange(row, 1).setValue(label);
    sh.getRange(row, 8).setFormula(formula).setNumberFormat('#,##0');
    row++;
  });
  const bebanTotalRow = row;
  totalRow(sh, row, 'TOTAL BEBAN OPERASIONAL', 8, 8, `=SUM(H${bebanStartRow}:H${row-1})`, C.LGRAY);
  row += 2;

  // ── LABA BERSIH ──
  const labaSebelumPajakRow = row;
  sh.getRange(row, 1, 1, 8).setFontWeight('bold').setBackground(C.LGRAY);
  sh.getRange(row, 1).setValue('LABA SEBELUM PAJAK');
  sh.getRange(row, 8).setFormula(`=H${grossProfitRow}-H${bebanTotalRow}`).setNumberFormat('#,##0');
  row++;

  sh.getRange(row, 1).setValue('  Beban Pajak (PPh Final 0,5% × Net Revenue)');
  sh.getRange(row, 8).setFormula(`=H${netRow}*0.005`).setNumberFormat('#,##0');
  const pajakRow = row; row++;

  sh.getRange(row, 1, 1, 8).setFontWeight('bold').setFontSize(12).setBackground(C.OK);
  sh.getRange(row, 1).setValue('LABA BERSIH (NET PROFIT)');
  sh.getRange(row, 8).setFormula(`=H${labaSebelumPajakRow}-H${pajakRow}`).setNumberFormat('#,##0');

  sh.setColumnWidth(1, 280); sh.setColumnWidths(2, 7, 130);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 8. POSISI KEUANGAN (NERACA)
// ─────────────────────────────────────────────────────────

function buildPosisiKeuangan(ss) {
  const sh = makeSheet(ss, 'Posisi Keuangan');

  title(sh, 'LAPORAN POSISI KEUANGAN (NERACA)', 1, 6);
  sh.getRange('A2:F2').merge().setValue('RAABIHA CLOTHING & KONVEKSI — Per ' + Utilities.formatDate(new Date(), 'Asia/Jakarta', 'dd MMMM yyyy'))
    .setHorizontalAlignment('center').setFontSize(11);

  // Helper SUMIF dari semua input
  function netAkun(kode, lebihDB) {
    const dbSrc = `SUMIF('INPUT: Penjualan'!D4:D1000,"${kode}",'INPUT: Penjualan'!K4:K1000)+SUMIF('INPUT: Jurnal Penyesuaian'!D4:D500,"${kode}",'INPUT: Jurnal Penyesuaian'!H4:H500)`;
    const crSrc = `SUMIF('INPUT: Penjualan'!D4:D1000,"${kode}",'INPUT: Penjualan'!K4:K1000)`;
    // Simplified: use direct sumif per relevant input
    return `=SUMIF('INPUT: Pembelian & Produksi'!K4:K1000,"${kode}",'INPUT: Pembelian & Produksi'!I4:I1000)-SUMIF('INPUT: Jurnal Penyesuaian'!F4:F500,"${kode}",'INPUT: Jurnal Penyesuaian'!H4:H500)`;
  }

  headerRow(sh, 3, ['ASET (AKTIVA)', '', 'Jumlah (Rp)', '', 'KEWAJIBAN & EKUITAS', 'Jumlah (Rp)']);

  let leftRow = 4;
  let rightRow = 4;

  const asetItems = [
    ['Aset Lancar', null, true],
    ['  Kas Toko (POS)', `=SUMIF('INPUT: Penjualan'!C4:C1000,"POS Toko",'INPUT: Penjualan'!K4:K1000)-SUMIF('INPUT: Beban & Kas Keluar'!G4:G1000,"Kas Toko (POS)",'INPUT: Beban & Kas Keluar'!F4:F1000)`],
    ['  Kas Bank BCA', `=SUMIF('INPUT: Penjualan'!D4:D1000,"1-102",'INPUT: Penjualan'!K4:K1000)-SUMIF('INPUT: Beban & Kas Keluar'!G4:G1000,"Kas Bank BCA",'INPUT: Beban & Kas Keluar'!F4:F1000)`],
    ['  Kas Bank Mandiri', `=SUMIF('INPUT: Penjualan'!D4:D1000,"1-103",'INPUT: Penjualan'!K4:K1000)-SUMIF('INPUT: Beban & Kas Keluar'!G4:G1000,"Kas Bank Mandiri",'INPUT: Beban & Kas Keluar'!F4:F1000)`],
    ['  Piutang Shopee', `=SUMIF('INPUT: Penjualan'!C4:C1000,"Shopee",'INPUT: Penjualan'!H4:H1000)-SUMIF('INPUT: Penjualan'!D4:D1000,"1-102",'INPUT: Penjualan'!K4:K1000)`],
    ['  Piutang Lazada', `=SUMIF('INPUT: Penjualan'!C4:C1000,"Lazada",'INPUT: Penjualan'!H4:H1000)-SUMIF('INPUT: Penjualan'!D4:D1000,"1-103",'INPUT: Penjualan'!K4:K1000)`],
    ['  Piutang Tokopedia', `=SUMIF('INPUT: Penjualan'!C4:C1000,"Tokopedia",'INPUT: Penjualan'!H4:H1000)`],
    ['  Piutang TikTok Shop', `=SUMIF('INPUT: Penjualan'!C4:C1000,"TikTok Shop",'INPUT: Penjualan'!H4:H1000)`],
    ['  Persediaan Bahan Baku', `=SUMIF('INPUT: Pembelian & Produksi'!C4:C1000,"Bahan Baku",'INPUT: Pembelian & Produksi'!I4:I1000)-SUMIF('INPUT: Pembelian & Produksi'!C4:C1000,"Ongkos Jahit",'INPUT: Pembelian & Produksi'!I4:I1000)`],
    ['  Persediaan Barang Jadi', `=SUMIF('INPUT: Pembelian & Produksi'!C4:C1000,"Barang Jadi (Supplier)",'INPUT: Pembelian & Produksi'!I4:I1000)`],
    ['Total Aset Lancar', null, false, true],
  ];

  asetItems.forEach((item, i) => {
    const r = 4 + i;
    sh.getRange(r, 1).setValue(item[0]);
    if (item[1]) sh.getRange(r, 3).setFormula(item[1]).setNumberFormat('#,##0');
    if (item[2]) sh.getRange(r, 1).setFontWeight('bold');
    if (item[3]) {
      sh.getRange(r, 3).setFormula(`=SUM(C5:C${r-1})`).setNumberFormat('#,##0');
      sh.getRange(r, 1, 1, 3).setFontWeight('bold').setBackground(C.LGRAY);
    }
    leftRow = r + 1;
  });

  sh.getRange(leftRow, 1).setValue('Aset Tetap').setFontWeight('bold');
  sh.getRange(leftRow + 1, 1).setValue('  Peralatan & Mesin (setelah penyusutan)');
  sh.getRange(leftRow + 1, 3).setFormula(`=SUMIF('INPUT: Pembelian & Produksi'!K4:K1000;"1-401";'INPUT: Pembelian & Produksi'!I4:I1000)-SUMIF('INPUT: Jurnal Penyesuaian'!F4:F500;"1-402";'INPUT: Jurnal Penyesuaian'!H4:H500)`).setNumberFormat('#,##0');
  const totalAsetRow = leftRow + 3;
  sh.getRange(leftRow + 2, 1).setValue('Total Aset Tetap').setFontWeight('bold');
  sh.getRange(leftRow + 2, 3).setFormula(`=C${leftRow+1}`).setNumberFormat('#,##0').setFontWeight('bold').setBackground(C.LGRAY);
  sh.getRange(leftRow + 3, 1, 1, 3).setFontWeight('bold').setFontSize(11).setBackground(C.OK);
  sh.getRange(leftRow + 3, 1).setValue('TOTAL ASET');
  sh.getRange(leftRow + 3, 3).setFormula(`=C${leftRow-1}+C${leftRow+2}`).setNumberFormat('#,##0');

  // PASIVA (kanan)
  const pItems = [
    ['Kewajiban Lancar', null, true],
    ['  Hutang Dagang / Supplier', `=SUMIF('INPUT: Pembelian & Produksi'!J4:J1000,"Hutang Dagang",'INPUT: Pembelian & Produksi'!I4:I1000)`],
    ['Total Kewajiban', null, false, true, 4, 5],
    ['', null],
    ['Ekuitas', null, true],
    ['  Modal Pemilik', '=0'],
    ['  Laba Bersih Tahun Ini', "='Laba Rugi'!H" + (sh.getLastRow() + 5)],
    ['Total Ekuitas', null, false, true, 6, 7],
    ['', null],
    ['TOTAL KEWAJIBAN & EKUITAS', null, false, false, null, null, true],
  ];

  // Sederhana: tuliskan di kolom E-F
  const pStartRow = 4;
  const pData = [
    ['Kewajiban Lancar', ''],
    ['  Hutang Dagang / Supplier', `=SUMIF('INPUT: Pembelian & Produksi'!J4:J500;"Hutang Dagang";'INPUT: Pembelian & Produksi'!I4:I500)`],
    ['Total Kewajiban', '=F5'],
    ['', ''],
    ['Ekuitas', ''],
    ['  Modal Pemilik (isi manual)', ''],
    ["  Laba Bersih Tahun Ini", "='Laba Rugi'!H40"],
    ['Total Ekuitas', '=SUM(F9:F10)'],
    ['', ''],
    ['TOTAL KEWAJIBAN & EKUITAS', '=F6+F11'],
  ];

  pData.forEach(([label, formula], i) => {
    const r = pStartRow + i;
    sh.getRange(r, 5).setValue(label);
    if (formula) sh.getRange(r, 6).setFormula(formula).setNumberFormat('#,##0');
  });

  sh.setColumnWidth(1, 240); sh.setColumnWidth(2, 20); sh.setColumnWidth(3, 150);
  sh.setColumnWidth(4, 30); sh.setColumnWidth(5, 240); sh.setColumnWidth(6, 150);
}

// ─────────────────────────────────────────────────────────
// 9. ARUS KAS
// ─────────────────────────────────────────────────────────

function buildArusKas(ss) {
  const sh = makeSheet(ss, 'Arus Kas');
  title(sh, 'LAPORAN ARUS KAS (CASH FLOW)', 1, 4);

  const items = [
    ['I. AKTIVITAS OPERASIONAL', null, true],
    ['  Penerimaan dari Penjualan (semua channel)', `=SUMIF('INPUT: Penjualan'!C4:C1000,"POS Toko",'INPUT: Penjualan'!K4:K1000)+SUMIF('INPUT: Penjualan'!D4:D1000,"1-102",'INPUT: Penjualan'!K4:K1000)+SUMIF('INPUT: Penjualan'!D4:D1000,"1-103",'INPUT: Penjualan'!K4:K1000)`],
    ['  Pembayaran Beban Operasional', `=-SUM('INPUT: Beban & Kas Keluar'!F4:F1000)`],
    ['  Pembayaran ke Supplier (tunai)', `=-SUMIF('INPUT: Pembelian & Produksi'!J4:J1000,"Tunai",'INPUT: Pembelian & Produksi'!I4:I1000)`],
    ['ARUS KAS BERSIH DARI OPERASIONAL', `=SUM(D3:D5)`, false, true],
    ['', null],
    ['II. AKTIVITAS INVESTASI', null, true],
    ['  Pembelian Aset Tetap', `=-SUMIF('INPUT: Pembelian & Produksi'!K4:K1000,"1-401",'INPUT: Pembelian & Produksi'!I4:I1000)`],
    ['ARUS KAS BERSIH DARI INVESTASI', `=D9`, false, true],
    ['', null],
    ['III. AKTIVITAS PENDANAAN', null, true],
    ['  Setoran Modal Pemilik', '=0'],
    ['  Prive / Pengambilan Pemilik', '=0'],
    ['ARUS KAS BERSIH DARI PENDANAAN', `=SUM(D13:D14)`, false, true],
    ['', null],
    ['KENAIKAN / (PENURUNAN) KAS BERSIH', `=D6+D10+D15`, false, false, true],
    ['Saldo Kas Awal Periode', ''],
    ['SALDO KAS AKHIR PERIODE', `=D17+D18`, false, false, true],
  ];

  items.forEach((item, i) => {
    const r = 3 + i;
    sh.getRange(r, 1).setValue(item[0]);
    if (item[1]) sh.getRange(r, 4).setFormula(item[1]).setNumberFormat('#,##0');
    if (item[2]) sh.getRange(r, 1, 1, 4).setFontWeight('bold').setBackground(C.INK2).setFontColor(C.WHITE);
    if (item[3]) sh.getRange(r, 1, 1, 4).setFontWeight('bold').setBackground(C.LGRAY);
    if (item[4]) sh.getRange(r, 1, 1, 4).setFontWeight('bold').setFontSize(11).setBackground(C.OK);
  });

  sh.setColumnWidth(1, 350); sh.setColumnWidths(2, 3, 30); sh.setColumnWidth(4, 150);
}

// ─────────────────────────────────────────────────────────
// 10. PERUBAHAN EKUITAS
// ─────────────────────────────────────────────────────────

function buildPerubahanEkuitas(ss) {
  const sh = makeSheet(ss, 'Perubahan Ekuitas');
  title(sh, 'LAPORAN PERUBAHAN EKUITAS', 1, 3);

  const items = [
    ['Modal Awal Periode (isi manual)', '', 0],
    ['(+) Tambahan Setoran Modal', '', 0],
    ["(+) Laba Bersih Tahun Ini", '', "='Laba Rugi'!H40"],
    ['(-) Prive / Pengambilan Pemilik', '', 0],
    ['MODAL AKHIR PERIODE', '', '=SUM(C3:C6)'],
  ];

  items.forEach((item, i) => {
    const r = 3 + i;
    sh.getRange(r, 1).setValue(item[0]);
    if (typeof item[2] === 'string' && item[2].startsWith('=')) {
      sh.getRange(r, 3).setFormula(item[2]).setNumberFormat('#,##0');
    } else {
      sh.getRange(r, 3).setValue(item[2]).setNumberFormat('#,##0');
    }
  });

  sh.getRange(7, 1, 1, 3).setFontWeight('bold').setFontSize(11).setBackground(C.OK);
  sh.setColumnWidth(1, 320); sh.setColumnWidth(2, 30); sh.setColumnWidth(3, 160);
}

// ─────────────────────────────────────────────────────────
// 11. KALKULATOR PAJAK
// ─────────────────────────────────────────────────────────

function buildKalkulatorPajak(ss) {
  const sh = makeSheet(ss, 'Kalkulator Pajak');
  title(sh, 'KALKULATOR PAJAK UMKM (PPh FINAL PP 55/2022)', 1, 5, C.INK);
  sh.getRange('A2:E2').merge()
    .setValue('Tarif 0% untuk omset ≤ Rp 500 Juta/tahun | Tarif 0,5% untuk Rp 500 Juta–Rp 4,8 Miliar/tahun')
    .setFontColor(C.MUTED).setFontSize(9).setWrap(true);
  sh.setRowHeight(2, 35);

  headerRow(sh, 3, ['Bulan', 'Omset Bulan Ini (Rp)', 'Akumulasi Omset (Rp)', 'Pajak Terhutang (Rp)', 'Status'], C.INK);

  const months = ['Januari','Februari','Maret','April','Mei','Juni',
                  'Juli','Agustus','September','Oktober','November','Desember'];
  months.forEach((m, i) => {
    const r = 4 + i;
    sh.getRange(r, 1).setValue(m);
    sh.getRange(r, 3).setFormula(`=SUM(B$4:B${r})`).setNumberFormat('#,##0');
    sh.getRange(r, 4).setFormula(`=IF(C${r}<=500000000;0;B${r}*0.005)`).setNumberFormat('#,##0');
    sh.getRange(r, 5).setFormula(`=IF(C${r}<=500000000;"Bebas pajak (< Rp 500 Jt)";"Wajib setor PPh Final 0,5%")`);
    sh.getRange(r, 1, 1, 5).setBackground(i % 2 === 0 ? C.WHITE : C.GRAY);
  });

  setRupiah(sh.getRange('B4:D15'));

  const tot = 16;
  sh.getRange(tot, 1, 1, 5).setFontWeight('bold').setBackground(C.INK2).setFontColor(C.WHITE);
  sh.getRange(tot, 1).setValue('TOTAL SETAHUN');
  sh.getRange(tot, 2).setFormula('=SUM(B4:B15)').setNumberFormat('#,##0');
  sh.getRange(tot, 3).setFormula('=B16').setNumberFormat('#,##0');
  sh.getRange(tot, 4).setFormula('=SUM(D4:D15)').setNumberFormat('#,##0');
  sh.getRange(tot, 5).setFormula('=IF(B16<=500000000;"BEBAS PAJAK";"Wajib Setor")').setFontWeight('bold');

  sh.setColumnWidths(1, 5, 200);
}

// ─────────────────────────────────────────────────────────
// 12. DASHBOARD
// ─────────────────────────────────────────────────────────

function buildDashboard(ss) {
  const sh = makeSheet(ss, 'Dashboard');

  title(sh, 'DASHBOARD AKUNTANSI RAABIHA', 1, 8);
  sh.getRange('A2:H2').merge()
    .setValue('Toko Baju & Konveksi — Multi-Channel: POS Toko | Web | Shopee | Lazada | Tokopedia | TikTok Shop')
    .setHorizontalAlignment('center').setFontSize(10).setBackground(C.INK2).setFontColor(C.SLATE);

  // ── KPI ROW ──
  const kpis = [
    ['TOTAL PENJUALAN (GROSS)', "=SUM('INPUT: Penjualan'!H4:H1000)", '#0284c7'],
    ['DISKON & BIAYA MARKETPLACE', "=SUM('INPUT: Penjualan'!I4:I1000)+SUM('INPUT: Penjualan'!J4:J1000)", '#dc2626'],
    ['NET REVENUE', "=SUM('INPUT: Penjualan'!K4:K1000)", '#16a34a'],
    ['TOTAL BEBAN OPERASIONAL', "=SUM('INPUT: Beban & Kas Keluar'!F4:F1000)", '#9333ea'],
    ['LABA BERSIH', "='Laba Rugi'!H40", '#d97706'],
    ['PAJAK TERHUTANG', "='Kalkulator Pajak'!D16", '#0f172a'],
  ];

  kpis.forEach((kpi, i) => {
    const col = 1 + (i * 1);
    sh.getRange(4, i + 1).setValue(kpi[0]).setFontSize(8).setFontWeight('bold').setFontColor('#64748b').setWrap(true);
    sh.getRange(5, i + 1).setFormula(kpi[1]).setNumberFormat('#,##0').setFontWeight('bold').setFontSize(13).setFontColor(kpi[2]);
  });

  sh.setRowHeight(4, 40);
  sh.setRowHeight(5, 36);

  // ── PENJUALAN PER CHANNEL ──
  sh.getRange('A7:H7').merge().setValue('RINGKASAN PENJUALAN PER CHANNEL').setFontWeight('bold').setBackground(C.INK2).setFontColor(C.WHITE);

  headerRow(sh, 8, ['Channel', 'Gross Sales', 'Diskon', 'Biaya Platform', 'Net Sales', '% dari Total', '', ''], C.INK);
  CHANNELS.forEach((ch, i) => {
    const r = 9 + i;
    sh.getRange(r, 1).setValue(ch);
    sh.getRange(r, 2).setFormula(`=SUMIF('INPUT: Penjualan'!C4:C1000;"${ch}";'INPUT: Penjualan'!H4:H1000)`).setNumberFormat('#,##0');
    sh.getRange(r, 3).setFormula(`=SUMIF('INPUT: Penjualan'!C4:C1000;"${ch}";'INPUT: Penjualan'!I4:I1000)`).setNumberFormat('#,##0');
    sh.getRange(r, 4).setFormula(`=SUMIF('INPUT: Penjualan'!C4:C1000;"${ch}";'INPUT: Penjualan'!J4:J1000)`).setNumberFormat('#,##0');
    sh.getRange(r, 5).setFormula(`=SUMIF('INPUT: Penjualan'!C4:C1000;"${ch}";'INPUT: Penjualan'!K4:K1000)`).setNumberFormat('#,##0');
    sh.getRange(r, 6).setFormula(`=IF(E9="";0;E${r}/SUM(E9:E14))`).setNumberFormat('0.0%');
    sh.getRange(r, 1, 1, 6).setBackground(i % 2 === 0 ? C.WHITE : C.GRAY);
  });

  // Total
  sh.getRange(15, 1).setValue('TOTAL').setFontWeight('bold');
  for (let c = 2; c <= 5; c++) {
    sh.getRange(15, c).setFormula(`=SUM(${String.fromCharCode(64+c)}9:${String.fromCharCode(64+c)}14)`).setNumberFormat('#,##0').setFontWeight('bold');
  }
  sh.getRange(15, 1, 1, 6).setBackground(C.OK).setFontWeight('bold');

  // ── LINK NAVIGASI ──
  sh.getRange('A17:H17').merge().setValue('MENU NAVIGASI CEPAT').setFontWeight('bold').setBackground(C.INK2).setFontColor(C.WHITE);
  const navLinks = [
    '>> INPUT: Penjualan', '>> INPUT: Pembelian & Produksi',
    '>> INPUT: Beban & Kas Keluar', '>> INPUT: Jurnal Penyesuaian',
    '>> Laba Rugi', '>> Posisi Keuangan',
    '>> Arus Kas', '>> Kalkulator Pajak',
  ];
  navLinks.forEach((txt, i) => {
    sh.getRange(18 + Math.floor(i/4), 1 + (i % 4) * 2, 1, 2).merge()
      .setValue(txt).setFontColor('#0ea5e9').setFontWeight('bold');
  });

  sh.setColumnWidths(1, 8, 155);
}
```

---

## Catatan Migrasi ke Web (Laravel/Filament)

Ketika disetujui untuk dijadikan modul web, pemetaan struktur ini langsung dapat digunakan:

| Sheet Google Sheets | Tabel Database | Diisi Oleh |
|---|---|---|
| `COA` | `chart_of_accounts` | Setup awal / admin |
| `INPUT: Penjualan` | `sales_journal_entries` | **POS & web: otomatis. Marketplace: form input** |
| `INPUT: Pembelian & Produksi` | `purchase_journal_entries` | Form input akuntan |
| `INPUT: Beban & Kas Keluar` | `expense_journal_entries` | Form input akuntan |
| `INPUT: Jurnal Penyesuaian` | `adjustment_journal_entries` | Form input akuntan |
| `Laba Rugi` | View/query dari semua tabel | Halaman laporan otomatis |
| `Posisi Keuangan` | View/query dari semua tabel | Halaman laporan otomatis |
| `Arus Kas` | View/query dari semua tabel | Halaman laporan otomatis |
| `Kalkulator Pajak` | Computed dari `sales_journal_entries` | Widget dashboard otomatis |

**Yang paling hemat waktu saat migrasi:**
Data transaksi POS (dari `pos_orders`) dan penjualan web (dari `orders`) **sudah ada di database** — tinggal dibuat event/trigger otomatis yang menghasilkan baris di `sales_journal_entries` setiap kali ada transaksi, tanpa perlu input manual akuntan sama sekali.
