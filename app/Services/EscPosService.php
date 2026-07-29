<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PosSession;
use App\Models\SiteSetting;

class EscPosService
{
    protected string $buffer = "";
    protected int $charWidth = 32; // Default for 58mm

    const ALIGN_LEFT = "\x1b\x61\x00";
    const ALIGN_CENTER = "\x1b\x61\x01";
    const ALIGN_RIGHT = "\x1b\x61\x02";
    const BOLD_ON = "\x1b\x45\x01";
    const BOLD_OFF = "\x1b\x45\x00";
    const SIZE_NORMAL = "\x1d\x21\x00";
    const SIZE_DOUBLE = "\x1d\x21\x11";
    const INIT = "\x1b\x40";
    const LF = "\x0a";

    public function __construct()
    {
        $this->buffer = self::INIT;
        $paperSize = SiteSetting::where('key', 'pos_paper_size')->value('value') ?? '58';
        $this->charWidth = ($paperSize == '80') ? 48 : 32;
    }

    protected function add(string $command)
    {
        $this->buffer .= $command;
    }

    protected function text(string $text)
    {
        // Simple word wrap or truncate if needed, but for now just add text
        $this->add($text);
    }

    protected function line(string $text = "")
    {
        $this->add($text . self::LF);
    }

    protected function divider(string $char = "-")
    {
        $this->line(str_repeat($char, $this->charWidth));
    }

    protected function feed(int $lines = 3)
    {
        $this->add("\x1b\x64" . chr($lines));
    }

    protected function cut()
    {
        // Full cut
        $this->add("\x1d\x56\x00");
    }

    protected function drawer()
    {
        // Kick drawer pin 2
        $this->add("\x1b\x70\x00\x19\xfa");
    }

    protected function justify(string $left, string $right)
    {
        $leftLen = strlen($left);
        $rightLen = strlen($right);
        $space = $this->charWidth - $leftLen - $rightLen;
        if ($space < 1) $space = 1; // Minimum 1 space, might overflow line but okay for now
        $this->line($left . str_repeat(" ", $space) . $right);
    }

    protected function getSettings(array $keys): array
    {
        return SiteSetting::whereIn('key', $keys)->pluck('value', 'key')->all();
    }

    /**
     * Generate Base64 receipt for an Order
     */
    public function generateReceipt(Order $order, bool $isReprint = false): string
    {
        $this->buffer = self::INIT; // Reset buffer agar tidak tumpuk jika dipakai ulang via DI container

        // 1. Settings (Batch Query)
        $settings = $this->getSettings([
            'pos_receipt_header',
            'pos_receipt_footer',
            'pos_show_cashier_name',
            'pos_show_date',
            'pos_auto_cut',
            'pos_open_cash_drawer',
            'pos_loyalty_enabled',
        ]);

        $header         = $settings['pos_receipt_header'] ?? "TOKO RAABIHA";
        $footer         = $settings['pos_receipt_footer'] ?? "Terima Kasih";
        $showCashier    = filter_var($settings['pos_show_cashier_name'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $showDate       = filter_var($settings['pos_show_date'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $autoCut        = filter_var($settings['pos_auto_cut'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $openDrawer     = filter_var($settings['pos_open_cash_drawer'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $loyaltyEnabled = filter_var($settings['pos_loyalty_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);
        
        if ($openDrawer && !$isReprint) {
            $this->drawer();
        }

        // Header
        $this->add(self::ALIGN_CENTER);
        $this->add(self::BOLD_ON);
        foreach (explode("\n", $header) as $hLine) {
            $this->line(trim($hLine));
        }
        $this->add(self::BOLD_OFF);

        if ($isReprint) {
            $this->add(self::BOLD_ON);
            $this->line("*** SALINAN / REPRINT ***");
            $this->add(self::BOLD_OFF);
        }

        $this->line();

        // Metadata
        $this->add(self::ALIGN_LEFT);
        $this->line("Nota  : " . $order->order_number);
        if ($showDate && $order->created_at) {
            $this->line("Waktu : " . $order->created_at->format('d/m/Y H:i'));
        }
        if ($showCashier && $order->cashier) {
            $this->line("Kasir : " . $order->cashier->name);
        }
        if ($order->customer_name) {
            $this->line("Plgn  : " . $order->customer_name);
        }
        if ($order->customer_phone) {
            $this->line("No.HP : " . $order->customer_phone);
        }
        $this->divider();

        // Items
        foreach ($order->items as $item) {
            $name = $item->name ?: ($item->product_name ?? 'Produk');
            if (!empty($item->variant_name) && !str_contains($name, $item->variant_name)) {
                $name .= ' - ' . $item->variant_name;
            }
            $name = (string) $name;
            $wrappedName = wordwrap($name, $this->charWidth, "\n", true);
            foreach(explode("\n", $wrappedName) as $nLine) {
                $this->line($nLine);
            }
            
            $qtyStr = $item->quantity . "x " . number_format($item->price, 0, ',', '.');
            $subtotalStr = number_format($item->total ?? $item->subtotal ?? 0, 0, ',', '.');
            $this->justify("  " . $qtyStr, $subtotalStr);
        }
        $this->divider();

        // Totals
        // Totals
        $this->justify("Subtotal", number_format($order->subtotal, 0, ',', '.'));
        
        $paymentDetails = is_string($order->payment_details) ? json_decode($order->payment_details, true) : ($order->payment_details ?? []);
        $voucherDiscount = (float) ($paymentDetails['voucher_discount'] ?? 0);
        $manualDiscount = (float) ($paymentDetails['manual_discount'] ?? 0);
        
        if ($voucherDiscount > 0) {
            $voucherName = $order->voucher ? $order->voucher->name : 'Promo';
            $discRight = "-" . number_format($voucherDiscount, 0, ',', '.');
            $maxNameLen = $this->charWidth - strlen($discRight) - 7;
            if ($maxNameLen > 0 && strlen($voucherName) > $maxNameLen) {
                $voucherName = substr($voucherName, 0, $maxNameLen);
            }
            $this->justify("VCR (" . $voucherName . ")", $discRight);
        }
        
        if ($manualDiscount > 0) {
            $this->justify("Diskon", "-" . number_format($manualDiscount, 0, ',', '.'));
        }
        
        if ($order->discount_total > 0 && $voucherDiscount == 0 && $manualDiscount == 0) {
            // Fallback untuk transaksi lama
            $this->justify("Diskon", "-" . number_format($order->discount_total, 0, ',', '.'));
        }
        
        $this->add(self::BOLD_ON);
        $this->justify("TOTAL", number_format($order->grand_total, 0, ',', '.'));
        $this->add(self::BOLD_OFF);

        if ($order->cash_paid) {
            $this->line();
            $this->justify("Tunai", number_format($order->cash_paid, 0, ',', '.'));
            $this->justify("Kembali", number_format($order->cash_change ?? 0, 0, ',', '.'));
        }

        // Section Loyalti Stempel Digital (jika diaktifkan di setting & ada nomor HP)
        if ($loyaltyEnabled && $order->customer_phone) {
            $phone = \App\Models\PosCustomer::normalizePhone($order->customer_phone);
            $customer = $phone ? \App\Models\PosCustomer::where('phone', $phone)->first() : null;
            if ($customer) {
                $expiryMonths = (int) ($settings['pos_loyalty_stamp_expiry_months'] ?? 6);
                if ($expiryMonths <= 0) $expiryMonths = 6;
                $lastVisit = $customer->last_visit_at ?? $order->created_at ?? now();
                $expiryDate = $lastVisit->copy()->addMonths($expiryMonths)->format('d/m/Y');

                $this->divider();
                $this->add(self::ALIGN_CENTER);
                $this->add(self::BOLD_ON);
                $this->line("KARTU CAP DIGITAL RAABIHA");
                $this->add(self::BOLD_OFF);
                $this->line("Total Cap: " . $customer->stamp_count . " dari 9 Cap");
                
                // Visual Cap 3 baris x 3 kolom
                $c = $customer->stamp_count;
                $row1 = implode(" ", array_map(fn($i) => $i <= $c ? "[X]" : "[ ]", [1,2,3]));
                $row2 = implode(" ", array_map(fn($i) => $i <= $c ? "[X]" : "[ ]", [4,5,6]));
                $row3 = implode(" ", array_map(fn($i) => $i <= $c ? "[X]" : "[ ]", [7,8,9]));

                $this->line("Voucher 1 (15k): " . $row1);
                $this->line("Voucher 2 (20k): " . $row2);
                $this->line("Voucher 3 (25k): " . $row3);
                $this->line("Masa Berlaku: s/d " . $expiryDate);
            }
        }

        // Footer
        $this->line();
        $this->add(self::ALIGN_CENTER);
        foreach (explode("\n", $footer) as $fLine) {
            $this->line(trim($fLine));
        }

        // Feed & Cut
        $this->feed(4);
        if ($autoCut) {
            $this->cut();
        }

        $singleReceiptBuffer = $this->buffer;
        $copies = intval($settings['pos_print_copies'] ?? 1);
        if ($copies > 1 && !$isReprint) {
            for ($i = 1; $i < $copies; $i++) {
                $this->buffer .= $singleReceiptBuffer;
            }
        }

        return base64_encode($this->buffer);
    }

    /**
     * Generate Plain Text Struk (Preview di Layar POS) - Dinamis mengikuti SiteSetting
     */
    public function generateReceiptText(Order $order, bool $isReprint = false): string
    {
        $width = $this->charWidth;

        $settings = $this->getSettings([
            'pos_receipt_header',
            'pos_receipt_footer',
            'pos_show_cashier_name',
            'pos_show_date',
            'pos_loyalty_enabled',
        ]);

        $header         = $settings['pos_receipt_header'] ?? "TOKO RAABIHA";
        $footer         = $settings['pos_receipt_footer'] ?? "Terima Kasih";
        $showCashier    = filter_var($settings['pos_show_cashier_name'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $showDate       = filter_var($settings['pos_show_date'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $loyaltyEnabled = filter_var($settings['pos_loyalty_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN);

        $lines = [];

        foreach (explode("\n", $header) as $hLine) {
            $lines[] = str_pad(trim($hLine), $width, ' ', STR_PAD_BOTH);
        }

        if ($isReprint) {
            $lines[] = str_pad("*** SALINAN / REPRINT ***", $width, ' ', STR_PAD_BOTH);
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = "Nota  : " . $order->order_number;
        if ($showDate && $order->created_at) {
            $lines[] = "Waktu : " . $order->created_at->format('d/m/Y H:i');
        }
        if ($showCashier && $order->cashier) {
            $lines[] = "Kasir : " . $order->cashier->name;
        }
        if ($order->customer_name) {
            $lines[] = "Plgn  : " . $order->customer_name;
        }
        $lines[] = str_repeat('-', $width);

        foreach ($order->items as $item) {
            $name = $item->name ?: ($item->product_name ?? 'Produk');
            if (!empty($item->variant_name) && !str_contains($name, $item->variant_name)) {
                $name .= ' - ' . $item->variant_name;
            }
            $name = (string) $name;
            $wrappedName = wordwrap($name, $width, "\n", true);
            foreach(explode("\n", $wrappedName) as $nLine) {
                $lines[] = $nLine;
            }

            $qtyStr = $item->quantity . "x " . number_format($item->price, 0, ',', '.');
            $subtotalStr = number_format($item->total ?? $item->subtotal ?? 0, 0, ',', '.');
            $leftPad = "  " . $qtyStr;
            $spaces = max(1, $width - strlen($leftPad) - strlen($subtotalStr));
            $lines[] = $leftPad . str_repeat(' ', $spaces) . $subtotalStr;
        }

        $lines[] = str_repeat('-', $width);
        
        $subLeft = "Subtotal";
        $subRight = number_format($order->subtotal, 0, ',', '.');
        $lines[] = $subLeft . str_repeat(' ', max(1, $width - strlen($subLeft) - strlen($subRight))) . $subRight;
        
        $paymentDetails = is_string($order->payment_details) ? json_decode($order->payment_details, true) : ($order->payment_details ?? []);
        $voucherDiscount = (float) ($paymentDetails['voucher_discount'] ?? 0);
        $manualDiscount = (float) ($paymentDetails['manual_discount'] ?? 0);
        
        if ($voucherDiscount > 0) {
            $voucherName = $order->voucher ? $order->voucher->name : 'Promo';
            $discRight = "-" . number_format($voucherDiscount, 0, ',', '.');
            $maxNameLen = $width - strlen($discRight) - 7;
            if ($maxNameLen > 0 && strlen($voucherName) > $maxNameLen) {
                $voucherName = substr($voucherName, 0, $maxNameLen);
            }
            $discLeft = "VCR (" . $voucherName . ")";
            $lines[] = $discLeft . str_repeat(' ', max(1, $width - strlen($discLeft) - strlen($discRight))) . $discRight;
        }
        
        if ($manualDiscount > 0) {
            $discLeft = "Diskon";
            $discRight = "-" . number_format($manualDiscount, 0, ',', '.');
            $lines[] = $discLeft . str_repeat(' ', max(1, $width - strlen($discLeft) - strlen($discRight))) . $discRight;
        }
        
        if ($order->discount_total > 0 && $voucherDiscount == 0 && $manualDiscount == 0) {
            $discLeft = "Diskon";
            $discRight = "-" . number_format($order->discount_total, 0, ',', '.');
            $lines[] = $discLeft . str_repeat(' ', max(1, $width - strlen($discLeft) - strlen($discRight))) . $discRight;
        }

        $totLeft = "TOTAL";
        $totRight = number_format($order->grand_total, 0, ',', '.');
        $lines[] = $totLeft . str_repeat(' ', max(1, $width - strlen($totLeft) - strlen($totRight))) . $totRight;

        if ($order->cash_paid) {
            $lines[] = str_repeat('-', $width);
            $paidLeft = "Tunai";
            $paidRight = number_format($order->cash_paid, 0, ',', '.');
            $lines[] = $paidLeft . str_repeat(' ', max(1, $width - strlen($paidLeft) - strlen($paidRight))) . $paidRight;

            $changeLeft = "Kembali";
            $changeRight = number_format($order->cash_change ?? 0, 0, ',', '.');
            $lines[] = $changeLeft . str_repeat(' ', max(1, $width - strlen($changeLeft) - strlen($changeRight))) . $changeRight;
        }

        if ($loyaltyEnabled && $order->customer_phone) {
            $phone = \App\Models\PosCustomer::normalizePhone($order->customer_phone);
            $customer = $phone ? \App\Models\PosCustomer::where('phone', $phone)->first() : null;
            if ($customer) {
                $expiryMonths = (int) ($settings['pos_loyalty_stamp_expiry_months'] ?? 6);
                if ($expiryMonths <= 0) $expiryMonths = 6;
                $lastVisit = $customer->last_visit_at ?? $order->created_at ?? now();
                $expiryDate = $lastVisit->copy()->addMonths($expiryMonths)->format('d/m/Y');

                $lines[] = str_repeat('-', $width);
                $lines[] = str_pad("KARTU CAP DIGITAL RAABIHA", $width, ' ', STR_PAD_BOTH);
                $lines[] = "Pelanggan: " . ($customer->name ?: $customer->phone);
                $lines[] = "Total Cap: " . $customer->stamp_count . " dari 9 Cap";

                $c = $customer->stamp_count;
                $row1 = implode(" ", array_map(fn($i) => $i <= $c ? "[X]" : "[ ]", [1,2,3]));
                $row2 = implode(" ", array_map(fn($i) => $i <= $c ? "[X]" : "[ ]", [4,5,6]));
                $row3 = implode(" ", array_map(fn($i) => $i <= $c ? "[X]" : "[ ]", [7,8,9]));

                $lines[] = "Voucher 1 (15k): " . $row1;
                $lines[] = "Voucher 2 (20k): " . $row2;
                $lines[] = "Voucher 3 (25k): " . $row3;
                $lines[] = "Masa Berlaku: s/d " . $expiryDate;
            }
        }

        $lines[] = str_repeat('-', $width);
        foreach (explode("\n", $footer) as $fLine) {
            $lines[] = str_pad(trim($fLine), $width, ' ', STR_PAD_BOTH);
        }

        return implode("\n", $lines);
    }

    /**
     * Generate Base64 Z-Report for a closed Shift
     */
    public function generateZReport(PosSession $session): string
    {
        $this->buffer = self::INIT; // Reset buffer

        $settings = $this->getSettings(['pos_receipt_header', 'pos_auto_cut']);
        $header  = $settings['pos_receipt_header'] ?? "TOKO RAABIHA";
        $autoCut = filter_var($settings['pos_auto_cut'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Fetch session data
        $orders = $session->orders()->get();
        $validOrders = $orders->where('status', '!=', 'cancelled');
        $voidedOrders = $orders->where('status', 'cancelled');

        $totalTrxCount = $validOrders->count();
        $cashSales = $validOrders->filter(fn($o) => in_array(strtolower($o->payment_method), ['cash', 'tunai']))->sum('grand_total');
        $nonCashSales = $validOrders->filter(fn($o) => !in_array(strtolower($o->payment_method), ['cash', 'tunai']))->sum('grand_total');
        $totalSales = $cashSales + $nonCashSales;

        $voidTotal = $voidedOrders->sum('grand_total');

        $pettyIn = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_petty_cash')
            ->where('type', 'in')
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $pettyOut = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_petty_cash')
            ->where('type', 'out')
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $refundOut = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_return_refund')
            ->where('type', 'out')
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $exchangeIn = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_exchange_pay')
            ->where('type', 'in')
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        // Render ESC/POS Header
        $this->add(self::ALIGN_CENTER);
        $this->add(self::BOLD_ON);
        foreach (explode("\n", $header) as $hLine) {
            $this->line(trim($hLine));
        }
        $this->add(self::SIZE_DOUBLE);
        $this->line("Z-REPORT (SHIFT)");
        $this->add(self::SIZE_NORMAL);
        $this->add(self::BOLD_OFF);
        $this->line();

        // Metadata
        $this->add(self::ALIGN_LEFT);
        $this->line("Kasir : " . ($session->cashier->name ?? 'Unknown'));
        $this->line("Buka  : " . $session->opened_at->format('d/m/Y H:i'));
        $this->line("Tutup : " . ($session->closed_at ? $session->closed_at->format('d/m/Y H:i') : 'Belum'));
        $this->divider();

        // 1. Breakdown Penjualan
        $this->justify("Total Transaksi", $totalTrxCount . " nota");
        $this->justify("Penjualan Tunai", number_format($cashSales, 0, ',', '.'));
        $this->justify("Penjualan Non-Tunai", number_format($nonCashSales, 0, ',', '.'));
        $this->add(self::BOLD_ON);
        $this->justify("Total Penjualan", number_format($totalSales, 0, ',', '.'));
        $this->add(self::BOLD_OFF);
        $this->divider();

        // 2. Adjustments, Void & Retur
        if ($pettyIn > 0)    $this->justify("Kas Masuk (Petty)", number_format($pettyIn, 0, ',', '.'));
        if ($pettyOut > 0)   $this->justify("Kas Keluar (Petty)", "-" . number_format($pettyOut, 0, ',', '.'));
        if ($exchangeIn > 0) $this->justify("Tambah Bayar Tukar", number_format($exchangeIn, 0, ',', '.'));
        if ($refundOut > 0)  $this->justify("Refund Retur Kas", "-" . number_format($refundOut, 0, ',', '.'));
        if ($voidTotal > 0)  $this->justify("Void Order (" . $voidedOrders->count() . ")", "-" . number_format($voidTotal, 0, ',', '.'));
        if ($pettyIn > 0 || $pettyOut > 0 || $exchangeIn > 0 || $refundOut > 0 || $voidTotal > 0) {
            $this->divider();
        }

        // 3. Rekap Kas Laci
        $this->justify("Modal Awal", number_format($session->opening_cash, 0, ',', '.'));
        if ($session->expected_ending_cash !== null) {
            $this->justify("Harapan Kas Akhir", number_format($session->expected_ending_cash, 0, ',', '.'));
        }
        if ($session->actual_ending_cash !== null) {
            $this->justify("Kas Aktual", number_format($session->actual_ending_cash, 0, ',', '.'));
        }
        $this->divider();

        $diff = $session->difference_cash ?? 0;
        $diffLabel = $diff < 0 ? "Kurang" : ($diff > 0 ? "Lebih" : "Pas");
        $this->add(self::BOLD_ON);
        $this->justify("Selisih ($diffLabel)", number_format(abs($diff), 0, ',', '.'));
        $this->add(self::BOLD_OFF);

        if (!empty($session->notes)) {
            $this->divider();
            $this->line("Catatan Shift:");
            $this->line(trim($session->notes));
        }

        $this->feed(4);
        if ($autoCut) {
            $this->cut();
        }

        return base64_encode($this->buffer);
    }

    /**
     * Generate Plaintext Z-Report for a closed Shift
     */
    public function generateZReportText(PosSession $session): string
    {
        $settings = $this->getSettings(['pos_receipt_header']);
        $header   = $settings['pos_receipt_header'] ?? "TOKO RAABIHA";

        $orders       = $session->orders()->get();
        $validOrders  = $orders->where('status', '!=', 'cancelled');
        $voidedOrders = $orders->where('status', 'cancelled');

        $totalTrxCount = $validOrders->count();
        $cashSales     = $validOrders->filter(fn($o) => in_array(strtolower($o->payment_method), ['cash', 'tunai']))->sum('grand_total');
        $nonCashSales  = $validOrders->filter(fn($o) => !in_array(strtolower($o->payment_method), ['cash', 'tunai']))->sum('grand_total');
        $totalSales    = $cashSales + $nonCashSales;

        $voidTotal = $voidedOrders->sum('grand_total');

        $pettyIn = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_petty_cash')
            ->where('type', 'in')
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $pettyOut = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_petty_cash')
            ->where('type', 'out')
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $refundOut = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_return_refund')
            ->where('type', 'out')
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $exchangeIn = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_exchange_pay')
            ->where('type', 'in')
            ->where('created_at', '>=', $session->opened_at)
            ->sum('amount');

        $lines = [];
        $lines[] = str_pad(trim($header), 32, ' ', STR_PAD_BOTH);
        $lines[] = str_pad("Z-REPORT (SHIFT)", 32, ' ', STR_PAD_BOTH);
        $lines[] = "";
        $lines[] = "Kasir : " . ($session->cashier->name ?? 'Unknown');
        $lines[] = "Buka  : " . ($session->opened_at ? $session->opened_at->format('d/m/Y H:i') : '-');
        $lines[] = "Tutup : " . ($session->closed_at ? $session->closed_at->format('d/m/Y H:i') : 'Belum');
        $lines[] = str_repeat('-', 32);

        $lines[] = $this->justifyText("Total Transaksi", $totalTrxCount . " nota");
        $lines[] = $this->justifyText("Penjualan Tunai", number_format($cashSales, 0, ',', '.'));
        $lines[] = $this->justifyText("Penjualan Non-Tunai", number_format($nonCashSales, 0, ',', '.'));
        $lines[] = $this->justifyText("Total Penjualan", number_format($totalSales, 0, ',', '.'));
        $lines[] = str_repeat('-', 32);

        if ($pettyIn > 0)    $lines[] = $this->justifyText("Kas Masuk (Petty)", number_format($pettyIn, 0, ',', '.'));
        if ($pettyOut > 0)   $lines[] = $this->justifyText("Kas Keluar (Petty)", "-" . number_format($pettyOut, 0, ',', '.'));
        if ($exchangeIn > 0) $lines[] = $this->justifyText("Tambah Bayar Tukar", number_format($exchangeIn, 0, ',', '.'));
        if ($refundOut > 0)  $lines[] = $this->justifyText("Refund Retur Kas", "-" . number_format($refundOut, 0, ',', '.'));
        if ($voidTotal > 0)  $lines[] = $this->justifyText("Void Order (" . $voidedOrders->count() . ")", "-" . number_format($voidTotal, 0, ',', '.'));

        if ($pettyIn > 0 || $pettyOut > 0 || $exchangeIn > 0 || $refundOut > 0 || $voidTotal > 0) {
            $lines[] = str_repeat('-', 32);
        }

        $lines[] = $this->justifyText("Modal Awal", number_format($session->opening_cash, 0, ',', '.'));
        if ($session->expected_ending_cash !== null) {
            $lines[] = $this->justifyText("Harapan Kas Akhir", number_format($session->expected_ending_cash, 0, ',', '.'));
        }
        if ($session->actual_ending_cash !== null) {
            $lines[] = $this->justifyText("Kas Aktual", number_format($session->actual_ending_cash, 0, ',', '.'));
        }
        $lines[] = str_repeat('-', 32);

        $diff = $session->difference_cash ?? 0;
        $diffLabel = $diff < 0 ? "Kurang" : ($diff > 0 ? "Lebih" : "Pas");
        $lines[] = $this->justifyText("Selisih ($diffLabel)", number_format(abs($diff), 0, ',', '.'));

        if (!empty($session->notes)) {
            $lines[] = str_repeat('-', 32);
            $lines[] = "Catatan Shift:";
            $lines[] = trim($session->notes);
        }

        return implode("\n", $lines);
    }

    /**
     * Generate Base64 receipt for a PosReturn (Return / Exchange)
     */
    public function generateReturnReceipt(\App\Models\PosReturn $posReturn): string
    {
        $this->buffer = self::INIT; // Reset buffer

        $settings = $this->getSettings(['pos_receipt_header', 'pos_receipt_footer', 'pos_auto_cut', 'pos_open_cash_drawer']);
        $header   = $settings['pos_receipt_header'] ?? "TOKO RAABIHA";
        $footer   = $settings['pos_receipt_footer'] ?? "Terima Kasih";
        $autoCut  = filter_var($settings['pos_auto_cut'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($posReturn->net_amount < 0 && filter_var($settings['pos_open_cash_drawer'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $this->drawer();
        }

        $this->add(self::ALIGN_CENTER);
        $this->add(self::BOLD_ON);
        foreach (explode("\n", $header) as $hLine) {
            $this->line(trim($hLine));
        }
        $this->line("*** STRUK RETUR / TUKAR ***");
        $this->add(self::BOLD_OFF);
        $this->line();

        $this->add(self::ALIGN_LEFT);
        $this->line("No Retur: " . $posReturn->return_number);
        $this->line("Nota Asli: " . ($posReturn->order->order_number ?? '-'));
        $this->line("Waktu   : " . $posReturn->created_at->format('d/m/Y H:i'));
        $this->line("Kasir   : " . ($posReturn->cashier->name ?? 'Kasir'));
        if ($posReturn->supervisor) {
            $this->line("Spv     : " . $posReturn->supervisor->name);
        }
        $this->line("Alasan  : " . ($posReturn->reason ?: '-'));
        $this->divider();

        // Returned Items
        $this->add(self::BOLD_ON);
        $this->line("BARANG DIRETUR:");
        $this->add(self::BOLD_OFF);

        foreach ($posReturn->returnedItems as $rItem) {
            $name = $rItem->variant ? ($rItem->product->name . ' - ' . $rItem->variant->name) : ($rItem->product->name ?? 'Produk');
            $this->line($name);
            $qtyStr = $rItem->quantity . "x " . number_format($rItem->price, 0, ',', '.');
            $this->justify("  " . $qtyStr, "-" . number_format($rItem->total, 0, ',', '.'));
        }
        $this->justify("Subtotal Retur", "-" . number_format($posReturn->returned_subtotal, 0, ',', '.'));

        // Exchanged Items (if any)
        if ($posReturn->exchangedItems->count() > 0) {
            $this->divider();
            $this->add(self::BOLD_ON);
            $this->line("BARANG PENGGANTI (TUKAR):");
            $this->add(self::BOLD_OFF);

            foreach ($posReturn->exchangedItems as $eItem) {
                $name = $eItem->variant ? ($eItem->product->name . ' - ' . $eItem->variant->name) : ($eItem->product->name ?? 'Produk');
                $this->line($name);
                $qtyStr = $eItem->quantity . "x " . number_format($eItem->price, 0, ',', '.');
                $this->justify("  " . $qtyStr, number_format($eItem->total, 0, ',', '.'));
            }
            $this->justify("Subtotal Tukar", number_format($posReturn->exchanged_subtotal, 0, ',', '.'));
        }

        $this->divider();
        $this->add(self::BOLD_ON);
        if ($posReturn->net_amount > 0) {
            $this->justify("TAMBAH BAYAR", number_format($posReturn->net_amount, 0, ',', '.'));
        } elseif ($posReturn->net_amount < 0) {
            $this->justify("PENGEMBALIAN UANG", number_format(abs($posReturn->net_amount), 0, ',', '.'));
        } else {
            $this->justify("SELISIH", "Rp 0 (PAS)");
        }
        $this->add(self::BOLD_OFF);

        $this->line();
        $this->add(self::ALIGN_CENTER);
        foreach (explode("\n", $footer) as $fLine) {
            $this->line(trim($fLine));
        }

        $this->feed(4);
        if ($autoCut) {
            $this->cut();
        }

        return base64_encode($this->buffer);
    }

    /**
     * Generate Plain Text Struk Retur / Tukar
     */
    public function generateReturnReceiptText(\App\Models\PosReturn $posReturn): string
    {
        $width = $this->charWidth;
        $settings = $this->getSettings(['pos_receipt_header', 'pos_receipt_footer']);
        $header   = $settings['pos_receipt_header'] ?? "TOKO RAABIHA";
        $footer   = $settings['pos_receipt_footer'] ?? "Terima Kasih";

        $lines = [];
        foreach (explode("\n", $header) as $hLine) {
            $lines[] = str_pad(trim($hLine), $width, ' ', STR_PAD_BOTH);
        }
        $lines[] = str_pad("*** STRUK RETUR / TUKAR ***", $width, ' ', STR_PAD_BOTH);
        $lines[] = str_repeat('-', $width);

        $lines[] = "No Retur: " . $posReturn->return_number;
        $lines[] = "Nota Asli: " . ($posReturn->order->order_number ?? '-');
        $lines[] = "Waktu   : " . $posReturn->created_at->format('d/m/Y H:i');
        $lines[] = "Kasir   : " . ($posReturn->cashier->name ?? 'Kasir');
        if ($posReturn->supervisor) {
            $lines[] = "Spv     : " . $posReturn->supervisor->name;
        }
        $lines[] = "Alasan  : " . ($posReturn->reason ?: '-');
        $lines[] = str_repeat('-', $width);

        $lines[] = "BARANG DIRETUR:";
        foreach ($posReturn->returnedItems as $rItem) {
            $name = $rItem->variant ? ($rItem->product->name . ' - ' . $rItem->variant->name) : ($rItem->product->name ?? 'Produk');
            $lines[] = $name;
            $qtyStr = $rItem->quantity . "x " . number_format($rItem->price, 0, ',', '.');
            $subStr = "-" . number_format($rItem->total, 0, ',', '.');
            $lines[] = "  " . $qtyStr . str_repeat(' ', max(1, $width - 2 - strlen($qtyStr) - strlen($subStr))) . $subStr;
        }
        $subRet = "-" . number_format($posReturn->returned_subtotal, 0, ',', '.');
        $lines[] = "Subtotal Retur" . str_repeat(' ', max(1, $width - strlen("Subtotal Retur") - strlen($subRet))) . $subRet;

        if ($posReturn->exchangedItems->count() > 0) {
            $lines[] = str_repeat('-', $width);
            $lines[] = "BARANG PENGGANTI (TUKAR):";
            foreach ($posReturn->exchangedItems as $eItem) {
                $name = $eItem->variant ? ($eItem->product->name . ' - ' . $eItem->variant->name) : ($eItem->product->name ?? 'Produk');
                $lines[] = $name;
                $qtyStr = $eItem->quantity . "x " . number_format($eItem->price, 0, ',', '.');
                $subStr = number_format($eItem->total, 0, ',', '.');
                $lines[] = "  " . $qtyStr . str_repeat(' ', max(1, $width - 2 - strlen($qtyStr) - strlen($subStr))) . $subStr;
            }
            $subEx = number_format($posReturn->exchanged_subtotal, 0, ',', '.');
            $lines[] = "Subtotal Tukar" . str_repeat(' ', max(1, $width - strlen("Subtotal Tukar") - strlen($subEx))) . $subEx;
        }

        $lines[] = str_repeat('-', $width);
        if ($posReturn->net_amount > 0) {
            $label = "TAMBAH BAYAR";
            $val = number_format($posReturn->net_amount, 0, ',', '.');
        } elseif ($posReturn->net_amount < 0) {
            $label = "PENGEMBALIAN UANG";
            $val = number_format(abs($posReturn->net_amount), 0, ',', '.');
        } else {
            $label = "SELISIH";
            $val = "Rp 0 (PAS)";
        }
        $lines[] = $label . str_repeat(' ', max(1, $width - strlen($label) - strlen($val))) . $val;

        $lines[] = str_repeat('-', $width);
        foreach (explode("\n", $footer) as $fLine) {
            $lines[] = str_pad(trim($fLine), $width, ' ', STR_PAD_BOTH);
        }

        return implode("\n", $lines);
    }
}
