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

    /**
     * Generate Base64 receipt for an Order
     */
    public function generateReceipt(Order $order): string
    {
        // 1. Settings
        $header = SiteSetting::where('key', 'pos_receipt_header')->value('value') ?? "TOKO RAABIHA";
        $footer = SiteSetting::where('key', 'pos_receipt_footer')->value('value') ?? "Terima Kasih";
        $showCashier = filter_var(SiteSetting::where('key', 'pos_show_cashier_name')->value('value') ?? true, FILTER_VALIDATE_BOOLEAN);
        $showDate = filter_var(SiteSetting::where('key', 'pos_show_date')->value('value') ?? true, FILTER_VALIDATE_BOOLEAN);
        $autoCut = filter_var(SiteSetting::where('key', 'pos_auto_cut')->value('value') ?? false, FILTER_VALIDATE_BOOLEAN);
        $openDrawer = filter_var(SiteSetting::where('key', 'pos_open_cash_drawer')->value('value') ?? false, FILTER_VALIDATE_BOOLEAN);
        
        if ($openDrawer) {
            $this->drawer();
        }

        // Header
        $this->add(self::ALIGN_CENTER);
        $this->add(self::BOLD_ON);
        foreach (explode("\n", $header) as $hLine) {
            $this->line(trim($hLine));
        }
        $this->add(self::BOLD_OFF);
        $this->line();

        // Metadata
        $this->add(self::ALIGN_LEFT);
        $this->line("Nota  : " . $order->order_number);
        if ($showDate) {
            $this->line("Waktu : " . $order->created_at->format('d/m/Y H:i'));
        }
        if ($showCashier && $order->cashier) {
            $this->line("Kasir : " . $order->cashier->name);
        }
        if ($order->customer_name) {
            $this->line("Plgn  : " . $order->customer_name);
        }
        $this->divider();

        // Items
        foreach ($order->items as $item) {
            $name = $item->product_name;
            if ($item->variant_name) {
                $name .= ' - ' . $item->variant_name;
            }
            $this->line($name);
            
            $qtyStr = $item->quantity . "x " . number_format($item->price, 0, ',', '.');
            $subtotalStr = number_format($item->subtotal, 0, ',', '.');
            $this->justify("  " . $qtyStr, $subtotalStr);
        }
        $this->divider();

        // Totals
        $this->justify("Subtotal", number_format($order->subtotal, 0, ',', '.'));
        if ($order->discount_amount > 0) {
            $this->justify("Diskon", "-" . number_format($order->discount_amount, 0, ',', '.'));
        }
        
        $this->add(self::BOLD_ON);
        $this->justify("TOTAL", number_format($order->grand_total, 0, ',', '.'));
        $this->add(self::BOLD_OFF);

        if ($order->cash_paid) {
            $this->line();
            $this->justify("Tunai", number_format($order->cash_paid, 0, ',', '.'));
            $this->justify("Kembali", number_format($order->cash_change ?? 0, 0, ',', '.'));
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

        return base64_encode($this->buffer);
    }

    /**
     * Generate Base64 Z-Report for a closed Shift
     */
    public function generateZReport(PosSession $session): string
    {
        $header = SiteSetting::where('key', 'pos_receipt_header')->value('value') ?? "TOKO RAABIHA";
        $autoCut = filter_var(SiteSetting::where('key', 'pos_auto_cut')->value('value') ?? false, FILTER_VALIDATE_BOOLEAN);

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

        $this->add(self::ALIGN_LEFT);
        $this->line("Kasir : " . ($session->cashier->name ?? 'Unknown'));
        $this->line("Buka  : " . $session->opened_at->format('d/m/Y H:i'));
        $this->line("Tutup : " . ($session->closed_at ? $session->closed_at->format('d/m/Y H:i') : 'Belum'));
        $this->divider();

        $this->justify("Modal Awal", number_format($session->opening_cash, 0, ',', '.'));
        // We can add logic to get total cash sales, total non-cash, etc.
        // For simplicity in this base service, we just show expected and actual.
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
        
        $this->feed(4);
        if ($autoCut) {
            $this->cut();
        }

        return base64_encode($this->buffer);
    }
}
