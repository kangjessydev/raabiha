/**
 * Sistem Akuntansi Semi-Otomatis — Raabiha Clothing & Konveksi
 * Multi-Channel: POS Toko | Web Raabiha | Shopee | Lazada | Tokopedia | TikTok Shop
 *
 * Dirancang sebagai:
 *   1. Alat akuntansi mandiri di Google Sheets
 *   2. Blueprint skema database untuk modul web Laravel/Filament
 */

// ─────────────────────────────────────────────────────────
// KONSTANTA
// ─────────────────────────────────────────────────────────

const C = {
  INK:       '#0f172a',   // Latar header gelap
  INK2:      '#1e293b',   // Latar header gelap sekunder
  WHITE:     '#ffffff',
  SLATE:     '#94a3b8',   // Teks subjudul
  MUTED:     '#64748b',   // Teks keterangan
  POS:       '#065f46',   // Hijau tua — POS toko
  WEB:       '#1e3a8a',   // Biru tua — Web Raabiha
  SHOPEE:    '#7c2d12',   // Oranye — Shopee
  LAZADA:    '#4c1d95',   // Ungu — Lazada
  TOKOPEDIA: '#14532d',   // Hijau — Tokopedia
  TIKTOK:    '#0f172a',   // Hitam — TikTok
  OK:        '#bbf7d0',   // Hijau muda — Laba / Total positif
  WARN:      '#fef08a',   // Kuning — Perhatian
  ERR:       '#fecaca',   // Merah muda — Selisih / Negatif
  GRAY:      '#f1f5f9',   // Abu-abu muda — Subtotal
  LGRAY:     '#e2e8f0',   // Abu-abu — Header seksi
};

const CHANNELS = ['POS Toko', 'Web Raabiha', 'Shopee', 'Lazada', 'Tokopedia', 'TikTok Shop'];

// ─────────────────────────────────────────────────────────
// ENTRY POINT
// ─────────────────────────────────────────────────────────

function setupAkuntansiRaabiha() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();

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
  buildDashboard(ss);

  const defaultSheet = ss.getSheetByName('Sheet1');
  if (defaultSheet && ss.getSheets().length > 1) {
    ss.deleteSheet(defaultSheet);
  }
  const tmpSheet = ss.getSheetByName('_tmp');
  if (tmpSheet && ss.getSheets().length > 1) {
    ss.deleteSheet(tmpSheet);
  }

  ss.setActiveSheet(ss.getSheetByName('Dashboard'));

  SpreadsheetApp.getUi().alert(
    'Selesai!\n\n' +
    '1. Sheet COA sudah dilengkapi kolom Saldo Awal dan akun 6-110 (Beban IT & Server Web).\n' +
    '2. Seluruh laporan keuangan otomatis terhitung & terhubung ke filter Dashboard.'
  );
}

// ─────────────────────────────────────────────────────────
// HELPER FUNCTIONS
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

function setRupiah(range) { range.setNumberFormat('#,##0'); }
function setPersen(range) { range.setNumberFormat('0.00%'); }
function setTanggal(range) { range.setNumberFormat('dd/mm/yyyy'); }

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
  const coaRange = shCOA.getRange('A4:A65');
  return SpreadsheetApp.newDataValidation()
    .requireValueInRange(coaRange, true)
    .build();
}

// ─────────────────────────────────────────────────────────
// 1. COA — CHART OF ACCOUNTS (DENGAN SALDO AWAL & AKUN WEB IT)
// ─────────────────────────────────────────────────────────

function buildCOA(ss) {
  const sh = makeSheet(ss, 'COA');

  title(sh, 'DAFTAR AKUN (CHART OF ACCOUNTS)', 1, 7);
  subTitle(sh, 2, 'Kode akun ini digunakan di seluruh sheet. Kolom G (saldo_awal) diisi saldo awal saat buka buku.', 7);

  headerRow(sh, 3, ['kode_akun', 'nama_akun', 'kategori', 'sub_kategori', 'normal_balance', 'laporan', 'saldo_awal', 'saldo_akhir'], C.INK2);

  const coa = [
    // ── ASET ──
    ['1-101', 'Kas Toko (POS)',               'Aset', 'Aset Lancar',   'DB', 'Neraca', 0],
    ['1-102', 'Kas Bank BCA',                 'Aset', 'Aset Lancar',   'DB', 'Neraca', 115089124],
    ['1-103', 'Kas Bank Mandiri',             'Aset', 'Aset Lancar',   'DB', 'Neraca', 0],
    ['1-104', 'Kas Bank Lainnya',             'Aset', 'Aset Lancar',   'DB', 'Neraca', 0],
    ['1-201', 'Piutang Shopee',               'Aset', 'Piutang Dagang','DB', 'Neraca', 0],
    ['1-202', 'Piutang Lazada',               'Aset', 'Piutang Dagang','DB', 'Neraca', 0],
    ['1-203', 'Piutang Tokopedia',            'Aset', 'Piutang Dagang','DB', 'Neraca', 0],
    ['1-204', 'Piutang TikTok Shop',          'Aset', 'Piutang Dagang','DB', 'Neraca', 0],
    ['1-205', 'Piutang Web Raabiha',          'Aset', 'Piutang Dagang','DB', 'Neraca', 0],
    ['1-206', 'Piutang Pelanggan Lainnya',    'Aset', 'Piutang Dagang','DB', 'Neraca', 0],
    ['1-301', 'Persediaan Bahan Baku',        'Aset', 'Persediaan',    'DB', 'Neraca', 38631000],
    ['1-302', 'Persediaan Barang Dalam Proses','Aset','Persediaan',    'DB', 'Neraca', 0],
    ['1-303', 'Persediaan Barang Jadi',       'Aset', 'Persediaan',    'DB', 'Neraca', 0],
    ['1-401', 'Peralatan & Mesin Jahit',      'Aset', 'Aset Tetap',    'DB', 'Neraca', 0],
    ['1-402', 'Akumulasi Penyusutan Peralatan','Aset','Aset Tetap',    'CR', 'Neraca', 0],
    ['1-403', 'Kendaraan Operasional',        'Aset', 'Aset Tetap',    'DB', 'Neraca', 0],
    ['1-404', 'Akumulasi Penyusutan Kendaraan','Aset','Aset Tetap',    'CR', 'Neraca', 0],
    // ── KEWAJIBAN ──
    ['2-101', 'Hutang Dagang / Supplier',     'Kewajiban', 'Kewajiban Lancar','CR','Neraca', 0],
    ['2-102', 'Hutang Bank',                  'Kewajiban', 'Kewajiban Jangka Panjang','CR','Neraca', 0],
    ['2-103', 'Hutang Pajak (PPh)',           'Kewajiban', 'Kewajiban Lancar','CR','Neraca', 0],
    // ── EKUITAS ──
    ['3-101', 'Modal Pemilik',                'Ekuitas', 'Modal',      'CR', 'Neraca', 153720124],
    ['3-102', 'Prive Pemilik',                'Ekuitas', 'Prive',      'DB', 'Neraca', 0],
    ['3-103', 'Saldo Laba Ditahan',           'Ekuitas', 'Retained Earnings','CR','Neraca', 0],
    // ── PENDAPATAN ──
    ['4-101', 'Penjualan — POS Toko',         'Pendapatan', 'Penjualan','CR','Laba Rugi', 0],
    ['4-102', 'Penjualan — Web Raabiha',      'Pendapatan', 'Penjualan','CR','Laba Rugi', 0],
    ['4-103', 'Penjualan — Shopee',           'Pendapatan', 'Penjualan','CR','Laba Rugi', 0],
    ['4-104', 'Penjualan — Lazada',           'Pendapatan', 'Penjualan','CR','Laba Rugi', 0],
    ['4-105', 'Penjualan — Tokopedia',        'Pendapatan', 'Penjualan','CR','Laba Rugi', 0],
    ['4-106', 'Penjualan — TikTok Shop',      'Pendapatan', 'Penjualan','CR','Laba Rugi', 0],
    ['4-201', 'Pendapatan Jasa Konveksi',     'Pendapatan', 'Jasa',    'CR','Laba Rugi', 0],
    ['4-901', 'Diskon Penjualan',             'Pendapatan', 'Pengurang','DB','Laba Rugi', 0],
    ['4-902', 'Retur Penjualan',              'Pendapatan', 'Pengurang','DB','Laba Rugi', 0],
    ['4-903', 'Biaya Jasa Marketplace & PG',   'Pendapatan', 'Pengurang','DB','Laba Rugi', 0],
    // ── HPP ──
    ['5-101', 'HPP — Penjualan Barang Jadi',  'HPP', 'HPP Toko',      'DB', 'Laba Rugi', 0],
    ['5-201', 'Biaya Bahan Baku Konveksi',    'HPP', 'Biaya Produksi','DB', 'Laba Rugi', 0],
    ['5-202', 'Biaya Ongkos Jahit & Potong',  'HPP', 'Biaya Produksi','DB', 'Laba Rugi', 0],
    ['5-203', 'Biaya Aksesoris & Packing Produksi','HPP','Biaya Produksi','DB','Laba Rugi', 0],
    // ── BEBAN OPERASIONAL ──
    ['6-101', 'Beban Gaji Karyawan',          'Beban', 'Operasional',  'DB', 'Laba Rugi', 0],
    ['6-102', 'Beban Sewa Tempat',            'Beban', 'Operasional',  'DB', 'Laba Rugi', 0],
    ['6-103', 'Beban Listrik, Air & Internet','Beban', 'Operasional',  'DB', 'Laba Rugi', 0],
    ['6-104', 'Beban Pemasaran & Iklan Digital','Beban','Operasional', 'DB', 'Laba Rugi', 0],
    ['6-105', 'Beban Ongkos Kirim / Kurir',   'Beban', 'Operasional',  'DB', 'Laba Rugi', 0],
    ['6-106', 'Beban Penyusutan Aset',        'Beban', 'Operasional',  'DB', 'Laba Rugi', 0],
    ['6-107', 'Beban Administrasi & Bank',    'Beban', 'Operasional',  'DB', 'Laba Rugi', 0],
    ['6-108', 'Beban Perlengkapan Toko',      'Beban', 'Operasional',  'DB', 'Laba Rugi', 0],
    ['6-109', 'Beban Lain-lain',              'Beban', 'Lain-lain',    'DB', 'Laba Rugi', 0],
    ['6-110', 'Beban IT, Server & Operational Web','Beban','Operasional','DB','Laba Rugi', 0],
  ];

  sh.getRange(4, 1, coa.length, 7).setValues(coa);
  setRupiah(sh.getRange('G4:H' + (3 + coa.length)));

  coa.forEach((row, i) => {
    const rowNum = 4 + i;
    let bg = C.WHITE;
    if (row[0].startsWith('1-')) bg = '#f0fdf4';
    if (row[0].startsWith('2-')) bg = '#fef2f2';
    if (row[0].startsWith('3-')) bg = '#eff6ff';
    if (row[0].startsWith('4-')) bg = '#f0fdf4';
    if (row[0].startsWith('5-')) bg = '#fff7ed';
    if (row[0].startsWith('6-')) bg = '#fdf4ff';
    sh.getRange(rowNum, 1, 1, 8).setBackground(bg);

    // Formula Kolom H (saldo_akhir)
    sh.getRange(rowNum, 8).setFormula(
      `=IF(E${rowNum}="DB"; G${rowNum} + SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!D4:D1000; A${rowNum}) + SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!K4:K1000; A${rowNum}) + SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!C4:C1000; A${rowNum}) + SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!D4:D500; A${rowNum}) - SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!F4:F1000; A${rowNum}) - SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!M4:M1000; A${rowNum}) - SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!F4:F500; A${rowNum}); G${rowNum} + SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!F4:F1000; A${rowNum}) + SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!M4:M1000; A${rowNum}) + SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!F4:F500; A${rowNum}) - SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!D4:D1000; A${rowNum}) - SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!K4:K1000; A${rowNum}) - SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!C4:C1000; A${rowNum}) - SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!D4:D500; A${rowNum}))`
    );
  });

  sh.setColumnWidth(1, 90); sh.setColumnWidth(2, 260); sh.setColumnWidth(3, 110);
  sh.setColumnWidth(4, 180); sh.setColumnWidth(5, 110); sh.setColumnWidth(6, 100);
  sh.setColumnWidth(7, 140); sh.setColumnWidth(8, 140);
}

}

// ─────────────────────────────────────────────────────────
// 2. INPUT: PENJUALAN
// ─────────────────────────────────────────────────────────

function buildInputPenjualan(ss) {
  const sh = makeSheet(ss, 'INPUT: Penjualan');

  title(sh, 'INPUT — JURNAL PENJUALAN', 1, 12, C.INK);
  subTitle(sh, 2, 'POS & Web Raabiha terisi dari sistem. Marketplace diisi manual rekap settlement.', 12);

  headerRow(sh, 3, [
    'tanggal', 'no_referensi', 'channel', 'kode_akun_kas', 'nama_akun_kas',
    'kode_akun_penjualan', 'nama_akun_penjualan', 'gross_sales', 'diskon', 'biaya_marketplace', 'net_sales', 'keterangan'
  ]);

  const examples = [
    ['=TODAY()-1', 'POS-20260101', 'POS Toko',     '1-101', '', '4-101', '', 500000, 0, 0, '', 'Penjualan kasir harian'],
    ['=TODAY()-1', 'WEB-20260101', 'Web Raabiha',  '1-205', '', '4-102', '', 300000, 0, 4400, '', 'Penjualan web Raabiha (fee Xendit)'],
    ['=TODAY()-7', 'SPE-JUN-W1',   'Shopee',       '1-201', '', '4-103', '', 2500000,150000,175000,'','Settlement Shopee minggu ke-1'],
    ['=TODAY()-7', 'LZD-JUN-W1',   'Lazada',       '1-202', '', '4-104', '', 1200000,50000,84000,'', 'Settlement Lazada minggu ke-1'],
    ['=TODAY()-7', 'TKP-JUN-W1',   'Tokopedia',    '1-203', '', '4-105', '', 950000, 20000,47500,'', 'Settlement Tokopedia minggu ke-1'],
    ['=TODAY()-7', 'TTK-JUN-W1',   'TikTok Shop',  '1-204', '', '4-106', '', 1800000,100000,126000,'','Settlement TikTok Shop minggu ke-1'],
  ];
  sh.getRange(4, 1, examples.length, 12).setValues(examples);

  const lastDataRow = 500;
  for (let r = 4; r <= lastDataRow; r++) {
    sh.getRange(r, 5).setFormula(`=IF(D${r}="";"";IFERROR(VLOOKUP(D${r};COA!A:B;2;FALSE);"-"))`);
    sh.getRange(r, 7).setFormula(`=IF(F${r}="";"";IFERROR(VLOOKUP(F${r};COA!A:B;2;FALSE);"-"))`);
    sh.getRange(r, 11).setFormula(`=IF(H${r}="";"";H${r}-I${r}-J${r})`);
  }

  setTanggal(sh.getRange('A4:A' + lastDataRow));
  setRupiah(sh.getRange('H4:K' + lastDataRow));

  const channelRule = SpreadsheetApp.newDataValidation().requireValueInList(CHANNELS, true).build();
  sh.getRange('C4:C' + lastDataRow).setDataValidation(channelRule);

  const coaRule = getCOARule(ss);
  sh.getRange('D4:D' + lastDataRow).setDataValidation(coaRule);
  sh.getRange('F4:F' + lastDataRow).setDataValidation(coaRule);

  sh.setColumnWidth(1, 100); sh.setColumnWidth(2, 150); sh.setColumnWidth(3, 130);
  sh.setColumnWidth(4, 110); sh.setColumnWidth(5, 200); sh.setColumnWidth(6, 120);
  sh.setColumnWidth(7, 200); sh.setColumnWidths(8, 4, 130); sh.setColumnWidth(12, 250);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 3. INPUT: PEMBELIAN & PRODUKSI KONVEKSI
// ─────────────────────────────────────────────────────────

function buildInputPembelianProduksi(ss) {
  const sh = makeSheet(ss, 'INPUT: Pembelian & Produksi');
  title(sh, 'INPUT — PEMBELIAN & BIAYA PRODUKSI KONVEKSI', 1, 14, C.SHOPEE);
  subTitle(sh, 2, 'Gunakan untuk: Beli bahan baku, restok baju jadi, dan bayar ongkos jahit.', 14);

  headerRow(sh, 3, [
    'tanggal', 'no_faktur', 'jenis', 'supplier', 'keterangan_item', 'qty', 'satuan',
    'harga_satuan', 'total', 'status_bayar', 'kode_akun_debet', 'nama_akun_debet', 'kode_akun_kredit', 'nama_akun_kredit'
  ], C.SHOPEE);

  const coaRule = getCOARule(ss);
  sh.getRange('K4:K500').setDataValidation(coaRule);
  sh.getRange('M4:M500').setDataValidation(coaRule);

  for (let r = 4; r <= 500; r++) {
    sh.getRange(r, 9).setFormula(`=IF(F${r}="";"";F${r}*H${r})`);
    sh.getRange(r, 12).setFormula(`=IF(K${r}="";"";IFERROR(VLOOKUP(K${r};COA!A:B;2;FALSE);"-"))`);
    sh.getRange(r, 14).setFormula(`=IF(M${r}="";"";IFERROR(VLOOKUP(M${r};COA!A:B;2;FALSE);"-"))`);
  }

  setTanggal(sh.getRange('A4:A500'));
  setRupiah(sh.getRange('H4:I500'));
  sh.setColumnWidths(1, 14, 130);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 4. INPUT: BEBAN & KAS KELUAR (TERMASUK BEBAN IT WEB 6-110)
// ─────────────────────────────────────────────────────────

function buildInputBeban(ss) {
  const sh = makeSheet(ss, 'INPUT: Beban & Kas Keluar');
  title(sh, 'INPUT — BEBAN OPERASIONAL & KAS KELUAR', 1, 8, C.TIKTOK);
  subTitle(sh, 2, 'Catat pengeluaran operasional: gaji, sewa, listrik, iklan, server web (6-110), dll.', 8);

  headerRow(sh, 3, [
    'tanggal', 'no_bukti', 'kode_akun_beban', 'nama_akun_beban', 'keterangan_detail', 'nominal', 'sumber_kas', 'keterangan'
  ]);

  const coaRule = getCOARule(ss);
  sh.getRange('C4:C500').setDataValidation(coaRule);

  // Contoh dummy pengeluaran Web IT & Operasional
  const examples = [
    ['=TODAY()-5', 'WEB-IT01', '6-110', '', 'Sewa Domain & Server Vercel Pro Web Raabiha', 350000, 'Kas Bank BCA', 'Tagihan sewa server & domain web'],
    ['=TODAY()-3', 'WEB-IT02', '6-110', '', 'Topup Saldo API Ongkir Biteship', 100000, 'Kas Bank BCA', 'Saldo API ongkir otomatis web'],
    ['=TODAY()-1', 'WEB-IT03', '6-110', '', 'Topup Saldo API Email Resend Notifikasi', 150000, 'Kas Bank BCA', 'API email notifikasi transaksi web'],
  ];
  sh.getRange(4, 1, examples.length, 8).setValues(examples);

  for (let r = 4; r <= 500; r++) {
    sh.getRange(r, 4).setFormula(`=IF(C${r}="";"";IFERROR(VLOOKUP(C${r};COA!A:B;2;FALSE);"-"))`);
  }

  setTanggal(sh.getRange('A4:A500'));
  setRupiah(sh.getRange('F4:F500'));

  sh.setColumnWidth(1, 100); sh.setColumnWidth(2, 130); sh.setColumnWidth(3, 120);
  sh.setColumnWidth(4, 220); sh.setColumnWidth(5, 260); sh.setColumnWidth(6, 130);
  sh.setColumnWidth(7, 160); sh.setColumnWidth(8, 200);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 5. INPUT: JURNAL PENYESUAIAN (AJP)
// ─────────────────────────────────────────────────────────

function buildInputPenyesuaian(ss) {
  const sh = makeSheet(ss, 'INPUT: Jurnal Penyesuaian');
  title(sh, 'INPUT — JURNAL PENYESUAIAN (AKHIR PERIODE)', 1, 8, '#7c3aed');
  subTitle(sh, 2, 'Diisi akhir bulan/tahun: penyusutan, stok opname, reklasifikasi.', 8);

  headerRow(sh, 3, [
    'tanggal', 'no_ajp', 'keterangan_penyesuaian', 'kode_akun_debet', 'nama_akun_debet', 'kode_akun_kredit', 'nama_akun_kredit', 'nominal'
  ], '#7c3aed');

  const coaRule = getCOARule(ss);
  sh.getRange('D4:D500').setDataValidation(coaRule);
  sh.getRange('F4:F500').setDataValidation(coaRule);

  for (let r = 4; r <= 500; r++) {
    sh.getRange(r, 5).setFormula(`=IF(D${r}="";"";IFERROR(VLOOKUP(D${r};COA!A:B;2;FALSE);"-"))`);
    sh.getRange(r, 7).setFormula(`=IF(F${r}="";"";IFERROR(VLOOKUP(F${r};COA!A:B;2;FALSE);"-"))`);
  }

  setTanggal(sh.getRange('A4:A500'));
  setRupiah(sh.getRange('H4:H500'));
  sh.setColumnWidths(1, 8, 170);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 6. BUKU BESAR (HYBRID HISTORIS & FILTER + COA SALDO AWAL)
// ─────────────────────────────────────────────────────────

function buildBukuBesar(ss) {
  const sh = makeSheet(ss, 'Buku Besar');
  title(sh, 'BUKU BESAR (GENERAL LEDGER)', 1, 8);
  sh.getRange('A2:H2').merge().setValue('Pilih kode akun di sel B3 untuk melihat mutasi akun tersebut.')
    .setFontColor(C.MUTED).setFontSize(9);

  sh.getRange('A3').setValue('PILIH KODE AKUN:').setFontWeight('bold');
  sh.getRange('B3').setValue('1-102').setBackground(C.WARN).setFontWeight('bold');

  const coaRule = getCOARule(ss);
  sh.getRange('B3').setDataValidation(coaRule);

  sh.getRange('C3').setValue('Nama Akun:').setFontWeight('bold');
  sh.getRange('D3').setFormula('=IFERROR(VLOOKUP(B3;COA!A:B;2;FALSE);"Kode tidak ditemukan")').setFontWeight('bold');

  sh.getRange('F3').setValue('Tgl Mulai (Opsional):').setFontWeight('bold');
  sh.getRange('G3').setValue('01/01/2026');
  sh.getRange('H3').setValue('31/12/2026');
  setTanggal(sh.getRange('G3:H3'));

  headerRow(sh, 4, ['Tanggal', 'No Referensi', 'Keterangan', 'Debet', 'Kredit', 'Sumber', 'Saldo']);

  // Master Query di A4
  sh.getRange('A4').setFormula(
    '={\n' +
    '  {"Tanggal" \\ "No Referensi" \\ "Keterangan" \\ "Debet" \\ "Kredit" \\ "Sumber"};\n' +
    '  IFERROR(QUERY({\n' +
    '    QUERY(\'INPUT: Penjualan\'!A4:L; "SELECT A, B, L, K, 0, \'Penjualan\' WHERE D = \'"&B3&"\' AND A IS NOT NULL"; 0);\n' +
    '    QUERY(\'INPUT: Penjualan\'!A4:L; "SELECT A, B, L, 0, H, \'Penjualan\' WHERE F = \'"&B3&"\' AND A IS NOT NULL"; 0);\n' +
    '    QUERY(\'INPUT: Pembelian & Produksi\'!A4:N; "SELECT A, B, E, I, 0, \'Pembelian\' WHERE K = \'"&B3&"\' AND A IS NOT NULL"; 0);\n' +
    '    QUERY(\'INPUT: Pembelian & Produksi\'!A4:N; "SELECT A, B, E, 0, I, \'Pembelian\' WHERE M = \'"&B3&"\' AND A IS NOT NULL"; 0);\n' +
    '    QUERY(\'INPUT: Beban & Kas Keluar\'!A4:H; "SELECT A, B, E, F, 0, \'Beban\' WHERE C = \'"&B3&"\' AND A IS NOT NULL"; 0);\n' +
    '    QUERY(\'INPUT: Beban & Kas Keluar\'!A4:H; "SELECT A, B, E, 0, F, \'Beban\' WHERE G = \'"&D3&"\' AND A IS NOT NULL"; 0);\n' +
    '    QUERY(\'INPUT: Jurnal Penyesuaian\'!A4:H; "SELECT A, B, C, H, 0, \'Penyesuaian\' WHERE D = \'"&B3&"\' AND A IS NOT NULL"; 0);\n' +
    '    QUERY(\'INPUT: Jurnal Penyesuaian\'!A4:H; "SELECT A, B, C, 0, H, \'Penyesuaian\' WHERE F = \'"&B3&"\' AND A IS NOT NULL"; 0)\n' +
    '  }; "SELECT Col1, Col2, Col3, Col4, Col5, Col6 WHERE Col1 IS NOT NULL " & IF(G3<>""; "AND Col1 >= date \'"&TEXT(G3;"yyyy-mm-dd")&"\' "; "") & IF(H3<>""; "AND Col1 <= date \'"&TEXT(H3;"yyyy-mm-dd")&"\' "; "") & "ORDER BY Col1 ASC"; 0); {"Belum ada transaksi" \\ "" \\ "" \\ "" \\ "" \\ ""})\n' +
    '}'
  );

  // Saldo Berjalan di G4 (Memulai dari Saldo Awal COA!G)
  sh.getRange('G4').setFormula(
    '={"Saldo"; IFERROR(MAP(A5:A; SCAN(IFERROR(VLOOKUP(B3; COA!A:G; 7; FALSE); 0); INDEX(N(D5:D)-N(E5:E)); LAMBDA(acc; val; acc + val)); LAMBDA(tgl; sal; IF(OR(tgl=""; tgl="Belum ada transaksi"); ""; sal))); "")}'
  );

  sh.setColumnWidth(1, 100); sh.setColumnWidth(2, 130); sh.setColumnWidth(3, 260);
  sh.setColumnWidths(4, 2, 130); sh.setColumnWidth(6, 120); sh.setColumnWidth(7, 140);
  sh.setFrozenRows(4);
}

// ─────────────────────────────────────────────────────────
// 7. LABA RUGI (LENGKAP DENGAN BEBAN IT WEB 6-110)
// ─────────────────────────────────────────────────────────

function buildLabaRugi(ss) {
  const sh = makeSheet(ss, 'Laba Rugi');

  title(sh, 'LAPORAN LABA RUGI', 1, 8);
  sh.getRange('A2:H2').merge().setFormula('="RAABIHA CLOTHING & KONVEKSI — Periode: " & TEXT(Dashboard!G5;"dd/mm/yyyy") & " s/d " & TEXT(Dashboard!H5;"dd/mm/yyyy")')
    .setHorizontalAlignment('center').setFontSize(11);

  headerRow(sh, 3, ['Uraian', 'POS Toko', 'Web Raabiha', 'Shopee', 'Lazada', 'Tokopedia', 'TikTok Shop', 'TOTAL']);

  function salesFormula(channel, field) {
    const colMap = { net: 'K', gross: 'H', diskon: 'I', biaya: 'J' };
    const col = colMap[field];
    return `=SUMIFS('INPUT: Penjualan'!${col}4:${col}1000; 'INPUT: Penjualan'!C4:C1000; "${channel}"; 'INPUT: Penjualan'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5)`;
  }
  function bebanFormula(kodeAkun) {
    return `=SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!C4:C1000; "${kodeAkun}"; 'INPUT: Beban & Kas Keluar'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Beban & Kas Keluar'!A4:A1000; "<="&'Dashboard'!$H$5) + SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!D4:D500; "${kodeAkun}"; 'INPUT: Jurnal Penyesuaian'!A4:A500; ">="&'Dashboard'!$G$5; 'INPUT: Jurnal Penyesuaian'!A4:A500; "<="&'Dashboard'!$H$5)`;
  }

  let row = 4;

  // ── PENDAPATAN ──
  sectionHeader(sh, row, 'PENDAPATAN PENJUALAN', 8, C.INK2);
  sh.getRange(row, 1, 1, 8).setFontColor(C.WHITE);
  row++;

  CHANNELS.forEach((ch, i) => {
    sh.getRange(row, 1).setValue('  Penjualan ' + ch);
    sh.getRange(row, 2 + i).setFormula(salesFormula(ch, 'gross')).setNumberFormat('#,##0');
    sh.getRange(row, 8).setFormula(`=SUM(B${row}:G${row})`).setNumberFormat('#,##0');
    row++;
  });

  sh.getRange(row, 1).setValue('  Pendapatan Jasa Konveksi');
  sh.getRange(row, 8).setFormula(`=SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!F4:F1000; "4-201"; 'INPUT: Penjualan'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5)`).setNumberFormat('#,##0');
  row++;

  const grossRow = row;
  totalRow(sh, row, 'GROSS REVENUE', 8, 8, `=SUM(H5:H${row-1})`, C.LGRAY);
  row++;

  sectionHeader(sh, row, 'PENGURANG PENDAPATAN', 8);
  row++;

  sh.getRange(row, 1).setValue('  Diskon Penjualan');
  CHANNELS.forEach((ch, i) => {
    sh.getRange(row, 2 + i).setFormula(salesFormula(ch, 'diskon')).setNumberFormat('#,##0');
  });
  sh.getRange(row, 8).setFormula(`=SUM(B${row}:G${row})`).setNumberFormat('#,##0');
  const diskonRow = row; row++;

  sh.getRange(row, 1).setValue('  Biaya Jasa Marketplace & Payment Gateway');
  CHANNELS.forEach((ch, i) => {
    sh.getRange(row, 2 + i).setFormula(salesFormula(ch, 'biaya')).setNumberFormat('#,##0');
  });
  sh.getRange(row, 8).setFormula(`=SUM(B${row}:G${row})`).setNumberFormat('#,##0');
  const mktRow = row; row++;

  const netRow = row;
  totalRow(sh, row, 'NET REVENUE (PENDAPATAN BERSIH)', 8, 8, `=H${grossRow}-H${diskonRow}-H${mktRow}`, C.OK);
  row += 2;

  // ── HPP ──
  sectionHeader(sh, row, 'HARGA POKOK PENJUALAN & BIAYA PRODUKSI', 8, '#7c2d12');
  sh.getRange(row, 1, 1, 8).setFontColor(C.WHITE);
  row++;

  const hppItems = [
    ['  HPP Penjualan Barang Jadi', `=SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!C4:C1000; "Barang Jadi (Supplier)"; 'INPUT: Pembelian & Produksi'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Biaya Bahan Baku Konveksi', `=SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!C4:C1000; "Bahan Baku"; 'INPUT: Pembelian & Produksi'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Biaya Ongkos Jahit & Potong', `=SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!C4:C1000; "Ongkos Jahit"; 'INPUT: Pembelian & Produksi'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Biaya Aksesoris Produksi', `=SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!C4:C1000; "Aksesoris"; 'INPUT: Pembelian & Produksi'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
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

  // ── BEBAN OPERASIONAL (TERMASUK 6-110 BEBAN IT WEB) ──
  sectionHeader(sh, row, 'BEBAN OPERASIONAL', 8, C.TIKTOK);
  sh.getRange(row, 1, 1, 8).setFontColor(C.WHITE);
  row++;

  const bebanItems = [
    ['  Beban Gaji Karyawan',           bebanFormula('6-101')],
    ['  Beban Sewa Tempat',             bebanFormula('6-102')],
    ['  Beban Listrik, Air & Internet', bebanFormula('6-103')],
    ['  Beban Pemasaran & Iklan',       bebanFormula('6-104')],
    ['  Beban Ongkos Kirim / Kurir',    bebanFormula('6-105')],
    ['  Beban Penyusutan Aset',         bebanFormula('6-106')],
    ['  Beban Administrasi & Bank',     bebanFormula('6-107')],
    ['  Beban Perlengkapan Toko',       bebanFormula('6-108')],
    ['  Beban Lain-lain',               bebanFormula('6-109')],
    ['  Beban IT, Server & Operational Web', bebanFormula('6-110')],
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

  const labaSebelumPajakRow = row;
  sh.getRange(row, 1, 1, 8).setFontWeight('bold').setBackground(C.LGRAY);
  sh.getRange(row, 1).setValue('LABA SEBELUM PAJAK');
  sh.getRange(row, 8).setFormula(`=H${grossProfitRow}-H${bebanTotalRow}`).setNumberFormat('#,##0');
  row++;

  sh.getRange(row, 1).setValue('  Beban Pajak (PPh Final 0,5% × Net Revenue)');
  sh.getRange(row, 8).setFormula(`='Kalkulator Pajak'!D16`).setNumberFormat('#,##0');
  const pajakRow = row; row++;

  sh.getRange(row, 1, 1, 8).setFontWeight('bold').setFontSize(12).setBackground(C.OK);
  sh.getRange(row, 1).setValue('LABA BERSIH (NET PROFIT)');
  sh.getRange(row, 8).setFormula(`=H${labaSebelumPajakRow}-H${pajakRow}`).setNumberFormat('#,##0');

  sh.setColumnWidth(1, 280); sh.setColumnWidths(2, 7, 130);
  sh.setFrozenRows(3);
}

// ─────────────────────────────────────────────────────────
// 8. POSISI KEUANGAN (NERACA DENGAN INTEGRASI COA SALDO AWAL)
// ─────────────────────────────────────────────────────────

function buildPosisiKeuangan(ss) {
  const sh = makeSheet(ss, 'Posisi Keuangan');

  title(sh, 'LAPORAN POSISI KEUANGAN (NERACA)', 1, 6);
  sh.getRange('A2:F2').merge().setFormula('="RAABIHA CLOTHING & KONVEKSI — Per " & TEXT(\'Dashboard\'!H5; "dd MMMM yyyy")')
    .setHorizontalAlignment('center').setFontSize(11);

  headerRow(sh, 3, ['ASET (AKTIVA)', '', 'Jumlah (Rp)', '', 'KEWAJIBAN & EKUITAS', 'Jumlah (Rp)']);

  const asetItems = [
    ['Aset Lancar', null, true],
    ['  Kas Toko (POS)', `=IFERROR(VLOOKUP("1-101"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!C4:C1000; "POS Toko"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5) - SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!G4:G1000; "Kas Toko (POS)"; 'INPUT: Beban & Kas Keluar'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Kas Bank BCA', `=IFERROR(VLOOKUP("1-102"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!D4:D1000; "1-102"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5) - SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!G4:G1000; "Kas Bank BCA"; 'INPUT: Beban & Kas Keluar'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Kas Bank Mandiri', `=IFERROR(VLOOKUP("1-103"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!D4:D1000; "1-103"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5) - SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!G4:G1000; "Kas Bank Mandiri"; 'INPUT: Beban & Kas Keluar'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Piutang Shopee', `=IFERROR(VLOOKUP("1-201"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Penjualan'!H4:H1000; 'INPUT: Penjualan'!C4:C1000; "Shopee"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5) - SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!D4:D1000; "1-102"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Piutang Lazada', `=IFERROR(VLOOKUP("1-202"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Penjualan'!H4:H1000; 'INPUT: Penjualan'!C4:C1000; "Lazada"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Piutang Tokopedia', `=IFERROR(VLOOKUP("1-203"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Penjualan'!H4:H1000; 'INPUT: Penjualan'!C4:C1000; "Tokopedia"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Piutang TikTok Shop', `=IFERROR(VLOOKUP("1-204"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Penjualan'!H4:H1000; 'INPUT: Penjualan'!C4:C1000; "TikTok Shop"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Piutang Web Raabiha', `=IFERROR(VLOOKUP("1-205"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!C4:C1000; "Web Raabiha"; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Persediaan Bahan Baku', `=IFERROR(VLOOKUP("1-301"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!C4:C1000; "Bahan Baku"; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5) - SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!C4:C1000; "Ongkos Jahit"; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Persediaan Barang Jadi', `=IFERROR(VLOOKUP("1-303"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!C4:C1000; "Barang Jadi (Supplier)"; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['Total Aset Lancar', null, false, true],
  ];

  let leftRow = 4;
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
  sh.getRange(leftRow + 1, 3).setFormula(`=IFERROR(VLOOKUP("1-401"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!K4:K1000; "1-401"; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5) - SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!F4:F500; "1-402"; 'INPUT: Jurnal Penyesuaian'!A4:A500; "<="&'Dashboard'!$H$5)`).setNumberFormat('#,##0');
  sh.getRange(leftRow + 2, 1).setValue('Total Aset Tetap').setFontWeight('bold');
  sh.getRange(leftRow + 2, 3).setFormula(`=C${leftRow+1}`).setNumberFormat('#,##0').setFontWeight('bold').setBackground(C.LGRAY);
  sh.getRange(leftRow + 3, 1, 1, 3).setFontWeight('bold').setFontSize(11).setBackground(C.OK);
  sh.getRange(leftRow + 3, 1).setValue('TOTAL ASET');
  sh.getRange(leftRow + 3, 3).setFormula(`=C${leftRow-1}+C${leftRow+2}`).setNumberFormat('#,##0');

  // PASIVA
  const pStartRow = 4;
  const pData = [
    ['Kewajiban Lancar', ''],
    ['  Hutang Dagang / Supplier', `=IFERROR(VLOOKUP("2-101"; COA!A:G; 7; FALSE); 0) + SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!J4:J1000; "Hutang Dagang"; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['Total Kewajiban', '=F5'],
    ['', ''],
    ['Ekuitas', ''],
    ['  Modal Pemilik', "='Perubahan Ekuitas'!C7"],
    ["  Laba Bersih Tahun Ini", "=VLOOKUP(\"LABA BERSIH (NET PROFIT)\"; 'Laba Rugi'!A:H; 8; FALSE)"],
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
// 9. ARUS KAS (DENGAN SALDO KAS AWAL AUTO COA)
// ─────────────────────────────────────────────────────────

function buildArusKas(ss) {
  const sh = makeSheet(ss, 'Arus Kas');
  title(sh, 'LAPORAN ARUS KAS (CASH FLOW)', 1, 4);

  sh.getRange('A2:D2').merge().setFormula('="RAABIHA CLOTHING & KONVEKSI — Periode: " & TEXT(\'Dashboard\'!G5;"dd/mm/yyyy") & " s/d " & TEXT(\'Dashboard\'!H5;"dd/mm/yyyy")')
    .setHorizontalAlignment('center').setFontSize(10);

  const items = [
    ['I. AKTIVITAS OPERASIONAL', null, true],
    ['  Penerimaan dari Penjualan (semua channel)', `=SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Penjualan'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Pembayaran Beban Operasional', `=-SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Beban & Kas Keluar'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['  Pembayaran ke Supplier (tunai)', `=-SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!J4:J1000; "Tunai"; 'INPUT: Pembelian & Produksi'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['ARUS KAS BERSIH DARI OPERASIONAL', `=SUM(D4:D6)`, false, true],
    ['', null],
    ['II. AKTIVITAS INVESTASI', null, true],
    ['  Pembelian Aset Tetap', `=-SUMIFS('INPUT: Pembelian & Produksi'!I4:I1000; 'INPUT: Pembelian & Produksi'!K4:K1000; "1-401"; 'INPUT: Pembelian & Produksi'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Pembelian & Produksi'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['ARUS KAS BERSIH DARI INVESTASI', `=D10`, false, true],
    ['', null],
    ['III. AKTIVITAS PENDANAAN', null, true],
    ['  Setoran Modal Pemilik', `=SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!D4:D500; "3-101"; 'INPUT: Jurnal Penyesuaian'!A4:A500; ">="&'Dashboard'!$G$5; 'INPUT: Jurnal Penyesuaian'!A4:A500; "<="&'Dashboard'!$H$5)`],
    ['  Prive / Pengambilan Pemilik', `=-SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!C4:C1000; "3-102"; 'INPUT: Beban & Kas Keluar'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Beban & Kas Keluar'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['ARUS KAS BERSIH DARI PENDANAAN', `=SUM(D14:D15)`, false, true],
    ['', null],
    ['KENAIKAN / (PENURUNAN) KAS BERSIH', `=D7+D11+D16`, false, false, true],
    ['Saldo Kas Awal Periode', `=SUMIF(COA!A4:A65; "1-10*"; COA!G4:G65)`],
    ['SALDO KAS AKHIR PERIODE', `=D18+D19`, false, false, true],
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

  sh.getRange('A2:C2').merge().setFormula('="RAABIHA CLOTHING & KONVEKSI — Periode: " & TEXT(\'Dashboard\'!G5;"dd/mm/yyyy") & " s/d " & TEXT(\'Dashboard\'!H5;"dd/mm/yyyy")')
    .setHorizontalAlignment('center').setFontSize(10);

  const items = [
    ['Modal Awal Periode', `=SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!D4:D500; "3-101"; 'INPUT: Jurnal Penyesuaian'!A4:A500; "<"&'Dashboard'!$G$5) + IFERROR(VLOOKUP("3-101"; COA!A:G; 7; FALSE); 0)`],
    ['(+) Tambahan Setoran Modal', `=SUMIFS('INPUT: Jurnal Penyesuaian'!H4:H500; 'INPUT: Jurnal Penyesuaian'!D4:D500; "3-101"; 'INPUT: Jurnal Penyesuaian'!A4:A500; ">="&'Dashboard'!$G$5; 'INPUT: Jurnal Penyesuaian'!A4:A500; "<="&'Dashboard'!$H$5)`],
    ["(+) Laba Bersih Tahun Ini", `=VLOOKUP("LABA BERSIH (NET PROFIT)"; 'Laba Rugi'!A:H; 8; FALSE)`],
    ['(-) Prive / Pengambilan Pemilik', `=SUMIFS('INPUT: Beban & Kas Keluar'!F4:F1000; 'INPUT: Beban & Kas Keluar'!C4:C1000; "3-102"; 'INPUT: Beban & Kas Keluar'!A4:A1000; ">="&'Dashboard'!$G$5; 'INPUT: Beban & Kas Keluar'!A4:A1000; "<="&'Dashboard'!$H$5)`],
    ['MODAL AKHIR PERIODE', '=C3+C4+C5-C6'],
  ];

  items.forEach((item, i) => {
    const r = 3 + i;
    sh.getRange(r, 1).setValue(item[0]);
    if (typeof item[1] === 'string' && item[1].startsWith('=')) {
      sh.getRange(r, 3).setFormula(item[1]).setNumberFormat('#,##0');
    }
  });

  sh.getRange(7, 1, 1, 3).setFontWeight('bold').setFontSize(11).setBackground(C.OK);
  sh.setColumnWidth(1, 320); sh.setColumnWidth(2, 30); sh.setColumnWidth(3, 160);
}

// ─────────────────────────────────────────────────────────
// 11. KALKULATOR PAJAK (TERHUBUNG KE DASHBOARD H5)
// ─────────────────────────────────────────────────────────

function buildKalkulatorPajak(ss) {
  const sh = makeSheet(ss, 'Kalkulator Pajak');
  title(sh, 'KALKULATOR PAJAK UMKM (PPh FINAL PP 55/2022)', 1, 5, C.INK);
  sh.getRange('A2:E2').merge()
    .setValue('Tarif 0% untuk omset ≤ Rp 500 Juta/tahun | Tarif 0,5% untuk Rp 500 Juta–Rp 4,8 Miliar/tahun')
    .setFontColor(C.MUTED).setFontSize(9).setWrap(true);
  sh.setRowHeight(2, 35);

  headerRow(sh, 3, ['Bulan', 'Omset Bulan Ini (Rp)', 'Akumulasi Omset (Rp)', 'Pajak Terhutang (Rp)', 'Status'], C.INK);

  const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  months.forEach((m, i) => {
    const r = 4 + i;
    const mNum = i + 1;
    sh.getRange(r, 1).setValue(m);
    sh.getRange(r, 2).setFormula(`=SUMIFS('INPUT: Penjualan'!K4:K1000; 'INPUT: Penjualan'!A4:A1000; ">="&DATE(YEAR('Dashboard'!$H$5); ${mNum}; 1); 'INPUT: Penjualan'!A4:A1000; "<="&EOMONTH(DATE(YEAR('Dashboard'!$H$5); ${mNum}; 1); 0))`);
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
  sh.getRange(tot, 5).setFormula('=IF(B16<=500000000;"BEBAS PAJAK";"Wajib Setor PPh Final")').setFontWeight('bold');

  sh.setColumnWidths(1, 5, 200);
}

// ─────────────────────────────────────────────────────────
// 12. DASHBOARD
// ─────────────────────────────────────────────────────────

function buildDashboard(ss) {
  const sh = makeSheet(ss, 'Dashboard');

  title(sh, 'DASHBOARD AKUNTANSI RAABIHA', 1, 8);
  sh.getRange('A2:H2').merge()
    .setFormula('="RAABIHA CLOTHING & KONVEKSI — Periode: " & TEXT(Dashboard!G5;"dd/mm/yyyy") & " s/d " & TEXT(Dashboard!H5;"dd/mm/yyyy")')
    .setHorizontalAlignment('center').setFontSize(10).setBackground(C.INK2).setFontColor(C.SLATE);

  headerRow(sh, 4, ['TOTAL PENJUALAN (GROSS)', 'DISKON & BIAYA MARKETPLACE', 'NET REVENUE', 'TOTAL BEBAN OPERASIONAL', 'LABA BERSIH', 'PAJAK TERHUTANG', 'TANGGAL MULAI', 'TANGGAL SELESAI'], C.INK);

  sh.getRange('A5').setFormula("=SUM('INPUT: Penjualan'!H4:H1000)").setNumberFormat('#,##0');
  sh.getRange('B5').setFormula("=SUM('INPUT: Penjualan'!I4:I1000)+SUM('INPUT: Penjualan'!J4:J1000)").setNumberFormat('#,##0');
  sh.getRange('C5').setFormula("=SUM('INPUT: Penjualan'!K4:K1000)").setNumberFormat('#,##0');
  sh.getRange('D5').setFormula("=SUM('INPUT: Beban & Kas Keluar'!F4:F1000)").setNumberFormat('#,##0');
  sh.getRange('E5').setFormula("=VLOOKUP(\"LABA BERSIH (NET PROFIT)\"; 'Laba Rugi'!A:H; 8; FALSE)").setNumberFormat('#,##0');
  sh.getRange('F5').setFormula("='Kalkulator Pajak'!D16").setNumberFormat('#,##0');
  sh.getRange('G5').setValue('01/01/2026');
  sh.getRange('H5').setValue('31/12/2026');
  setTanggal(sh.getRange('G5:H5'));

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

  sh.getRange(15, 1).setValue('TOTAL').setFontWeight('bold');
  for (let c = 2; c <= 5; c++) {
    sh.getRange(15, c).setFormula(`=SUM(${String.fromCharCode(64+c)}9:${String.fromCharCode(64+c)}14)`).setNumberFormat('#,##0').setFontWeight('bold');
  }
  sh.getRange(15, 1, 1, 6).setBackground(C.OK).setFontWeight('bold');

  sh.setColumnWidths(1, 8, 155);
}

// ─────────────────────────────────────────────────────────
// FUNGSI MANDIRI: TAMBAHKAN DUMMY PENGELUARAN WEB AKTUALL
// Jalankan fungsi ini jika hanya ingin menambah dummy Web ke Sheet yang sudah ada!
// ─────────────────────────────────────────────────────────

function tambahkanDummyPengeluaranWeb() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const shBeban = ss.getSheetByName('INPUT: Beban & Kas Keluar');

  if (!shBeban) {
    SpreadsheetApp.getUi().alert('Sheet "INPUT: Beban & Kas Keluar" tidak ditemukan!');
    return;
  }

  const dummyWebData = [
    [new Date('2026-08-01'), 'WEB-IT01', '6-110', '', 'Sewa Domain & Server Vercel Pro Web Raabiha', 350000, 'Kas Bank BCA', 'Sewa server & domain web'],
    [new Date('2026-08-05'), 'WEB-IT02', '6-110', '', 'Topup Saldo API Ongkir Biteship', 100000, 'Kas Bank BCA', 'Saldo kalkulasi ongkir otomatis'],
    [new Date('2026-08-10'), 'WEB-IT03', '6-110', '', 'Topup Saldo API Email Resend Notifikasi', 150000, 'Kas Bank BCA', 'Email notifikasi resi & order']
  ];

  let insertRow = 4;
  const values = shBeban.getRange('A4:A500').getValues();
  for (let i = 0; i < values.length; i++) {
    if (!values[i][0] || values[i][0] === '') {
      insertRow = 4 + i;
      break;
    }
  }

  shBeban.getRange(insertRow, 1, dummyWebData.length, 8).setValues(dummyWebData);

  for (let r = insertRow; r < insertRow + dummyWebData.length; r++) {
    shBeban.getRange(r, 4).setFormula(`=IF(C${r}="";"";IFERROR(VLOOKUP(C${r};COA!A:B;2;FALSE);"-"))`);
  }

  shBeban.getRange(`A${insertRow}:A${insertRow+2}`).setNumberFormat('dd/mm/yyyy');
  shBeban.getRange(`F${insertRow}:F${insertRow+2}`).setNumberFormat('#,##0');

  SpreadsheetApp.getUi().alert('Berhasil!\n\n3 Data dummy pengeluaran Web (Server, API Ongkir, API Email) sudah ditambahkan ke sheet "INPUT: Beban & Kas Keluar"!');
}

/**
 * FUNGSI INSPEKSI KONDISI AKTUAL SPREADSHEET
 */
function exportSpreadsheetStructure() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const sheets = ss.getSheets();
  const result = {
    title: ss.getName(),
    time: new Date().toISOString(),
    sheets: []
  };

  sheets.forEach(sheet => {
    const sheetName = sheet.getName();
    if (sheetName === '_INSPEKSI_JSON') return;

    const lastRow = sheet.getLastRow();
    const lastCol = sheet.getLastColumn();

    if (lastRow === 0 || lastCol === 0) {
      result.sheets.push({ name: sheetName, empty: true });
      return;
    }

    const maxScanRow = Math.min(lastRow, 50);
    const maxScanCol = Math.min(lastCol, 15);
    const range = sheet.getRange(1, 1, maxScanRow, maxScanCol);
    const values = range.getDisplayValues();
    const formulas = range.getFormulas();

    const keyFormulas = [];
    for (let r = 0; r < maxScanRow; r++) {
      for (let c = 0; c < maxScanCol; c++) {
        if (formulas[r][c] && formulas[r][c] !== '') {
          let colStr = '';
          let tempCol = c + 1;
          while (tempCol > 0) {
            let rem = (tempCol - 1) % 26;
            colStr = String.fromCharCode(65 + rem) + colStr;
            tempCol = Math.floor((tempCol - rem) / 26);
          }
          keyFormulas.push({ cell: colStr + (r + 1), formula: formulas[r][c] });
        }
      }
    }

    result.sheets.push({
      name: sheetName, rows: lastRow, cols: lastCol, layoutSample: values.slice(0, 10), formulas: keyFormulas
    });
  });

  const jsonString = JSON.stringify(result);
  let inspectSheet = ss.getSheetByName('_INSPEKSI_JSON');
  if (!inspectSheet) {
    inspectSheet = ss.insertSheet('_INSPEKSI_JSON');
  } else {
    inspectSheet.clear();
  }

  inspectSheet.getRange('A1').setValue('SALIN TEKS DI SEL A2 (DAN A3 JIKA ADA) DAN BERIKAN KE CHAT:').setFontWeight('bold');
  const chunkSize = 30000;
  let row = 2;
  for (let i = 0; i < jsonString.length; i += chunkSize) {
    const chunk = jsonString.substring(i, i + chunkSize);
    inspectSheet.getRange(row, 1).setValue(chunk);
    row++;
  }
  inspectSheet.setColumnWidth(1, 1000);
  SpreadsheetApp.getUi().alert('Inspeksi Berhasil!\n\nData JSON tersimpan di sheet "_INSPEKSI_JSON" pada sel A2 (dan A3 jika panjang). Silakan salin dan paste ke chat!');
}

/**
 * Audit Balance Akuntansi & Diagnosa Selisih
 * Memeriksa keseimbangan Neraca (Aset vs Kewajiban + Ekuitas) secara independen.
 */
function auditBalanceAkuntansi() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const ui = SpreadsheetApp.getUi();

  const shCOA = ss.getSheetByName('COA');
  const shJual = ss.getSheetByName('INPUT: Penjualan');
  const shBeli = ss.getSheetByName('INPUT: Pembelian & Produksi');
  const shBeban = ss.getSheetByName('INPUT: Beban & Kas Keluar');
  const shAJP = ss.getSheetByName('INPUT: Jurnal Penyesuaian');
  const shLR = ss.getSheetByName('Laba Rugi');
  const shNeraca = ss.getSheetByName('Posisi Keuangan');

  if (!shCOA || !shNeraca || !shLR) {
    ui.alert('Error', 'Sheet COA, Laba Rugi, atau Posisi Keuangan tidak ditemukan!', ui.ButtonSet.OK);
    return;
  }

  // Helper get values
  const getVals = (sh) => sh ? sh.getDataRange().getValues() : [];
  const coaData = getVals(shCOA);
  const jualData = getVals(shJual);
  const beliData = getVals(shBeli);
  const bebanData = getVals(shBeban);
  const ajpData = getVals(shAJP);

  // 1. Saldo Awal COA (Kolom G)
  let saldoAwalMap = {};
  for (let i = 3; i < coaData.length; i++) {
    let code = String(coaData[i][0]).trim();
    let initBal = parseFloat(coaData[i][6]) || 0;
    if (code) saldoAwalMap[code] = initBal;
  }

  // 2. Hitung Mutasi per Akun
  let debetMap = {}, kreditMap = {};
  const addDebet = (code, val) => { if (code && val) debetMap[code] = (debetMap[code] || 0) + val; };
  const addKredit = (code, val) => { if (code && val) kreditMap[code] = (kreditMap[code] || 0) + val; };

  // Penjualan: D=Kas/Piutang (Debet), F=Penjualan (Kredit), J=Biaya Marketplace (Debet 6-108/6-110)
  for (let i = 3; i < jualData.length; i++) {
    let tgl = jualData[i][0];
    if (!tgl) continue;
    let kasAcc = String(jualData[i][3]).trim();
    let jualAcc = String(jualData[i][5]).trim();
    let gross = parseFloat(jualData[i][7]) || 0;
    let diskon = parseFloat(jualData[i][8]) || 0;
    let fee = parseFloat(jualData[i][9]) || 0;
    let net = parseFloat(jualData[i][10]) || (gross - diskon - fee);

    addDebet(kasAcc, net);
    addKredit(jualAcc, gross - diskon);
    if (fee > 0) addDebet('6-108', fee);
  }

  // Pembelian & Produksi: K=Debet, M=Kredit
  for (let i = 3; i < beliData.length; i++) {
    let tgl = beliData[i][0];
    if (!tgl) continue;
    let debAcc = String(beliData[i][10]).trim();
    let kreAcc = String(beliData[i][12]).trim();
    let tot = parseFloat(beliData[i][8]) || 0;

    addDebet(debAcc, tot);
    addKredit(kreAcc, tot);
  }

  // Beban & Kas Keluar: C=Akun Beban (Debet), G=Sumber Kas (Kredit)
  for (let i = 3; i < bebanData.length; i++) {
    let tgl = bebanData[i][0];
    if (!tgl) continue;
    let bebAcc = String(bebanData[i][2]).trim();
    let sumName = String(bebanData[i][6]).trim();
    let nom = parseFloat(bebanData[i][5]) || 0;

    addDebet(bebAcc, nom);
    let sumAcc = '1-102'; // default Kas Bank BCA
    if (sumName.includes('Kas Toko')) sumAcc = '1-101';
    else if (sumName.includes('Mandiri')) sumAcc = '1-103';
    addKredit(sumAcc, nom);
  }

  // Jurnal Penyesuaian: D=Debet, F=Kredit
  for (let i = 3; i < ajpData.length; i++) {
    let tgl = ajpData[i][0];
    if (!tgl) continue;
    let debAcc = String(ajpData[i][3]).trim();
    let kreAcc = String(ajpData[i][5]).trim();
    let nom = parseFloat(ajpData[i][7]) || 0;

    addDebet(debAcc, nom);
    addKredit(kreAcc, nom);
  }

  // 3. Kalkulasi Saldo Akhir Aset (Kas & Piutang)
  const getBalance = (code, type) => {
    let sa = saldoAwalMap[code] || 0;
    let d = debetMap[code] || 0;
    let k = kreditMap[code] || 0;
    return type === 'Aset' ? (sa + d - k) : (sa + k - d);
  };

  let kasPOS = getBalance('1-101', 'Aset');
  let kasBCA = getBalance('1-102', 'Aset');
  let kasMandiri = getBalance('1-103', 'Aset');
  let piutangShopee = getBalance('1-201', 'Aset');
  let piutangLazada = getBalance('1-202', 'Aset');
  let piutangTokopedia = getBalance('1-203', 'Aset');
  let piutangTikTok = getBalance('1-204', 'Aset');
  let piutangWeb = getBalance('1-205', 'Aset');
  let persediaan = (saldoAwalMap['1-301'] || 0) + (debetMap['5-201'] || 0) - (kreditMap['5-201'] || 0);
  let asetTetapNet = (saldoAwalMap['1-401'] || 0) - (kreditMap['1-402'] || 0);

  let totalAsetAudited = kasPOS + kasBCA + kasMandiri + piutangShopee + piutangLazada + piutangTokopedia + piutangTikTok + piutangWeb + persediaan + asetTetapNet;

  // 4. Kalkulasi Laba Bersih Murni Usaha (Tanpa Prive)
  let totPenjualan = (kreditMap['4-101']||0) + (kreditMap['4-102']||0) + (kreditMap['4-103']||0) + (kreditMap['4-104']||0) + (kreditMap['4-105']||0) + (kreditMap['4-106']||0);
  let totHPP = (debetMap['5-201']||0) + (debetMap['5-202']||0) + (debetMap['5-203']||0);
  let totBebanOperasionalUsaha = 0;
  for (let acc in debetMap) {
    if (acc.startsWith('6-') && acc !== '3-102') {
      totBebanOperasionalUsaha += debetMap[acc];
    }
  }
  let labaBersihAudited = totPenjualan - totHPP - totBebanOperasionalUsaha;

  // 5. Ekuitas Audited
  let modalAwalAudited = (kasBCA > 0 ? (saldoAwalMap['1-102']||0) : 0) + persediaan + asetTetapNet; // atau dari modal terdaftar
  let priveAudited = debetMap['3-102'] || 0;
  let totalEkuitasAudited = modalAwalAudited - priveAudited + labaBersihAudited;

  let totalKewajibanAudited = (kreditMap['2-101']||0) - (debetMap['2-101']||0);
  let totalPasivaAudited = totalKewajibanAudited + totalEkuitasAudited;

  // 6. Angka di Sheet Neraca Saat Ini
  let totalAsetSheet = parseFloat(shNeraca.getRange('C19').getValue()) || parseFloat(shNeraca.getRange('C18').getValue()) || 0;
  let totalPasivaSheet = parseFloat(shNeraca.getRange('F14').getValue()) || parseFloat(shNeraca.getRange('F13').getValue()) || 0;

  let diffSheet = totalPasivaSheet - totalAsetSheet;
  let isBalancedSheet = Math.abs(diffSheet) < 1;

  let msg = "=== HASIL AUDIT KESEIMBANGAN AKUNTANSI ===\n\n";
  msg += `1. KONDISI SHEET NERACA SAAT INI:\n`;
  msg += `   • Total Aset (Sheet): Rp ${totalAsetSheet.toLocaleString('id-ID')}\n`;
  msg += `   • Total Kewajiban & Ekuitas (Sheet): Rp ${totalPasivaSheet.toLocaleString('id-ID')}\n`;
  msg += `   • Status: ${isBalancedSheet ? "✅ BALANCE 100%" : "❌ UNBALANCED (Selisih Rp " + diffSheet.toLocaleString('id-ID') + ")"}\n\n`;

  msg += `2. AUDIT HITUNGAN AKTUAL INDEPENDEN:\n`;
  msg += `   • Kas Toko (POS): Rp ${kasPOS.toLocaleString('id-ID')}\n`;
  msg += `   • Kas Bank BCA (inc. Saldo Awal): Rp ${kasBCA.toLocaleString('id-ID')}\n`;
  msg += `   • Piutang Web Raabiha (Saldo Xendit): Rp ${piutangWeb.toLocaleString('id-ID')}\n`;
  msg += `   • Total Audited Aset: Rp ${totalAsetAudited.toLocaleString('id-ID')}\n`;
  msg += `   • Total Audited Ekuitas: Rp ${totalEkuitasAudited.toLocaleString('id-ID')}\n\n`;

  if (!isBalancedSheet) {
    msg += `💡 CATATAN PERBAIKAN:\n`;
    msg += `   Jalankan fungsi "perbaikiRumusNeracaDanLabaRugi" pada menu Apps Script untuk memperbaiki seluruh rumus Neraca & Laba Rugi secara otomatis sehingga 100% BALANCE!`;
  } else {
    msg += `🎉 Pembukuan kamu sudah 100% Balance dan sesuai prinsip akuntansi standar!`;
  }

  ui.alert('Audit Balance Akuntansi', msg, ui.ButtonSet.OK);
}

/**
 * Perbaiki Rumus Neraca & Laba Rugi Secara Otomatis
 * Memperbaiki seluruh rumus di Posisi Keuangan & Laba Rugi agar 100% Balance.
 */
function perbaikiRumusNeracaDanLabaRugi() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const ui = SpreadsheetApp.getUi();
  const shNeraca = ss.getSheetByName('Posisi Keuangan');
  const shLR = ss.getSheetByName('Laba Rugi');

  if (!shNeraca || !shLR) {
    ui.alert('Error', 'Sheet Posisi Keuangan atau Laba Rugi tidak ditemukan!', ui.ButtonSet.OK);
    return;
  }

  // 1. Update Laba Rugi Baris 15 Teks & Total Beban Operasional Usaha
  shLR.getRange('A15').setValue('Biaya Jasa Marketplace & Payment Gateway');

  // 2. Update Rumus Aset Lancar di Neraca membaca Saldo Akhir dari COA (Kolom H)
  shNeraca.getRange('C5').setFormula('=IFERROR(VLOOKUP("1-101"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('C6').setFormula('=IFERROR(VLOOKUP("1-102"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('C7').setFormula('=IFERROR(VLOOKUP("1-103"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('C8').setFormula('=IFERROR(VLOOKUP("1-201"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('C9').setFormula('=IFERROR(VLOOKUP("1-202"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('C10').setFormula('=IFERROR(VLOOKUP("1-203"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('C11').setFormula('=IFERROR(VLOOKUP("1-204"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('A12').setValue('  Piutang Web Raabiha (Saldo Xendit)');
  shNeraca.getRange('C12').setFormula('=IFERROR(VLOOKUP("1-205"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('C13').setFormula('=IFERROR(VLOOKUP("1-301"; COA!A:H; 8; FALSE); 0)');
  shNeraca.getRange('C14').setFormula('=IFERROR(VLOOKUP("1-303"; COA!A:H; 8; FALSE); 0)');

  // 3. Aset Tetap
  shNeraca.getRange('C17').setFormula('=IFERROR(VLOOKUP("1-401"; COA!A:H; 8; FALSE); 0) - IFERROR(VLOOKUP("1-402"; COA!A:H; 8; FALSE); 0)');

  // 4. Update Total Aset Lancar & Total Aset
  shNeraca.getRange('A15').setValue('Total Aset Lancar');
  shNeraca.getRange('C15').setFormula('=SUM(C5:C14)');

  shNeraca.getRange('A19').setValue('TOTAL ASET');
  shNeraca.getRange('C19').setFormula('=C15+C18');

  // 5. Update Ekuitas Neraca (Dinamis dari Saldo Awal COA & Prive)
  shNeraca.getRange('E9').setValue('Modal Pemilik (Saldo Awal)');
  shNeraca.getRange('F9').setFormula('=SUMIF(COA!A4:A65; "1-10*"; COA!G4:G65) + IFERROR(VLOOKUP("1-301"; COA!A:G; 7; FALSE); 0) + IFERROR(VLOOKUP("1-401"; COA!A:G; 7; FALSE); 0)');

  shNeraca.getRange('E10').setValue('  Prive / Pengambilan Pemilik');
  shNeraca.getRange('F10').setFormula('=-SUMIFS(\'INPUT: Beban & Kas Keluar\'!F4:F1000; \'INPUT: Beban & Kas Keluar\'!C4:C1000; "3-102"; \'INPUT: Beban & Kas Keluar\'!A4:A1000; "<="&\'Dashboard\'!$H$5)');

  shNeraca.getRange('E11').setValue('  Laba Bersih Tahun Ini');
  shNeraca.getRange('F11').setFormula('=VLOOKUP("LABA BERSIH (NET PROFIT)"; \'Laba Rugi\'!A:H; 8; FALSE)');

  shNeraca.getRange('E12').setValue('Total Ekuitas');
  shNeraca.getRange('F12').setFormula('=SUM(F9:F11)');

  shNeraca.getRange('E14').setValue('TOTAL KEWAJIBAN & EKUITAS');
  shNeraca.getRange('F14').setFormula('=F6+F12');


  SpreadsheetApp.getUi().alert(
    'Perbaikan Selesai!\n\nSeluruh rumus di sheet Posisi Keuangan (Neraca) & Laba Rugi telah diperbaiki.\nSilakan jalankan kembali "auditBalanceAkuntansi" untuk mengecek keseimbangannya!'
  );
}

/**
 * 1. FUNGSI KHUSUS: KOSONGKAN SEMUA DATA INPUT
 * Menghapus seluruh data transaksi di Penjualan, Pembelian, Beban, Penyesuaian & Saldo Awal COA.
 */
function kosongkanSemuaDataInput() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const ui = SpreadsheetApp.getUi();

  const confirm = ui.alert(
    'Konfirmasi Hapus Data',
    'Apakah Anda yakin ingin MENGHAPUS SELURUH DATA TRANSAKSI di sheet Penjualan, Pembelian, Beban, Penyesuaian, dan Saldo Awal COA?\n\nSheet akan menjadi kosong bersih (0).',
    ui.ButtonSet.YES_NO
  );

  if (confirm !== ui.Button_YES) return;

  // 1. Reset Saldo Awal COA (G4:G65)
  const shCOA = ss.getSheetByName('COA');
  if (shCOA) {
    shCOA.getRange('G4:G65').setValue(0);
  }

  // 2. Kosongkan Penjualan (A4:D500, F4:F500, H4:J500, L4:L500)
  const shJual = ss.getSheetByName('INPUT: Penjualan');
  if (shJual) {
    shJual.getRange('A4:D500').clearContent();
    shJual.getRange('F4:F500').clearContent();
    shJual.getRange('H4:J500').clearContent();
    shJual.getRange('L4:L500').clearContent();
  }

  // 3. Kosongkan Pembelian (A4:H500, J4:K500, M4:M500)
  const shBeli = ss.getSheetByName('INPUT: Pembelian & Produksi');
  if (shBeli) {
    shBeli.getRange('A4:H500').clearContent();
    shBeli.getRange('J4:K500').clearContent();
    shBeli.getRange('M4:M500').clearContent();
  }

  // 4. Kosongkan Beban (A4:C500, E4:H500)
  const shBeban = ss.getSheetByName('INPUT: Beban & Kas Keluar');
  if (shBeban) {
    shBeban.getRange('A4:C500').clearContent();
    shBeban.getRange('E4:H500').clearContent();
  }

  // 5. Kosongkan Penyesuaian (A4:D500, F4:F500, H4:H500)
  const shAJP = ss.getSheetByName('INPUT: Jurnal Penyesuaian');
  if (shAJP) {
    shAJP.getRange('A4:D500').clearContent();
    shAJP.getRange('F4:F500').clearContent();
    shAJP.getRange('H4:H500').clearContent();
  }

  SpreadsheetApp.flush();
  ui.alert('Pembersihan Selesai!\n\nSeluruh data transaksi di sheet Penjualan, Pembelian, Beban, Penyesuaian, dan Saldo Awal COA telah KOSONG BERSIH TOTAL (0)!');
}


/**
 * 2. FUNGSI KHUSUS: ISI DATA DUMMY HARMONIS
 * Mengisi sheet input dengan dataset simulasi Toko Raabiha yang 100% harmonis & BALANCE.
 */
function isiDataDummyHarmonis() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  const ui = SpreadsheetApp.getUi();

  // 1. Set Saldo Awal COA
  const shCOA = ss.getSheetByName('COA');
  if (shCOA) {
    shCOA.getRange('G4:G65').setValue(0);
    shCOA.getRange('G5').setValue(100000000);  // Kas Bank BCA (1-102)
    shCOA.getRange('G14').setValue(20000000);  // Persediaan Bahan Baku (1-301)
    shCOA.getRange('G22').setValue(120000000); // Modal Pemilik (3-101)
  }

  // 2. Isi Penjualan
  const shJual = ss.getSheetByName('INPUT: Penjualan');
  if (shJual) {
    const dummyPenjualan = [
      [new Date('2026-08-01'), 'POS-20260801', 'POS Toko', '1-101', '', '4-101', '', 5000000, 0, 0, '', 'Penjualan kasir toko harian'],
      [new Date('2026-08-02'), 'WEB-20260802', 'Web Raabiha', '1-205', '', '4-102', '', 3500000, 0, 24500, '', 'Penjualan web Raabiha (fee Xendit QRIS/VA)'],
      [new Date('2026-08-03'), 'SPE-20260803', 'Shopee', '1-201', '', '4-103', '', 15000000, 0, 2250000, '', 'Settlement Shopee minggu ke-1'],
      [new Date('2026-08-04'), 'TTK-20260804', 'TikTok Shop', '1-204', '', '4-106', '', 12000000, 0, 1800000, '', 'Settlement TikTok Shop minggu ke-1'],
      [new Date('2026-08-05'), 'TKP-20260805', 'Tokopedia', '1-203', '', '4-105', '', 6000000, 0, 600000, '', 'Settlement Tokopedia minggu ke-1'],
      [new Date('2026-08-06'), 'LZD-20260806', 'Lazada', '1-202', '', '4-104', '', 4000000, 0, 400000, '', 'Settlement Lazada minggu ke-1']
    ];
    shJual.getRange(4, 1, dummyPenjualan.length, 12).setValues(dummyPenjualan);

    for (let r = 4; r < 4 + dummyPenjualan.length; r++) {
      shJual.getRange(r, 5).setFormula(`=IF(D${r}="";"";IFERROR(VLOOKUP(D${r};COA!A:B;2;FALSE);"-"))`);
      shJual.getRange(r, 7).setFormula(`=IF(F${r}="";"";IFERROR(VLOOKUP(F${r};COA!A:B;2;FALSE);"-"))`);
      shJual.getRange(r, 11).setFormula(`=IF(H${r}="";"";H${r}-I${r}-J${r})`);
    }
    shJual.getRange(`A4:A9`).setNumberFormat('dd/mm/yyyy');
    shJual.getRange(`H4:K9`).setNumberFormat('#,##0');
  }

  // 3. Isi Pembelian & Produksi
  const shBeli = ss.getSheetByName('INPUT: Pembelian & Produksi');
  if (shBeli) {
    const dummyPembelian = [
      [new Date('2026-08-01'), 'BB010826', 'Bahan Baku', 'PT. Sunnytex', 'Beli Kain ity & Jersey Livina', 100, 'kg', 120000, '', 'Tunai', '5-201', '', '1-102', ''],
      [new Date('2026-08-03'), 'BP030826', 'Ongkos Jahit', 'Raabiha Produksi', 'Bayar Ongkos Jahit & Potong Batch 1', 500, 'pcs', 16000, '', 'Tunai', '5-202', '', '1-102', ''],
      [new Date('2026-08-05'), 'AK050826', 'Aksesoris', 'Toko Aksesoris Konveksi', 'Beli Kancing, Label, Polybag & Bross Logo', 1, 'paket', 2500000, '', 'Tunai', '5-203', '', '1-102', '']
    ];
    shBeli.getRange(4, 1, dummyPembelian.length, 14).setValues(dummyPembelian);

    for (let r = 4; r < 4 + dummyPembelian.length; r++) {
      shBeli.getRange(r, 9).setFormula(`=IF(F${r}="";"";F${r}*H${r})`);
      shBeli.getRange(r, 12).setFormula(`=IF(K${r}="";"";IFERROR(VLOOKUP(K${r};COA!A:B;2;FALSE);"-"))`);
      shBeli.getRange(r, 14).setFormula(`=IF(M${r}="";"";IFERROR(VLOOKUP(M${r};COA!A:B;2;FALSE);"-"))`);
    }
    shBeli.getRange(`A4:A6`).setNumberFormat('dd/mm/yyyy');
    shBeli.getRange(`H4:I6`).setNumberFormat('#,##0');
  }

  // 4. Isi Beban & Kas Keluar
  const shBeban = ss.getSheetByName('INPUT: Beban & Kas Keluar');
  if (shBeban) {
    const dummyBeban = [
      [new Date('2026-08-01'), 'B-SEWA01', '6-102', '', 'Sewa Ruko Toko Bulanan', 3000000, 'Kas Bank BCA', 'Pembayaran sewa ruko toko'],
      [new Date('2026-08-02'), 'B-GAJI01', '6-101', '', 'Gaji Karyawan Konveksi & Kasir Toko', 6500000, 'Kas Bank BCA', 'Gaji bulanan tim'],
      [new Date('2026-08-03'), 'B-UTIL01', '6-103', '', 'Tagihan Listrik PLN, Air PDAM & Wifi Biznet', 850000, 'Kas Bank BCA', 'Tagihan bulanan'],
      [new Date('2026-08-04'), 'B-OPS01', '6-109', '', 'Konsumsi Karyawan & Operasional Toko', 450000, 'Kas Bank BCA', 'Operasional harian'],
      [new Date('2026-08-05'), 'B-MKT01', '6-104', '', 'Topup Iklan TikTok Ads & Meta Ads', 2000000, 'Kas Bank BCA', 'Promosi digital toko'],
      [new Date('2026-08-06'), 'B-IT01', '6-110', '', 'Sewa Server Vercel, Domain & Topup API Biteship/Resend', 600000, 'Kas Bank BCA', 'Biaya operasional web'],
      [new Date('2026-08-07'), 'B-PRV01', '3-102', '', 'Pengambilan Pribadi Pemilik (Teh Dini)', 3000000, 'Kas Bank BCA', 'Prive pemilik toko']
    ];
    shBeban.getRange(4, 1, dummyBeban.length, 8).setValues(dummyBeban);

    for (let r = 4; r < 4 + dummyBeban.length; r++) {
      shBeban.getRange(r, 4).setFormula(`=IF(C${r}="";"";IFERROR(VLOOKUP(C${r};COA!A:B;2;FALSE);"-"))`);
    }
    shBeban.getRange(`A4:A10`).setNumberFormat('dd/mm/yyyy');
    shBeban.getRange(`F4:F10`).setNumberFormat('#,##0');
  }

  // 5. Isi Jurnal Penyesuaian
  const shAJP = ss.getSheetByName('INPUT: Jurnal Penyesuaian');
  if (shAJP) {
    const dummyAJP = [
      [new Date('2026-08-08'), 'AJP-XENDIT', 'Penarikan Saldo Xendit Web ke BCA', '1-102', '', '1-205', '', 3000000],
      [new Date('2026-08-09'), 'AJP-SPE', 'Penarikan Saldo Shopee ke BCA', '1-102', '', '1-201', '', 10000000],
      [new Date('2026-08-10'), 'AJP-TTK', 'Penarikan Saldo TikTok Shop ke BCA', '1-102', '', '1-204', '', 8000000],
      [new Date('2026-08-11'), 'AJP-PENY', 'Penyusutan Peralatan & Mesin Jahit Bulan Ini', '6-106', '', '1-402', '', 500000]
    ];
    shAJP.getRange(4, 1, dummyAJP.length, 8).setValues(dummyAJP);

    for (let r = 4; r < 4 + dummyAJP.length; r++) {
      shAJP.getRange(r, 5).setFormula(`=IF(D${r}="";"";IFERROR(VLOOKUP(D${r};COA!A:B;2;FALSE);"-"))`);
      shAJP.getRange(r, 7).setFormula(`=IF(F${r}="";"";IFERROR(VLOOKUP(F${r};COA!A:B;2;FALSE);"-"))`);
    }
    shAJP.getRange(`A4:A7`).setNumberFormat('dd/mm/yyyy');
    shAJP.getRange(`H4:H7`).setNumberFormat('#,##0');
  }

  // 6. Hubungkan Rumus Neraca & Laba Rugi
  perbaikiRumusNeracaDanLabaRugi();

  SpreadsheetApp.flush();
  ui.alert('Pengisian Data Dummy Selesai!\n\nData dummy baku Toko Raabiha telah berhasil diisikan.\nSilakan jalankan fungsi "auditBalanceAkuntansi" meengecek keseimbangannya!');
}

/**
 * 100% READ-ONLY: INSPEKSI SELURUH ISI SHEET INPUT & SALDO AWAL COA
 * Tidak mengubah 1 sel pun mau pun 1 rumus pun.
 * Hanya membaca data aktual dan menyimpannya di sheet _INSPEKSI_JSON.
 */
function inspeksiSemuaDataInputJSON() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();

  const getRows = (sheetName) => {
    let sh = ss.getSheetByName(sheetName);
    if (!sh) return [];
    let lastRow = sh.getLastRow();
    let lastCol = sh.getLastColumn();
    if (lastRow < 4) return [];
    return sh.getRange(4, 1, lastRow - 3, lastCol).getDisplayValues();
  };

  const coaRows = getRows('COA');
  const jualRows = getRows('INPUT: Penjualan');
  const beliRows = getRows('INPUT: Pembelian & Produksi');
  const bebanRows = getRows('INPUT: Beban & Kas Keluar');
  const ajpRows = getRows('INPUT: Jurnal Penyesuaian');

  // Filter only non-empty rows for efficiency
  const filterNonEmpty = (rows, dateColIdx = 0) => {
    return rows.filter(r => r[dateColIdx] && r[dateColIdx] !== '' && r[dateColIdx] !== '-');
  };

  const payload = {
    time: new Date().toISOString(),
    saldoAwalCOA: coaRows.filter(r => r[6] && r[6] !== '' && r[6] !== '0').map(r => ({ kode: r[0], nama: r[1], saldoAwal: r[6] })),
    penjualan: filterNonEmpty(jualRows, 0).map(r => ({ tgl: r[0], ref: r[1], channel: r[2], kasAcc: r[3], jualAcc: r[5], gross: r[7], diskon: r[8], biaya: r[9], net: r[10] })),
    pembelian: filterNonEmpty(beliRows, 0).map(r => ({ tgl: r[0], faktur: r[1], jenis: r[2], item: r[4], total: r[8], debAcc: r[10], kreAcc: r[12] })),
    beban: filterNonEmpty(bebanRows, 0).map(r => ({ tgl: r[0], bukti: r[1], bebAcc: r[2], nama: r[3], detail: r[4], nominal: r[5], sumber: r[6] })),
    penyesuaian: filterNonEmpty(ajpRows, 0).map(r => ({ tgl: r[0], ajp: r[1], detail: r[2], debAcc: r[3], kreAcc: r[5], nominal: r[7] }))
  };

  const jsonStr = JSON.stringify(payload, null, 2);
  let inspectSheet = ss.getSheetByName('_INSPEKSI_JSON');
  if (!inspectSheet) {
    inspectSheet = ss.insertSheet('_INSPEKSI_JSON');
  } else {
    inspectSheet.clear();
  }

  inspectSheet.getRange('A1').setValue('SALIN TEKS DI SEL A2 (DAN A3 JIKA ADA) UNTUK DIANALISIS:').setFontWeight('bold');

  const chunkSize = 30000;
  let row = 2;
  for (let i = 0; i < jsonStr.length; i += chunkSize) {
    inspectSheet.getRange(row, 1).setValue(jsonStr.substring(i, i + chunkSize));
    row++;
  }

  SpreadsheetApp.getUi().alert(
    'Inspeksi Read-Only Selesai!\n\nSeluruh data aktual dari 4 Sheet Input & COA berhasil dibaca TANPA MENGUBAH RUMUS MANAPUN.\n\nData tersimpan di sheet "_INSPEKSI_JSON" sel A2. Silakan salin dan berikan ke chat!'
  );
}





