/**
 * Google Apps Script - Sistem Akuntansi Semi-Otomatis (Retail & Konveksi)
 * Toko Baju & Konveksi Raabiha
 * 
 * Cara Penggunaan:
 * 1. Buka Google Sheets baru.
 * 2. Klik menu Ekstensi -> Apps Script.
 * 3. Hapus semua kode default, lalu paste kode ini.
 * 4. Simpan, pilih fungsi 'setupAkuntansiSystem', lalu klik 'Jalankan' (Run).
 * 5. Berikan izin akses script. Seluruh sheet & rumus otomatis dibuat!
 */

function setupAkuntansiSystem() {
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  
  // 1. Sheet COA (Chart of Accounts)
  createCOASheet(ss);
  
  // 2. Sheet Jurnal Umum (Master Data Entry)
  createJurnalUmumSheet(ss);
  
  // 3. Sheet Jurnal Penjualan
  createJurnalPenjualanSheet(ss);
  
  // 4. Sheet Jurnal Pembelian (Bahan Baku Konveksi & Stok Baju)
  createJurnalPembelianSheet(ss);
  
  // 5. Sheet Jurnal Pengeluaran Kas
  createJurnalPengeluaranSheet(ss);
  
  // 6. Sheet Laba Rugi (Otomatis)
  createLabaRugiSheet(ss);
  
  // 7. Sheet Posisi Keuangan / Neraca (Otomatis)
  createNeracaSheet(ss);
  
  // Hapus Sheet default 'Sheet1' jika ada
  const defaultSheet = ss.getSheetByName('Sheet1');
  if (defaultSheet && ss.getSheets().length > 1) {
    ss.deleteSheet(defaultSheet);
  }
  
  SpreadsheetApp.getUi().alert('Sistem Akuntansi Semi-Otomatis Berhasil Dibuat!');
}

function createCOASheet(ss) {
  let sheet = ss.getSheetByName('COA');
  if (!sheet) sheet = ss.insertSheet('COA');
  sheet.clear();
  
  const headers = [['Kode Akun', 'Nama Akun', 'Kategori', 'Pos Saldo (DB/CR)']];
  const coaData = [
    // ASET
    ['1-101', 'Kas Toko (POS)', 'Aset Lancar', 'DB'],
    ['1-102', 'Kas Bank BCA', 'Aset Lancar', 'DB'],
    ['1-103', 'Kas Bank Mandiri', 'Aset Lancar', 'DB'],
    ['1-108', 'Piutang Marketplace & Pelanggan', 'Aset Lancar', 'DB'],
    ['1-201', 'Persediaan Bahan Baku (Konveksi)', 'Persediaan', 'DB'],
    ['1-202', 'Persediaan Barang Jadi (Toko Baju)', 'Persediaan', 'DB'],
    ['1-301', 'Peralatan & Mesin Jahit', 'Aset Tetap', 'DB'],
    ['1-302', 'Akumulasi Penyusutan Peralatan', 'Aset Tetap', 'CR'],
    
    // KEWAJIBAN & EKUITAS
    ['2-101', 'Hutang Dagang / Supplier Bahan', 'Kewajiban', 'CR'],
    ['3-101', 'Modal Pemilik', 'Ekuitas', 'CR'],
    ['3-102', 'Prive Pemilik', 'Ekuitas', 'DB'],
    
    // PENDAPATAN
    ['4-101', 'Penjualan Toko & E-Commerce', 'Pendapatan', 'CR'],
    ['4-102', 'Penjualan Jasa Konveksi / Jahit', 'Pendapatan', 'CR'],
    ['4-201', 'Diskon Penjualan', 'Pendapatan', 'DB'],
    
    // HPP & BIAYA PRODUKSI
    ['5-101', 'HPP Penjualan Barang', 'HPP', 'DB'],
    ['5-201', 'Biaya Bahan Baku Konveksi', 'Biaya Produksi', 'DB'],
    ['5-202', 'Biaya Ongkos Jahit & Potong', 'Biaya Produksi', 'DB'],
    
    // BEBAN OPERASIONAL
    ['6-101', 'Beban Gaji Karyawan', 'Beban Operasional', 'DB'],
    ['6-102', 'Beban Sewa Tempat', 'Beban Operasional', 'DB'],
    ['6-103', 'Beban Listrik, Air & Internet', 'Beban Operasional', 'DB'],
    ['6-104', 'Beban Biaya Jasa Marketplace & Biaya Admin', 'Beban Operasional', 'DB'],
    ['6-105', 'Beban Transportasi & Packing', 'Beban Operasional', 'DB'],
  ];
  
  sheet.getRange(1, 1, 1, 4).setValues(headers).setFontWeight('bold').setBackground('#1e293b').setFontColor('#ffffff');
  sheet.getRange(2, 1, coaData.length, 4).setValues(coaData);
  sheet.autoResizeColumns(1, 4);
}

function createJurnalUmumSheet(ss) {
  let sheet = ss.getSheetByName('Jurnal Umum');
  if (!sheet) sheet = ss.insertSheet('Jurnal Umum');
  sheet.clear();
  
  const headers = [['Tanggal', 'No. Bukti', 'Keterangan', 'Kode Akun', 'Nama Akun', 'Debet (Rp)', 'Kredit (Rp)']];
  sheet.getRange(1, 1, 1, 7).setValues(headers).setFontWeight('bold').setBackground('#0f172a').setFontColor('#ffffff');
  
  // Format Tanggal & Angka
  sheet.getRange('A2:A1000').setNumberFormat('yyyy-mm-dd');
  sheet.getRange('F2:G1000').setNumberFormat('#,##0');
  
  // Rumus Otomatis VLOOKUP Nama Akun berdasarkan Kode Akun di Kolom D
  const formulaRange = sheet.getRange('E2:E1000');
  formulaRange.setFormula('=IF(D2="",""; IFERROR(VLOOKUP(D2; COA!A:B; 2; FALSE); "Kode Akun Tidak Ada"))');
  
  sheet.setColumnWidth(1, 110);
  sheet.setColumnWidth(2, 140);
  sheet.setColumnWidth(3, 240);
  sheet.setColumnWidth(4, 100);
  sheet.setColumnWidth(5, 220);
  sheet.setColumnWidth(6, 130);
  sheet.setColumnWidth(7, 130);
}

function createJurnalPenjualanSheet(ss) {
  let sheet = ss.getSheetByName('Jurnal Penjualan');
  if (!sheet) sheet = ss.insertSheet('Jurnal Penjualan');
  sheet.clear();
  
  const headers = [['Tanggal', 'No. Nota', 'Nama Pelanggan', 'Metode Pembayaran', 'Total Belanja (Rp)', 'Diskon (Rp)', 'Net Sales (Rp)', 'Status']];
  sheet.getRange(1, 1, 1, 8).setValues(headers).setFontWeight('bold').setBackground('#065f46').setFontColor('#ffffff');
  
  sheet.getRange('A2:A1000').setNumberFormat('yyyy-mm-dd');
  sheet.getRange('E2:G1000').setNumberFormat('#,##0');
  
  // Rumus Net Sales = Total Belanja - Diskon
  sheet.getRange('G2:G1000').setFormula('=IF(E2="",""; E2-F2)');
  
  sheet.setColumnWidth(1, 110);
  sheet.setColumnWidth(2, 150);
  sheet.setColumnWidth(3, 180);
  sheet.setColumnWidth(4, 150);
  sheet.setColumnWidth(5, 130);
  sheet.setColumnWidth(6, 110);
  sheet.setColumnWidth(7, 130);
  sheet.setColumnWidth(8, 110);
}

function createJurnalPembelianSheet(ss) {
  let sheet = ss.getSheetByName('Jurnal Pembelian');
  if (!sheet) sheet = ss.insertSheet('Jurnal Pembelian');
  sheet.clear();
  
  const headers = [['Tanggal', 'No. Faktur', 'Supplier / Toko', 'Jenis Item (Bahan/Baju)', 'Qty', 'Harga Satuan (Rp)', 'Total Pembelian (Rp)', 'Status Pembayaran']];
  sheet.getRange(1, 1, 1, 8).setValues(headers).setFontWeight('bold').setBackground('#1e3a8a').setFontColor('#ffffff');
  
  sheet.getRange('A2:A1000').setNumberFormat('yyyy-mm-dd');
  sheet.getRange('F2:G1000').setNumberFormat('#,##0');
  
  // Rumus Total Pembelian = Qty * Harga Satuan
  sheet.getRange('G2:G1000').setFormula('=IF(E2="",""; E2*F2)');
  
  sheet.setColumnWidth(1, 110);
  sheet.setColumnWidth(2, 140);
  sheet.setColumnWidth(3, 180);
  sheet.setColumnWidth(4, 180);
  sheet.setColumnWidth(5, 70);
  sheet.setColumnWidth(6, 130);
  sheet.setColumnWidth(7, 140);
  sheet.setColumnWidth(8, 140);
}

function createJurnalPengeluaranSheet(ss) {
  let sheet = ss.getSheetByName('Pengeluaran & Beban');
  if (!sheet) sheet = ss.insertSheet('Pengeluaran & Beban');
  sheet.clear();
  
  const headers = [['Tanggal', 'No. Bukti', 'Kategori Beban / Pengeluaran', 'Keterangan Detail', 'Nominal (Rp)', 'Sumber Uang (Kas/Bank)']];
  sheet.getRange(1, 1, 1, 6).setValues(headers).setFontWeight('bold').setBackground('#831843').setFontColor('#ffffff');
  
  sheet.getRange('A2:A1000').setNumberFormat('yyyy-mm-dd');
  sheet.getRange('E2:E1000').setNumberFormat('#,##0');
  
  sheet.setColumnWidth(1, 110);
  sheet.setColumnWidth(2, 140);
  sheet.setColumnWidth(3, 220);
  sheet.setColumnWidth(4, 250);
  sheet.setColumnWidth(5, 140);
  sheet.setColumnWidth(6, 180);
}

function createLabaRugiSheet(ss) {
  let sheet = ss.getSheetByName('Laba Rugi (Otomatis)');
  if (!sheet) sheet = ss.insertSheet('Laba Rugi (Otomatis)');
  sheet.clear();
  
  sheet.getRange('A1:C1').merge().setValue('LAPORAN LABA RUGI (OTOMATIS)').setFontWeight('bold').setFontSize(14).setHorizontalAlignment('center');
  sheet.getRange('A2:C2').merge().setValue('TOKO BAJU & KONVEKSI RAABIHA').setFontSize(11).setHorizontalAlignment('center');
  
  const structure = [
    ['PENDAPATAN USANA', '', ''],
    ['Penjualan Toko & E-Commerce', '', '=SUMIF(\'Jurnal Umum\'!F:F; "4-101"; \'Jurnal Umum\'!G:G) + SUM(\'Jurnal Penjualan\'!G:G)'],
    ['Penjualan Jasa Konveksi / Jahit', '', '=SUMIF(\'Jurnal Umum\'!F:F; "4-102"; \'Jurnal Umum\'!G:G)'],
    ['Diskon Penjualan', '', '=-SUMIF(\'Jurnal Umum\'!F:F; "4-201"; \'Jurnal Umum\'!F:F) - SUM(\'Jurnal Penjualan\'!F:F)'],
    ['TOTAL PENDAPATAN BERSIH', '', '=SUM(C4:C6)'],
    ['', '', ''],
    ['HARGA POKOK PENJUALAN & PRODUKSI', '', ''],
    ['HPP Penjualan Barang', '', '=SUMIF(\'Jurnal Umum\'!F:F; "5-101"; \'Jurnal Umum\'!F:F)'],
    ['Biaya Bahan Baku Konveksi', '', '=SUMIF(\'Jurnal Umum\'!F:F; "5-201"; \'Jurnal Umum\'!F:F) + SUMIF(\'Jurnal Pembelian\'!D:D; "*Bahan*"; \'Jurnal Pembelian\'!G:G)'],
    ['Biaya Ongkos Jahit & Potong', '', '=SUMIF(\'Jurnal Umum\'!F:F; "5-202"; \'Jurnal Umum\'!F:F)'],
    ['TOTAL HPP & BIAYA PRODUKSI', '', '=SUM(C9:C11)'],
    ['', '', ''],
    ['LABA KOTOR', '', '=C7-C12'],
    ['', '', ''],
    ['BEBAN OPERASIONAL', '', ''],
    ['Beban Gaji Karyawan', '', '=SUMIF(\'Jurnal Umum\'!F:F; "6-101"; \'Jurnal Umum\'!F:F) + SUMIF(\'Pengeluaran & Beban\'!C:C; "*Gaji*"; \'Pengeluaran & Beban\'!E:E)'],
    ['Beban Sewa Tempat', '', '=SUMIF(\'Jurnal Umum\'!F:F; "6-102"; \'Jurnal Umum\'!F:F) + SUMIF(\'Pengeluaran & Beban\'!C:C; "*Sewa*"; \'Pengeluaran & Beban\'!E:E)'],
    ['Beban Listrik, Air & Internet', '', '=SUMIF(\'Jurnal Umum\'!F:F; "6-103"; \'Jurnal Umum\'!F:F) + SUMIF(\'Pengeluaran & Beban\'!C:C; "*Listrik*"; \'Pengeluaran & Beban\'!E:E)'],
    ['Beban Biaya Jasa Marketplace & Biaya Admin', '', '=SUMIF(\'Jurnal Umum\'!F:F; "6-104"; \'Jurnal Umum\'!F:F)'],
    ['Beban Transportasi & Packing', '', '=SUMIF(\'Jurnal Umum\'!F:F; "6-105"; \'Jurnal Umum\'!F:F) + SUMIF(\'Pengeluaran & Beban\'!C:C; "*Transport*"; \'Pengeluaran & Beban\'!E:E)'],
    ['TOTAL BEBAN OPERASIONAL', '', '=SUM(C16:C20)'],
    ['', '', ''],
    ['LABA BERSIH (NET PROFIT)', '', '=C13-C21']
  ];
  
  sheet.getRange(3, 1, structure.length, 3).setValues(structure);
  sheet.getRange('C4:C23').setNumberFormat('#,##0');
  
  // Format Header Sections
  sheet.getRange('A3:C3').setFontWeight('bold').setBackground('#e2e8f0');
  sheet.getRange('A7:C7').setFontWeight('bold').setBackground('#f1f5f9');
  sheet.getRange('A8:C8').setFontWeight('bold').setBackground('#e2e8f0');
  sheet.getRange('A12:C12').setFontWeight('bold').setBackground('#f1f5f9');
  sheet.getRange('A13:C13').setFontWeight('bold').setBackground('#dcfce7'); // Laba Kotor
  sheet.getRange('A15:C15').setFontWeight('bold').setBackground('#e2e8f0');
  sheet.getRange('A21:C21').setFontWeight('bold').setBackground('#f1f5f9');
  sheet.getRange('A23:C23').setFontWeight('bold').setFontSize(11).setBackground('#bbf7d0'); // Laba Bersih
  
  sheet.setColumnWidth(1, 280);
  sheet.setColumnWidth(2, 30);
  sheet.setColumnWidth(3, 160);
}

function createNeracaSheet(ss) {
  let sheet = ss.getSheetByName('Posisi Keuangan (Neraca)');
  if (!sheet) sheet = ss.insertSheet('Posisi Keuangan (Neraca)');
  sheet.clear();
  
  sheet.getRange('A1:D1').merge().setValue('LAPORAN POSISI KEUANGAN (NERACA)').setFontWeight('bold').setFontSize(14).setHorizontalAlignment('center');
  sheet.getRange('A2:D2').merge().setValue('TOKO BAJU & KONVEKSI RAABIHA').setFontSize(11).setHorizontalAlignment('center');
  
  const structure = [
    ['ASET (AKTIVA)', '', 'KEWAJIBAN & EKUITAS (PASIVA)', ''],
    ['Kas Toko (POS)', '=SUMIF(\'Jurnal Umum\'!F:F; "1-101"; \'Jurnal Umum\'!F:F) - SUMIF(\'Jurnal Umum\'!F:F; "1-101"; \'Jurnal Umum\'!G:G) + SUMIF(\'Jurnal Penjualan\'!D:D; "*Tunai*"; \'Jurnal Penjualan\'!G:G)', 'Hutang Dagang / Supplier', '=SUMIF(\'Jurnal Pembelian\'!H:H; "*Hutang*"; \'Jurnal Pembelian\'!G:G) + SUMIF(\'Jurnal Umum\'!F:F; "2-101"; \'Jurnal Umum\'!G:G) - SUMIF(\'Jurnal Umum\'!F:F; "2-101"; \'Jurnal Umum\'!F:F)'],
    ['Kas Bank BCA', '=SUMIF(\'Jurnal Umum\'!F:F; "1-102"; \'Jurnal Umum\'!F:F) - SUMIF(\'Jurnal Umum\'!F:F; "1-102"; \'Jurnal Umum\'!G:G)', 'TOTAL KEWAJIBAN', '=B4'],
    ['Kas Bank Mandiri', '=SUMIF(\'Jurnal Umum\'!F:F; "1-103"; \'Jurnal Umum\'!F:F) - SUMIF(\'Jurnal Umum\'!F:F; "1-103"; \'Jurnal Umum\'!G:G)', '', ''],
    ['Piutang Marketplace & Pelanggan', '=SUMIF(\'Jurnal Umum\'!F:F; "1-108"; \'Jurnal Umum\'!F:F) - SUMIF(\'Jurnal Umum\'!F:F; "1-108"; \'Jurnal Umum\'!G:G)', 'Modal Pemilik', '=SUMIF(\'Jurnal Umum\'!F:F; "3-101"; \'Jurnal Umum\'!G:G) - SUMIF(\'Jurnal Umum\'!F:F; "3-101"; \'Jurnal Umum\'!F:F)'],
    ['Persediaan Bahan Baku', '=SUMIF(\'Jurnal Pembelian\'!D:D; "*Bahan*"; \'Jurnal Pembelian\'!G:G) + SUMIF(\'Jurnal Umum\'!F:F; "1-201"; \'Jurnal Umum\'!F:F) - SUMIF(\'Jurnal Umum\'!F:F; "1-201"; \'Jurnal Umum\'!G:G)', 'Prive Pemilik', '=-SUMIF(\'Jurnal Umum\'!F:F; "3-102"; \'Jurnal Umum\'!F:F)'],
    ['Persediaan Barang Jadi', '=SUMIF(\'Jurnal Pembelian\'!D:D; "*Baju*"; \'Jurnal Pembelian\'!G:G) + SUMIF(\'Jurnal Umum\'!F:F; "1-202"; \'Jurnal Umum\'!F:F) - SUMIF(\'Jurnal Umum\'!F:F; "1-202"; \'Jurnal Umum\'!G:G)', 'Laba Tahun Berjalan', '=\'Laba Rugi (Otomatis)\'!C23'],
    ['Peralatan & Mesin Jahit', '=SUMIF(\'Jurnal Umum\'!F:F; "1-301"; \'Jurnal Umum\'!F:F) - SUMIF(\'Jurnal Umum\'!F:F; "1-302"; \'Jurnal Umum\'!G:G)', 'TOTAL EKUITAS', '=SUM(D7:D9)'],
    ['', '', '', ''],
    ['TOTAL ASET', '=SUM(B4:B10)', 'TOTAL KEWAJIBAN & EKUITAS', '=D5+D10']
  ];
  
  sheet.getRange(3, 1, structure.length, 4).setValues(structure);
  sheet.getRange('B4:B12').setNumberFormat('#,##0');
  sheet.getRange('D4:D12').setNumberFormat('#,##0');
  
  sheet.getRange('A3:B3').setFontWeight('bold').setBackground('#1e293b').setFontColor('#ffffff');
  sheet.getRange('C3:D3').setFontWeight('bold').setBackground('#1e293b').setFontColor('#ffffff');
  sheet.getRange('A5:B5').setFontWeight('bold').setBackground('#f1f5f9');
  sheet.getRange('C5:D5').setFontWeight('bold').setBackground('#f1f5f9');
  sheet.getRange('C10:D10').setFontWeight('bold').setBackground('#f1f5f9');
  sheet.getRange('A12:B12').setFontWeight('bold').setBackground('#cbd5e1'); // Total Aset
  sheet.getRange('C12:D12').setFontWeight('bold').setBackground('#cbd5e1'); // Total Pasiva
  
  sheet.setColumnWidth(1, 230);
  sheet.setColumnWidth(2, 140);
  sheet.setColumnWidth(3, 230);
  sheet.setColumnWidth(4, 140);
}
