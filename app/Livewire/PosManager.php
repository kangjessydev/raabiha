<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Order;
use App\Models\PosSession;
use App\Models\User;
use App\Services\PosTransactionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

#[Layout('components.layouts.pos')]
class PosManager extends Component
{
    public $search = '';
    public $activeSession = null;

    // State untuk form buka/tutup shift
    public $openingCash = 0;
    public $actualEndingCash = 0;
    public $sessionNotes = '';

    // State Petty Cash
    public $pettyCashType = 'out';
    public $pettyCashAmount = 0;
    public $pettyCashNotes = '';

    // State PIN POS 6-Digit
    public $hasPosPin = false;
    public $posPinInput = '';
    public $posPinConfirm = '';
    public $oldPosPin = '';
    public $newPosPin = '';
    public $newPosPinConfirm = '';
    // State Filter & Search Riwayat Transaksi
    public string $historySearch = '';
    public string $historyPaymentFilter = 'all';
    public string $historyStatusFilter = 'all';
    public string $historyDateFilter = 'shift';

    // State Filter & Search Riwayat Retur
    public string $returnSearch = '';
    public string $returnTypeFilter = 'all';
    public string $returnDateFilter = 'shift';

    // State Filter & Search Pelanggan POS
    public string $customerSearch = '';
    public string $customerDateFilter = 'all';
    public string $customerStampFilter = 'all'; // all, ready_gift, completed_card
    public string $customerSortColumn = 'total_spent'; // name, stamp_count, points, completed_cards, total_spent
    public string $customerSortDirection = 'desc'; // asc, desc
    public string $customerMinSpend = '';
    public string $customerMaxSpend = '';
    public string $customerMinStamps = '';
    public string $customerMinPoints = '';

    public function sortCustomersBy(string $column)
    {
        if ($this->customerSortColumn === $column) {
            $this->customerSortDirection = $this->customerSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->customerSortColumn = $column;
            $this->customerSortDirection = 'desc';
        }
    }

    // State Filter & Search Pesanan Dipesan (Reserved)
    public string $reservedSearch = '';
    public string $reservedFilterStatus = 'all'; // all, upcoming, overdue

    public function resetReservedFilters()
    {
        $this->reservedSearch = '';
        $this->reservedFilterStatus = 'all';
    }

    public function resetHistoryFilters()
    {
        $this->historySearch = '';
        $this->historyPaymentFilter = 'all';
        $this->historyStatusFilter = 'all';
        $this->historyDateFilter = 'shift';
    }

    public function resetReturnFilters()
    {
        $this->returnSearch = '';
        $this->returnTypeFilter = 'all';
        $this->returnDateFilter = 'shift';
    }

    public function resetCustomerFilters()
    {
        $this->customerSearch = '';
        $this->customerDateFilter = 'all';
        $this->customerStampFilter = 'all';
        $this->customerSortColumn = 'total_spent';
        $this->customerSortDirection = 'desc';
        $this->customerMinSpend = '';
        $this->customerMaxSpend = '';
        $this->customerMinStamps = '';
        $this->customerMinPoints = '';
    }

    public function addPettyCash($supervisorId = null, $supervisorPin = null)
    {
        if (!$this->activeSession) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sesi shift kasir belum dibuka!']);
            return;
        }

        $this->validate([
            'pettyCashType'   => 'required|in:in,out',
            'pettyCashAmount' => 'required|numeric|gt:0',
            'pettyCashNotes'  => 'required|string|max:255',
        ], [
            'pettyCashAmount.gt' => 'Nominal petty cash harus lebih besar dari Rp 0.',
            'pettyCashNotes.required' => 'Keterangan pengeluaran/pemasukan kas wajib diisi.',
        ]);

        $limitMode = \App\Models\SiteSetting::where('key', 'pos_petty_cash_limit_mode')->value('value') ?? 'cumulative';
        $maxLimit  = (int) (\App\Models\SiteSetting::where('key', 'pos_petty_cash_max_limit')->value('value') ?? 50000);

        if ($this->pettyCashType === 'out') {
            $exceeds = false;
            $exceedMessage = '';

            // Hitung total akumulasi pengeluaran kasir pada shift ini
            $currentTotalOut = 0;
            if ($this->activeSession) {
                $currentTotalOut = \App\Models\Cashflow::where('source', 'pos')
                    ->where('category', 'pos_petty_cash')
                    ->where('type', 'out')
                    ->where('created_at', '>=', $this->activeSession->opened_at)
                    ->sum('amount');
            }
            $projectedTotal = $currentTotalOut + $this->pettyCashAmount;

            if ($limitMode === 'cumulative' || $limitMode === 'both') {
                if ($projectedTotal > $maxLimit) {
                    $exceeds = true;
                    $exceedMessage = 'Total akumulasi kas keluar shift ini (Rp ' . number_format($currentTotalOut, 0, ',', '.') . ' + Rp ' . number_format($this->pettyCashAmount, 0, ',', '.') . ' = Rp ' . number_format($projectedTotal, 0, ',', '.') . ') melebihi limit shift (Rp ' . number_format($maxLimit, 0, ',', '.') . '). Membutuhkan PIN Supervisor.';
                }
            }

            if (!$exceeds && ($limitMode === 'per_transaction' || $limitMode === 'both')) {
                if ($this->pettyCashAmount > $maxLimit) {
                    $exceeds = true;
                    $exceedMessage = 'Pengeluaran Rp ' . number_format($this->pettyCashAmount, 0, ',', '.') . ' melebihi limit per transaksi (Rp ' . number_format($maxLimit, 0, ',', '.') . '). Membutuhkan PIN Supervisor.';
                }
            }

            if ($exceeds) {
                if (empty($supervisorId) || empty($supervisorPin)) {
                    $this->dispatch('require-supervisor-pin', [
                        'actionType' => 'petty_cash_limit',
                        'title'      => 'Verifikasi PIN Supervisor',
                        'message'    => $exceedMessage,
                    ]);
                    return;
                }

                $supervisor = $this->validateSupervisorPin($supervisorId, $supervisorPin);
                if (!$supervisor) {
                    return;
                }
            }
        }

        \App\Models\Cashflow::create([
            'transaction_date' => now()->toDateString(),
            'type'             => $this->pettyCashType,
            'category'         => 'pos_petty_cash',
            'amount'           => $this->pettyCashAmount,
            'description'      => ($this->pettyCashType === 'out' ? 'Kas Keluar POS: ' : 'Kas Masuk POS: ') . $this->pettyCashNotes,
            'order_id'         => null,
            'source'           => 'pos',
            'is_reversed'      => false,
        ]);

        $autoOpen = \App\Models\SiteSetting::where('key', 'pos_auto_open_drawer_on_petty_cash')->value('value');
        if ($autoOpen === null || $autoOpen === '1' || $autoOpen === true || $autoOpen === 'true') {
            $this->dispatch('trigger-cash-drawer', [
                'reason' => 'Catat Kas (' . ($this->pettyCashType === 'out' ? 'Keluar' : 'Masuk') . ')'
            ]);
        }

        $label = $this->pettyCashType === 'out' ? 'Kas Keluar' : 'Kas Masuk';
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$label} senilai Rp " . number_format($this->pettyCashAmount, 0, ',', '.') . " berhasil dicatat."
        ]);
        $this->dispatch('petty-cash-saved');

        $this->pettyCashAmount = 0;
        $this->pettyCashNotes = '';
        $this->pettyCashType = 'out';
    }

    public function recordPettyCash($supervisorId = null, $supervisorPin = null)
    {
        return $this->addPettyCash($supervisorId, $supervisorPin);
    }

    public function openManualDrawer($supervisorId = null, $supervisorPin = null, $reason = 'Buka Laci Manual (No Sale)')
    {
        if (!$this->activeSession) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sesi shift kasir belum dibuka!']);
            return;
        }

        $requirePinSetting = \App\Models\SiteSetting::where('key', 'pos_require_pin_for_manual_drawer')->value('value');
        $requirePin = ($requirePinSetting === null || $requirePinSetting === '1' || $requirePinSetting === true || $requirePinSetting === 'true');

        $supervisorName = '';
        if ($requirePin) {
            if (empty($supervisorId) || empty($supervisorPin)) {
                $this->dispatch('require-supervisor-pin', [
                    'actionType' => 'manual_drawer',
                    'title'      => 'PIN Supervisor Dibutuhkan',
                    'message'    => 'Membuka laci kasir secara manual memerlukan otorisasi Supervisor.',
                ]);
                return;
            }

            $supervisor = $this->validateSupervisorPin($supervisorId, $supervisorPin);
            if (!$supervisor) {
                return;
            }
            $supervisorName = $supervisor->name;
        }

        // Catat di Riwayat Kas (Audit Trail) agar tercatat jam & supervisor pengizin
        \App\Models\Cashflow::create([
            'transaction_date' => now()->toDateString(),
            'type'             => 'info',
            'category'         => 'pos_drawer_open',
            'amount'           => 0,
            'description'      => 'Buka Laci Manual (No Sale)' . ($supervisorName ? ' — Supervisor: ' . $supervisorName : ''),
            'order_id'         => null,
            'source'           => 'pos',
            'is_reversed'      => false,
        ]);

        // Pemicu sinyal elektrik ke printer thermal
        $this->dispatch('trigger-cash-drawer', [
            'reason' => $reason
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Sinyal perintah buka laci kasir berhasil dikirim.'
        ]);
    }

    #[\Livewire\Attributes\Computed]
    public function pettyCashLimitMode()
    {
        return \App\Models\SiteSetting::where('key', 'pos_petty_cash_limit_mode')->value('value') ?? 'cumulative';
    }

    #[\Livewire\Attributes\Computed]
    public function pettyCashMaxLimit()
    {
        return (int) (\App\Models\SiteSetting::where('key', 'pos_petty_cash_max_limit')->value('value') ?? 50000);
    }

    public function mount()
    {
        $this->loadActiveSession();
        $this->hasPosPin = !empty(Auth::user()->pos_pin);
    }

    public $deviceBlocked = false;

    public function loadActiveSession()
    {
        // Pemicu Safety Net: Otomatis selesaikan sesi gantung yang melampaui batas shift/hari kemarin jika ada
        PosSession::autoCloseStaleSessions();

        $this->activeSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($this->activeSession) {
            $lockedDevice = \Illuminate\Support\Facades\Cache::get('pos_session_device_' . $this->activeSession->id);
            if (!$lockedDevice) {
                \Illuminate\Support\Facades\Cache::forever('pos_session_device_' . $this->activeSession->id, \Illuminate\Support\Facades\Session::getId());
            } elseif ($lockedDevice !== \Illuminate\Support\Facades\Session::getId()) {
                $this->activeSession = null;
                $this->deviceBlocked = true;
                return;
            }
        }
    }

    public $takeoverRequestedByOther = false;
    public $generatedTakeoverCode = null;

    public function requestTakeover()
    {
        $existingSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();
            
        if ($existingSession) {
            \Illuminate\Support\Facades\Cache::put('pos_takeover_request_' . $existingSession->id, true, now()->addMinutes(5));
            \Illuminate\Support\Facades\Cache::put('pos_takeover_initiated_' . $existingSession->id . '_' . \Illuminate\Support\Facades\Session::getId(), true, now()->addMinutes(5));
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Permintaan dikirim. Tunggu persetujuan dari perangkat aktif.']);
            $this->dispatch('takeover-requested');
        }
    }

    public function submitTakeoverCode($code)
    {
        $existingSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($existingSession) {
            $savedCode = \Illuminate\Support\Facades\Cache::get('pos_takeover_code_' . $existingSession->id);
            if ($savedCode && $savedCode == $code) {
                \Illuminate\Support\Facades\Cache::forever('pos_session_device_' . $existingSession->id, \Illuminate\Support\Facades\Session::getId());
                \Illuminate\Support\Facades\Cache::forget('pos_takeover_code_' . $existingSession->id);
                \Illuminate\Support\Facades\Cache::forget('pos_takeover_initiated_' . $existingSession->id . '_' . \Illuminate\Support\Facades\Session::getId());
                $this->deviceBlocked = false;
                $this->loadActiveSession();
                $this->dispatch('notify', ['type' => 'success', 'message' => 'Sesi berhasil diambil alih!']);
                $this->dispatch('takeover-success');
            } else {
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Kode tidak valid atau kadaluarsa.']);
            }
        }
    }

    public function forceTakeoverWithSupervisor($supervisorId, $pin)
    {
        $supervisor = \App\Models\User::find($supervisorId);
        $isSup = $supervisor && (
            $supervisor->is_pos_supervisor ||
            in_array($supervisor->role, ['super_admin', 'owner', 'admin', 'manager', 'supervisor']) ||
            $supervisor->hasAnyRole(['super_admin', 'owner', 'admin', 'manager', 'supervisor'])
        );

        if (!$supervisor || !$isSup || empty($supervisor->pos_pin) || !\Illuminate\Support\Facades\Hash::check($pin, $supervisor->pos_pin)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'PIN Supervisor tidak valid.']);
            return false;
        }

        $existingSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($existingSession) {
            \Illuminate\Support\Facades\Cache::forever('pos_session_device_' . $existingSession->id, \Illuminate\Support\Facades\Session::getId());
            \Illuminate\Support\Facades\Cache::forget('pos_takeover_initiated_' . $existingSession->id . '_' . \Illuminate\Support\Facades\Session::getId());
            
            \App\Models\Cashflow::create([
                'transaction_date' => now()->toDateString(),
                'type'             => 'info',
                'category'         => 'pos_takeover_force',
                'amount'           => 0,
                'description'      => 'Sesi diambil alih paksa dari perangkat lain oleh Supervisor: ' . $supervisor->name,
                'order_id'         => null,
                'source'           => 'pos',
                'is_reversed'      => false,
            ]);

            $this->deviceBlocked = false;
            $this->loadActiveSession();
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Sesi berhasil diambil alih paksa.']);
            $this->dispatch('takeover-success');
            return true;
        }
        return false;
    }

    public function checkTakeoverRequest()
    {
        if ($this->activeSession) {
            if (\Illuminate\Support\Facades\Cache::has('pos_takeover_request_' . $this->activeSession->id)) {
                $this->takeoverRequestedByOther = true;
                $this->dispatch('show-takeover-alert');
            } else {
                $this->takeoverRequestedByOther = false;
            }

            $lockedDevice = \Illuminate\Support\Facades\Cache::get('pos_session_device_' . $this->activeSession->id);
            if ($lockedDevice && $lockedDevice !== \Illuminate\Support\Facades\Session::getId()) {
                $this->activeSession = null;
                $this->deviceBlocked = true;
                $this->takeoverRequestedByOther = false;
                $this->generatedTakeoverCode = null;
            }
        }
    }

    public function approveTakeoverRequest()
    {
        if ($this->activeSession) {
            $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            \Illuminate\Support\Facades\Cache::put('pos_takeover_code_' . $this->activeSession->id, $code, now()->addMinutes(5));
            \Illuminate\Support\Facades\Cache::forget('pos_takeover_request_' . $this->activeSession->id);
            
            $this->generatedTakeoverCode = $code;
            $this->takeoverRequestedByOther = false;
        }
    }

    public function rejectTakeoverRequest()
    {
        if ($this->activeSession) {
            \Illuminate\Support\Facades\Cache::forget('pos_takeover_request_' . $this->activeSession->id);
            $this->takeoverRequestedByOther = false;
        }
    }

    public function checkTakeoverStatus()
    {
        $existingSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($existingSession) {
            // Hanya cek status penolakan jika perangkat ini memang telah secara resmi mengajukan request kode
            $hasInitiated = \Illuminate\Support\Facades\Cache::has('pos_takeover_initiated_' . $existingSession->id . '_' . \Illuminate\Support\Facades\Session::getId());
            if (!$hasInitiated) {
                return; // Jangan lakukan apa-apa jika user sedang di menu awal atau menu Bypass PIN Supervisor
            }

            if (\Illuminate\Support\Facades\Cache::has('pos_takeover_code_' . $existingSession->id)) {
                return; // Kode telah dibuat oleh perangkat A, menunggu input
            }

            if (!\Illuminate\Support\Facades\Cache::has('pos_takeover_request_' . $existingSession->id)) {
                \Illuminate\Support\Facades\Cache::forget('pos_takeover_initiated_' . $existingSession->id . '_' . \Illuminate\Support\Facades\Session::getId());
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Permintaan ditolak oleh perangkat utama.']);
                $this->dispatch('takeover-rejected'); 
            }
        }
    }

    public function isCurrentShiftAllowed(?int $cashierId = null): array
    {
        $cashierId = $cashierId ?? Auth::id();
        $user = \App\Models\User::find($cashierId);

        $restrictionEnabled = \App\Models\SiteSetting::where('key', 'pos_shift_restriction_enabled')->value('value');
        if ($restrictionEnabled === '0' || $restrictionEnabled === 'false') {
            return ['allowed' => true, 'reason' => 'Restriction Disabled'];
        }

        $graceMinutes = (int) (\App\Models\SiteSetting::where('key', 'pos_shift_early_grace_minutes')->value('value') ?? 60);
        $overtimeHours = (int) (\App\Models\SiteSetting::where('key', 'pos_shift_overtime_max_hours')->value('value') ?? 4);

        $rawShifts = \App\Models\SiteSetting::where('key', 'pos_master_shifts')->value('value');
        $masterShifts = is_string($rawShifts) ? (json_decode($rawShifts, true) ?: []) : (is_array($rawShifts) ? $rawShifts : []);

        $rawWhitelist = \App\Models\SiteSetting::where('key', 'pos_whitelist_users')->value('value');
        $whitelistUsers = is_string($rawWhitelist) ? (json_decode($rawWhitelist, true) ?: []) : (is_array($rawWhitelist) ? $rawWhitelist : []);

        // 1. Cek jika pengguna terdaftar di Whitelist
        $whitelistEntry = null;
        foreach ($whitelistUsers as $w) {
            if (isset($w['user_id']) && (string)$w['user_id'] === (string)$cashierId) {
                $whitelistEntry = $w;
                break;
            }
        }

        if ($whitelistEntry) {
            // Jika akun Whitelist dan opsi Master Shift terikat dikosongkan -> Bebas 24 jam
            if (empty($whitelistEntry['shift_name'])) {
                return ['allowed' => true, 'reason' => 'Whitelist Unrestricted Access'];
            }
        }

        $now = \Carbon\Carbon::now();
        $currentTime = $now->format('H:i');

        $userShifts = [];
        $assignedShift = \App\Models\PosSession::findAssignedShiftForUser($cashierId, $masterShifts, $whitelistUsers);

        if ($assignedShift) {
            $userShifts[] = $assignedShift;
        } else {
            // Jika tidak ada spesifik shift terikat, cek shift umum yang terbuka untuk semua kasir
            foreach ($masterShifts as $shift) {
                $assigned = $shift['assigned_cashiers'] ?? [];
                if (!is_array($assigned) || empty($assigned)) {
                    $userShifts[] = $shift;
                }
            }
        }

        if (empty($userShifts)) {
            if ($user && $user->pos_shift_start && $user->pos_shift_end) {
                $userShifts[] = [
                    'shift_name' => 'Shift Khusus Kasir',
                    'start_time' => $user->pos_shift_start,
                    'end_time'   => $user->pos_shift_end,
                ];
            }
        }

        if (empty($userShifts)) {
            if (empty($masterShifts)) {
                return ['allowed' => true, 'reason' => 'No Shift Configured'];
            }
        }

        $shiftsToValidate = !empty($userShifts) ? $userShifts : $masterShifts;

        foreach ($shiftsToValidate as $shift) {
            $startTimeStr = $shift['start_time'] ?? '08:00';
            $endTimeStr = $shift['end_time'] ?? '16:00';

            try {
                $shiftStart = \Carbon\Carbon::createFromFormat('H:i', substr($startTimeStr, 0, 5))->subMinutes($graceMinutes);
                $shiftEnd = \Carbon\Carbon::createFromFormat('H:i', substr($endTimeStr, 0, 5))->addHours($overtimeHours);

                if ($shiftStart->gt($shiftEnd)) {
                    if ($now->gte($shiftStart) || $now->lte($shiftEnd)) {
                        return ['allowed' => true, 'shift' => $shift['shift_name'] ?? 'Master Shift'];
                    }
                } else {
                    if ($now->gte($shiftStart) && $now->lte($shiftEnd)) {
                        return ['allowed' => true, 'shift' => $shift['shift_name'] ?? 'Master Shift'];
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return [
            'allowed' => false,
            'message' => 'Waktu saat ini (' . $currentTime . ') di luar jam shift operasional resmi Anda. Membutuhkan Otorisasi Supervisor.',
        ];
    }

    public function openSession($supervisorId = null, $supervisorPin = null)
    {
        // Single Active Session check per Kasir
        $existingSession = PosSession::where('cashier_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($existingSession) {
            $this->activeSession = $existingSession;
            $this->dispatch('session-opened');
            $this->dispatch('notify', [
                'type'    => 'info',
                'message' => 'Sesi aktif Anda ditemui dan otomatis dimuat kembali.',
            ]);
            return;
        }

        $this->validate([
            'openingCash' => 'required|numeric|min:0',
        ]);

        // Single Store Active Session Check: Prevent multiple open cashier shifts
        $otherActiveSession = PosSession::where('status', 'open')
            ->where('cashier_id', '!=', Auth::id())
            ->with('cashier')
            ->first();

        if ($otherActiveSession) {
            if (!$supervisorId || !$supervisorPin) {
                $otherName = $otherActiveSession->cashier ? $otherActiveSession->cashier->name : 'Kasir Lain';
                $this->dispatch('require-supervisor-pin', [
                    'actionType' => 'takeover_other_shift',
                    'message'    => 'Sesi kasir ' . $otherName . ' masih aktif sejak ' . $otherActiveSession->opened_at->format('H:i') . '. Membutuhkan Otorisasi Supervisor untuk Tutup Paksa & Buka Sesi Baru.',
                ]);
                return;
            }

            $supervisor = $this->validateSupervisorPin($supervisorId, $supervisorPin, 'notify');
            if (!$supervisor) {
                return;
            }

            // Force close previous cashier session
            $otherActiveSession->update([
                'closed_at' => now(),
                'status'    => 'closed',
                'notes'     => 'Sesi ditutup paksa saat pembukaan shift baru oleh ' . Auth::user()->name . ' (Otorisasi Supervisor: ' . $supervisor->name . ')',
            ]);
        }

        // Check Shift Schedule restriction
        $shiftCheck = $this->isCurrentShiftAllowed();
        if (!$shiftCheck['allowed']) {
            if (!$supervisorId || !$supervisorPin) {
                $this->dispatch('require-supervisor-pin', [
                    'actionType' => 'out_of_hours_shift',
                    'message'    => $shiftCheck['message'],
                ]);
                return;
            }

            $supervisor = $this->validateSupervisorPin($supervisorId, $supervisorPin, 'notify');
            if (!$supervisor) {
                return;
            }
        }

        PosSession::create([
            'cashier_id'   => Auth::id(),
            'opened_at'    => now(),
            'opening_cash' => $this->openingCash,
            'status'       => 'open',
        ]);

        $this->loadActiveSession();
        $this->dispatch('session-opened');
        $this->dispatch('notify', [
            'type'    => 'success',
            'message' => 'Sesi kasir baru berhasil dibuka!',
        ]);
    }

    public function closeSession()
    {
        if (!$this->activeSession) return;

        $this->validate([
            'actualEndingCash' => 'required|numeric|min:0',
        ]);

        $pettyCashIn = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_petty_cash')
            ->where('type', 'in')
            ->where('created_at', '>=', $this->activeSession->opened_at)
            ->sum('amount');

        $pettyCashOut = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_petty_cash')
            ->where('type', 'out')
            ->where('created_at', '>=', $this->activeSession->opened_at)
            ->sum('amount');

        $totalCashSales = $this->activeSession->orders()
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                  ->orWhere('payment_method', 'tunai');
            })
            ->sum('grand_total');

        // Kas keluar fisik: Void tunai + Refund retur (dikembalikan dari laci kas)
        $voidAndRefundCashOut = \App\Models\Cashflow::where('source', 'pos')
            ->where('type', 'out')
            ->where('created_at', '>=', $this->activeSession->opened_at)
            ->where(function ($q) {
                $q->where('category', 'pos_return_refund')
                  ->orWhere(function ($q2) {
                      $q2->where('category', 'pos_void')
                         ->whereHas('order', function ($q3) {
                             $q3->whereIn('payment_method', ['cash', 'tunai']);
                         });
                  });
            })
            ->sum('amount');

        // Kas masuk tambahan: Selisih tambah bayar saat penukaran barang
        $exchangeExtraPayIn = \App\Models\Cashflow::where('source', 'pos')
            ->where('category', 'pos_exchange_pay')
            ->where('type', 'in')
            ->where('created_at', '>=', $this->activeSession->opened_at)
            ->sum('amount');

        $expectedEnding = $this->activeSession->opening_cash
            + $totalCashSales
            + $pettyCashIn
            + $exchangeExtraPayIn
            - $pettyCashOut
            - $voidAndRefundCashOut;

        $this->activeSession->update([
            'closed_at'            => now(),
            'expected_ending_cash' => $expectedEnding,
            'actual_ending_cash'   => $this->actualEndingCash,
            'difference_cash'      => $this->actualEndingCash - $expectedEnding,
            'status'               => 'closed',
            'notes'                => $this->sessionNotes,
        ]);

        $escPosService = $this->escPos();
        $zReportBase64 = $escPosService->generateZReport($this->activeSession);
        $zReportText   = $escPosService->generateZReportText($this->activeSession);

        $cashierName = $this->activeSession->cashier->name ?? 'Kasir';

        $this->dispatch('session-closed');
        $this->dispatch('print-z-report', [
            'title'        => 'Laporan Laci Kasir (Z-Report)',
            'order_number' => 'Tutup Shift (' . $cashierName . ')',
            'base64'       => $zReportBase64,
            'text'         => $zReportText,
        ]);

        $this->activeSession    = null;
        $this->openingCash      = 0;
        $this->actualEndingCash = 0;
        $this->sessionNotes     = '';
    }

    public function logoutCashier()
    {
        \Illuminate\Support\Facades\Auth::logout();
        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return $this->redirect(route('pos.login'), navigate: true);
    }

    /**
     * Menerima payload checkout dari Alpine.js
     */
    public function processCheckout($payload)
    {
        if (!$this->activeSession) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sesi kasir belum dibuka!']);
            return;
        }

        $data = is_string($payload) ? json_decode($payload, true) : $payload;
        $data['cashier_id']    = Auth::id();
        $data['pos_session_id'] = $this->activeSession->id;

        $idempotencyKey = $data['payment_details']['idempotency_key'] ?? null;
        $lock = null;
        $lockKey = null;

        if ($idempotencyKey) {
            $lockKey = 'pos_checkout_' . $idempotencyKey;
            
            // Jika transaksi dengan idempotency key ini sudah pernah sukses diproses, return transaksi tersebut!
            if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
                $existingOrderId = \Illuminate\Support\Facades\Cache::get($lockKey);
                $existingOrder = \App\Models\Order::find($existingOrderId);
                if ($existingOrder) {
                    $receiptBase64 = $this->escPos()->generateReceipt($existingOrder);
                    $receiptText   = $this->escPos()->generateReceiptText($existingOrder);

                    $this->dispatch('checkout-success', [
                        'order_id'     => $existingOrder->id,
                        'order_number' => $existingOrder->order_number,
                        'grand_total'  => $existingOrder->grand_total,
                        'cash_change'  => $existingOrder->cash_change,
                        'receipt_text' => $receiptText,
                        'base64'       => $receiptBase64,
                    ]);
                    return;
                }
            }

            // Atomic lock dengan block wait 5 detik jika request pertama masih berjalan di background
            $lock = \Illuminate\Support\Facades\Cache::lock($lockKey . '_mutex', 15);
            try {
                $lock->block(5);
            } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
                // Jika timeout menunggu lock, cek apakah transaksi sudah selesai dibuat
                if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
                    $existingOrderId = \Illuminate\Support\Facades\Cache::get($lockKey);
                    $existingOrder = \App\Models\Order::find($existingOrderId);
                    if ($existingOrder) {
                        $receiptBase64 = $this->escPos()->generateReceipt($existingOrder);
                        $receiptText   = $this->escPos()->generateReceiptText($existingOrder);

                        $this->dispatch('checkout-success', [
                            'order_id'     => $existingOrder->id,
                            'order_number' => $existingOrder->order_number,
                            'grand_total'  => $existingOrder->grand_total,
                            'cash_change'  => $existingOrder->cash_change,
                            'receipt_text' => $receiptText,
                            'base64'       => $receiptBase64,
                        ]);
                        return;
                    }
                }
                $this->dispatch('notify', ['type' => 'error', 'message' => 'Transaksi sedang diproses. Harap tunggu sebentar...']);
                return;
            }
        }

        try {
            $service = new PosTransactionService();
            $order   = $service->completePosTransaction($data);

            if ($idempotencyKey) {
                // Simpan ID Order yang berhasil dibuat dengan key ini
                \Illuminate\Support\Facades\Cache::put($lockKey, $order->id, now()->addDay());
            }

            $receiptBase64 = $this->escPos()->generateReceipt($order);
            $receiptText   = $this->escPos()->generateReceiptText($order);

            $this->dispatch('checkout-success', [
                'order_id'        => $order->id,
                'order_number'    => $order->order_number,
                'grand_total'     => $order->grand_total,
                'cash_change'     => $order->cash_change,
                'receipt_text'    => $receiptText,
                'base64'          => $receiptBase64,
                'allPosCustomers' => $this->allPosCustomers,
            ]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Pembayaran berhasil diproses.']);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        } finally {
            if ($lock) {
                $lock->release();
            }
        }
    }

    /**
     * Mencatat bahwa struk telah dicetak
     */
    public function logPrint($orderId)
    {
        $order = \App\Models\Order::find($orderId);
        if ($order) {
            $order->increment('print_count');
        }
    }

    public function reprintReceipt($orderId)
    {
        $order = Order::with(['items', 'cashier', 'posReturns'])->find($orderId);
        if (!$order) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Transaksi tidak ditemukan.']);
            return;
        }

        try {
            $receiptBase64 = $this->escPos()->generateReceipt($order, isReprint: true);
            $receiptText   = $this->escPos()->generateReceiptText($order, isReprint: true);

            $payload = [
                'title'        => 'Cetak Ulang Struk',
                'order_id'     => $orderId,
                'order_number' => $order->order_number,
                'cash_change'  => $order->cash_change,
                'text'         => $receiptText,
                'base64'       => $receiptBase64,
                'has_returns'  => false,
            ];

            if ($order->posReturns && $order->posReturns->count() > 0) {
                $latestReturn = \App\Models\PosReturn::with([
                    'order', 'cashier', 'supervisor',
                    'returnedItems.product', 'returnedItems.variant',
                    'exchangedItems.product', 'exchangedItems.variant'
                ])->where('order_id', $orderId)->latest()->first();

                if ($latestReturn) {
                    $payload['has_returns']   = true;
                    $payload['return_id']      = $latestReturn->id;
                    $payload['return_number']  = $latestReturn->return_number;
                    $payload['return_text']    = $this->escPos()->generateReturnReceiptText($latestReturn);
                    $payload['return_base64']  = $this->escPos()->generateReturnReceipt($latestReturn);
                }
            }

            $this->dispatch('print-receipt', $payload);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Pratinjau struk #' . $order->order_number . ' ditampilkan.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal menyiapkan struk: ' . $e->getMessage()]);
        }
    }

    public function reprintReturnReceipt($returnId)
    {
        $posReturn = \App\Models\PosReturn::with([
            'order', 'cashier', 'supervisor',
            'returnedItems.product', 'returnedItems.variant',
            'exchangedItems.product', 'exchangedItems.variant'
        ])->find($returnId);

        if (!$posReturn) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Data retur tidak ditemukan.']);
            return;
        }

        try {
            $receiptBase64 = $this->escPos()->generateReturnReceipt($posReturn);
            $receiptText   = $this->escPos()->generateReturnReceiptText($posReturn);

            $this->dispatch('print-receipt', [
                'title'        => 'Struk Retur / Penukaran Barang',
                'order_id'     => null,
                'order_number' => $posReturn->return_number,
                'cash_change'  => 0,
                'text'         => $receiptText,
                'base64'       => $receiptBase64,
            ]);
            $this->dispatch('notify', ['type' => 'success', 'message' => 'Pratinjau struk retur #' . $posReturn->return_number . ' ditampilkan.']);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal mencetak ulang struk retur: ' . $e->getMessage()]);
        }
    }

    /**
     * Tandai pesanan 'reserved' (Dipesan) sebagai 'completed' saat barang diambil pelanggan
     */
    public function completeReservedOrder($orderId)
    {
        try {
            $service = new PosTransactionService();
            $order = $service->completeReservedOrder((int) $orderId);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Pesanan #' . $order->order_number . ' berhasil ditandai Selesai (barang diambil).'
            ]);

            // Cetak / Pratinjau Struk Penyerahan Barang
            $receiptBase64 = $this->escPos()->generateReceipt($order, isReprint: false);
            $receiptText   = $this->escPos()->generateReceiptText($order, isReprint: false);

            $this->dispatch('print-receipt', [
                'title'        => 'Struk Penyerahan Barang (Selesai)',
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'cash_change'  => $order->cash_change,
                'text'         => $receiptText,
                'base64'       => $receiptBase64,
                'has_returns'  => false,
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function lookupPosCustomer($phone)
    {
        $normalized = \App\Models\PosCustomer::normalizePhone($phone);
        if (!$normalized) {
            return null;
        }

        $customer = \App\Models\PosCustomer::where('phone', $normalized)->first();
        if (!$customer) {
            return [
                'exists'                => false,
                'phone'                 => $normalized,
                'stamp_count'           => 0,
                'points_balance'        => 0,
                'completed_cards_count' => 0,
            ];
        }

        return [
            'exists'                => true,
            'name'                  => $customer->name,
            'phone'                 => $customer->phone,
            'stamp_count'           => $customer->stamp_count,
            'points_balance'        => $customer->points_balance,
            'completed_cards_count' => $customer->completed_cards_count,
            'total_visits'          => $customer->total_visits,
        ];
    }

    public function claimPhysicalGiftDirectly($phone, $giftDescription)
    {
        $normalized = \App\Models\PosCustomer::normalizePhone($phone);
        $customer = $normalized ? \App\Models\PosCustomer::where('phone', $normalized)->first() : null;
        if (!$customer) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Pelanggan tidak ditemukan.']);
            return;
        }

        \App\Models\PosStampLog::create([
            'pos_customer_id' => $customer->id,
            'order_id'        => null,
            'type'            => 'redeemed',
            'stamps'          => 0,
            'points'          => 0,
            'description'     => "Penyerahan Hadiah Fisik Langsung: {$giftDescription} oleh Kasir " . (Auth::user()->name ?? 'Kasir') . ".",
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => "Hadiah fisik \"{$giftDescription}\" berhasil diserahkan ke pelanggan!"]);
    }

    public function processReturn($payload)
    {
        if (!$this->activeSession) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Sesi kasir belum dibuka!']);
            return;
        }

        $data = is_string($payload) ? json_decode($payload, true) : $payload;
        $data['cashier_id']     = Auth::id();
        $data['pos_session_id'] = $this->activeSession->id;

        try {
            $service = new PosTransactionService();
            $posReturn = $service->processPosReturn($data);

            $receiptBase64 = $this->escPos()->generateReturnReceipt($posReturn);
            $receiptText   = $this->escPos()->generateReturnReceiptText($posReturn);

            $this->dispatch('return-success', [
                'return_id'     => $posReturn->id,
                'return_number' => $posReturn->return_number,
                'net_amount'    => $posReturn->net_amount,
            ]);
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Retur/Penukaran barang #' . $posReturn->return_number . ' berhasil diproses.'
            ]);
            $this->dispatch('print-receipt', [
                'title'        => 'Struk Retur / Penukaran Barang',
                'order_id'     => null,
                'order_number' => $posReturn->return_number,
                'cash_change'  => 0,
                'text'         => $receiptText,
                'base64'       => $receiptBase64,
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => $e->getMessage()]);
        }
    }

    protected function escPos(): \App\Services\EscPosService
    {
        return app(\App\Services\EscPosService::class);
    }

    public function saveInitialPosPin()
    {
        try {
            $this->validate([
                'posPinInput'   => 'required|digits:6',
                'posPinConfirm' => 'required|same:posPinInput',
            ], [
                'posPinInput.required'   => 'PIN POS wajib diisi.',
                'posPinInput.digits'     => 'PIN POS harus berupa 6 digit angka.',
                'posPinConfirm.required' => 'Konfirmasi PIN wajib diisi.',
                'posPinConfirm.same'     => 'Konfirmasi PIN tidak cocok.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('pin-creation-failed');
            throw $e;
        }

        Auth::user()->update([
            'pos_pin' => \Illuminate\Support\Facades\Hash::make($this->posPinInput),
        ]);

        $this->hasPosPin = true;
        $this->posPinInput = '';
        $this->posPinConfirm = '';

        $this->dispatch('pin-created');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'PIN POS 6-digit berhasil dibuat!']);
    }

    public function unlockScreenWithPin($pin)
    {
        $user = Auth::user();
        if (empty($user->pos_pin)) {
            $this->dispatch('screen-unlock-failed', ['message' => 'Anda belum membuat PIN POS. Silakan buat PIN terlebih dahulu.']);
            return;
        }

        if (\Illuminate\Support\Facades\Hash::check($pin, $user->pos_pin)) {
            $this->dispatch('screen-unlocked');
        } else {
            $this->dispatch('screen-unlock-failed', ['message' => 'PIN POS 6-digit salah!']);
        }
    }

    public function changePosPin()
    {
        $user = Auth::user();
        if (empty($user->pos_pin)) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Anda belum memiliki PIN POS.']);
            return;
        }

        $this->validate([
            'oldPosPin'        => 'required|digits:6',
            'newPosPin'        => 'required|digits:6|different:oldPosPin',
            'newPosPinConfirm' => 'required|same:newPosPin',
        ], [
            'oldPosPin.required'        => 'PIN lama wajib diisi.',
            'oldPosPin.digits'          => 'PIN lama harus 6 digit angka.',
            'newPosPin.required'        => 'PIN baru wajib diisi.',
            'newPosPin.digits'          => 'PIN baru harus 6 digit angka.',
            'newPosPin.different'       => 'PIN baru harus berbeda dengan PIN lama.',
            'newPosPinConfirm.required' => 'Konfirmasi PIN baru wajib diisi.',
            'newPosPinConfirm.same'     => 'Konfirmasi PIN baru tidak cocok.',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($this->oldPosPin, $user->pos_pin)) {
            $this->addError('oldPosPin', 'PIN lama Anda tidak sesuai.');
            return;
        }

        $user->update([
            'pos_pin' => \Illuminate\Support\Facades\Hash::make($this->newPosPin),
        ]);

        $this->oldPosPin = '';
        $this->newPosPin = '';
        $this->newPosPinConfirm = '';

        $this->dispatch('pin-changed');
        $this->dispatch('notify', ['type' => 'success', 'message' => 'PIN POS 6-digit berhasil diperbarui.']);
    }

    private function validateSupervisorPin($supervisorId, $pin, string $failureDispatchEvent = 'notify'): ?User
    {
        // Lockout berbasis supervisor yang dicoba — bukan kasir yang mencoba.
        // Ini mencegah brute force dari banyak terminal kasir sekaligus.
        $supId      = (int) $supervisorId;
        $lockKey    = 'sup_pin_lock_sup_' . $supId;
        $attemptsKey = 'sup_pin_attempts_sup_' . $supId;

        if (\Illuminate\Support\Facades\Cache::has($lockKey)) {
            $seconds = max(1, \Illuminate\Support\Facades\Cache::get($lockKey) - time());
            $msg = 'PIN Supervisor terkunci. Coba lagi dalam ' . $seconds . ' detik.';
            if ($failureDispatchEvent === 'notify') {
                $this->dispatch('notify', ['type' => 'error', 'message' => $msg]);
            } else {
                $this->dispatch($failureDispatchEvent, ['message' => $msg]);
            }
            return null;
        }

        $supervisor = User::find($supervisorId);
        $isSupRole = $supervisor && (
            $supervisor->is_pos_supervisor ||
            in_array($supervisor->role, ['super_admin', 'owner', 'admin', 'manager', 'supervisor']) ||
            $supervisor->hasAnyRole(['super_admin', 'owner', 'admin', 'manager', 'supervisor'])
        );
        $isValidPin = $supervisor && $isSupRole && $supervisor->pos_pin && \Illuminate\Support\Facades\Hash::check($pin, $supervisor->pos_pin);

        if ($isValidPin) {
            \Illuminate\Support\Facades\Cache::forget($attemptsKey);
            return $supervisor;
        }

        $attempts = \Illuminate\Support\Facades\Cache::get($attemptsKey, 0) + 1;
        \Illuminate\Support\Facades\Cache::put($attemptsKey, $attempts, 300);

        if ($attempts >= 3) {
            \Illuminate\Support\Facades\Cache::put($lockKey, time() + 60, 60);
            \Illuminate\Support\Facades\Cache::forget($attemptsKey);
            $supName = $supervisor ? $supervisor->name : 'Supervisor';
            $msg = 'PIN ' . $supName . ' salah 3x. Akses terkunci 60 detik demi keamanan.';
        } else {
            $remaining = 3 - $attempts;
            $supName = $supervisor ? $supervisor->name : '';
            $msg = 'PIN Supervisor ' . $supName . ' salah! Sisa percobaan: ' . $remaining . 'x.';
        }

        if ($failureDispatchEvent === 'notify') {
            $this->dispatch('notify', ['type' => 'error', 'message' => $msg]);
        } else {
            $this->dispatch($failureDispatchEvent, ['message' => $msg]);
        }

        return null;
    }

    public function verifySupervisorPin($supervisorId, $pin, $actionType = '')
    {
        $supervisor = $this->validateSupervisorPin($supervisorId, $pin, 'supervisor-auth-failed');
        if ($supervisor) {
            $this->dispatch('supervisor-authorized', ['actionType' => $actionType, 'supervisorName' => $supervisor->name]);
        }
    }

    public function voidOrder($orderId, $supervisorId, $supervisorPin, $reason = '')
    {
        $order = Order::with(['items', 'voidBy'])->find($orderId);
        if (!$order) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Transaksi tidak ditemukan.']);
            return;
        }

        if ($order->status === 'cancelled') {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Transaksi ini sudah pernah dibatalkan (Void).']);
            return;
        }

        $supervisor = $this->validateSupervisorPin($supervisorId, $supervisorPin, 'notify');
        if (!$supervisor) {
            return;
        }

        try {
            DB::transaction(function () use ($order, $supervisor, $reason) {
                // 1. Mark order as cancelled/void
                $order->update([
                    'status'         => 'cancelled',
                    'payment_status' => 'failed',
                    'void_by_id'     => $supervisor->id,
                    'void_reason'    => $reason ?: 'Void Kasir POS',
                    'void_at'        => now(),
                ]);

                // 2. Restock product items
                foreach ($order->items as $item) {
                    $stockBefore = 0;
                    $stockAfter  = 0;

                    if ($item->product_variant_id) {
                        $variant = \App\Models\ProductVariant::find($item->product_variant_id);
                        if ($variant) {
                            $stockBefore = $variant->stock;
                            $variant->increment('stock', $item->quantity);
                            $stockAfter = $stockBefore + $item->quantity;
                        }
                    } else {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $stockBefore = $product->stock;
                            $product->increment('stock', $item->quantity);
                            $stockAfter = $stockBefore + $item->quantity;
                        }
                    }

                    // Stock log (hanya jika ada model yang ditemukan)
                    if ($stockAfter > 0 || $stockBefore >= 0) {
                        \App\Models\StockLog::create([
                            'product_id'         => $item->product_id,
                            'product_variant_id' => $item->product_variant_id,
                            'user_id'            => Auth::id(),
                            'type'               => 'in',
                            'quantity_before'    => $stockBefore,
                            'quantity_change'    => $item->quantity,
                            'quantity_after'     => $stockAfter,
                            'reason'             => 'pos_void',
                            'notes'              => 'Restock Void Nota POS #' . $order->order_number . ' (Disetujui Supervisor: ' . $supervisor->name . ')',
                        ]);
                    }
                }

                // 3. Record Cashflow Reversal — balik SEMUA entri 'pos_sale' untuk order ini
                // (Penting untuk Split Payment yang memiliki lebih dari 1 baris cashflow per transaksi)
                $existingCashflows = \App\Models\Cashflow::where('order_id', $order->id)
                    ->where('type', 'in')
                    ->where('source', 'pos')
                    ->where('category', 'pos_sale')
                    ->where('is_reversed', false)
                    ->get();

                foreach ($existingCashflows as $cf) {
                    $cf->update(['is_reversed' => true]);
                    \App\Models\Cashflow::create([
                        'transaction_date' => now()->toDateString(),
                        'type'             => 'out',
                        'category'         => 'pos_void',
                        'amount'           => $cf->amount,
                        'description'      => 'Void Transaksi POS #' . $order->order_number . ' (' . strtoupper($order->payment_method) . ') (Disetujui Supervisor: ' . $supervisor->name . ') - ' . ($reason ?: 'Batal transaksi'),
                        'order_id'         => $order->id,
                        'source'           => 'pos',
                        'is_reversed'      => true,
                    ]);
                }

                // Fallback: jika tidak ada cashflow 'in' yang ditemukan (misal sudah pernah di-void), catat manual
                if ($existingCashflows->isEmpty()) {
                    \App\Models\Cashflow::create([
                        'transaction_date' => now()->toDateString(),
                        'type'             => 'out',
                        'category'         => 'pos_void',
                        'amount'           => $order->grand_total,
                        'description'      => 'Void Transaksi POS #' . $order->order_number . ' (' . strtoupper($order->payment_method) . ') (Disetujui Supervisor: ' . $supervisor->name . ') - ' . ($reason ?: 'Batal transaksi'),
                        'order_id'         => $order->id,
                        'source'           => 'pos',
                        'is_reversed'      => true,
                    ]);
                }

                // 4. Rollback Loyalti Stempel Pelanggan jika ada
                if ($order->customer_phone) {
                    $phone = \App\Models\PosCustomer::normalizePhone($order->customer_phone);
                    $customer = $phone ? \App\Models\PosCustomer::where('phone', $phone)->first() : null;
                    if ($customer) {
                        $earnedLogs = \App\Models\PosStampLog::where('order_id', $order->id)->where('type', 'earned')->get();
                        foreach ($earnedLogs as $eLog) {
                            $stampsToDeduct = $eLog->stamps;
                            $pointsToDeduct = $eLog->points;
                            $newStamps = max(0, $customer->stamp_count - $stampsToDeduct);
                            $newPoints = max(0, $customer->points_balance - $pointsToDeduct);
                            $customer->update([
                                'stamp_count'    => $newStamps,
                                'points_balance' => $newPoints,
                            ]);

                            \App\Models\PosStampLog::create([
                                'pos_customer_id' => $customer->id,
                                'order_id'        => $order->id,
                                'type'            => 'adjusted',
                                'stamps'          => -$stampsToDeduct,
                                'points'          => -$pointsToDeduct,
                                'description'     => "Penyesuaian ditarik kembali karena Void Nota POS #{$order->order_number}.",
                            ]);
                        }
                    }
                }
            });

            $this->dispatch('order-voided');
            $this->dispatch('notify', [
                'type'    => 'success',
                'message' => 'Transaksi #' . $order->order_number . ' berhasil dibatalkan (Disetujui Supervisor: ' . $supervisor->name . ').'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', ['type' => 'error', 'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage()]);
        }
    }

    /**
     * Simpan Produk Kustom Baru secara instan ke Katalog POS
     */
    public function saveCustomProduct($data)
    {
        $name = trim($data['name'] ?? '');
        $purchasePrice = (float) ($data['purchase_price'] ?? 0);
        $normalPrice = (float) ($data['normal_price'] ?? 0);
        $price = (float) ($data['price'] ?? 0);
        $qty = (int) ($data['quantity'] ?? 1);

        if (empty($name)) {
            $this->dispatch('pos-toast', message: 'Nama produk kustom wajib diisi.', type: 'error');
            return null;
        }

        if ($price <= 0) {
            $this->dispatch('pos-toast', message: 'Harga jual harus lebih dari 0.', type: 'error');
            return null;
        }

        $newProduct = Product::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . time(),
            'price' => $normalPrice > 0 ? $normalPrice : $price,
            'pos_price' => $price,
            'purchase_price' => $purchasePrice,
            'stock' => max(1, $qty),
            'sold_count' => 0,
            'is_active' => true,
            'is_custom' => true,
            'channel_visibility' => 'pos_only',
        ]);

        // Catat Log Stok Masuk Awal ke Manajemen Stok
        \App\Models\StockLog::create([
            'product_id'         => $newProduct->id,
            'product_variant_id' => null,
            'user_id'            => Auth::id(),
            'type'               => 'in',
            'quantity_before'    => 0,
            'quantity_change'    => max(1, $qty),
            'quantity_after'     => max(1, $qty),
            'reason'             => 'initial_stock',
            'notes'              => 'Stok awal pendaftaran produk kustom/impor via POS',
        ]);

        $this->dispatch('pos-toast', message: "Produk '{$name}' berhasil disimpan ke Katalog POS (Stok: " . max(1, $qty) . " pcs)!", type: 'success');

        return [
            'id' => $newProduct->id,
            'name' => $newProduct->name,
            'price' => (float) $newProduct->pos_price,
            'purchase_price' => (float) $newProduct->purchase_price,
            'stock' => (int) $newProduct->stock,
        ];
    }

    public function render()
    {
        $products          = [];
        $sessionOrders     = collect();
        $sessionCustomers  = collect();
        $sessionPettyCash  = collect();
        $sessionReturns    = collect();
        $allProductsJson   = [];
        $sessionStats = [
            'total_trx'          => 0,
            'cash_sales'         => 0,
            'non_cash_sales'     => 0,
            'non_cash_breakdown' => [],
            'total_sales'        => 0,
            'opening_cash'       => 0,
            'petty_cash_in'      => 0,
            'petty_cash_out'     => 0,
            'void_refund_out'    => 0,
            'exchange_in'        => 0,
            'expected_cash'      => 0,
            'opened_at'          => '-',
        ];

        if ($this->activeSession) {
            /* ---------- Product list ---------- */
            $query = Product::with(['variants.attributeOptions.attribute', 'variants.media'])
                ->where('is_active', true)
                ->whereIn('channel_visibility', ['pos_only', 'both']);

            if (!empty($this->search)) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku',  'like', '%' . $this->search . '%');
                });

                $products = $query->limit(48)->get()->map(function ($p) {
                    $p->computed_stock = $p->has_variants ? $p->variants->sum('stock') : $p->stock;
                    return $p;
                });
            } else {
                $top3 = (clone $query)
                    ->selectRaw('*, (CASE WHEN has_variants = 1 THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id) ELSE stock END) as computed_stock')
                    ->where('sold_count', '>=', 5)
                    ->whereRaw('(CASE WHEN has_variants = 1 THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id) ELSE stock END) > 0')
                    ->orderBy('sold_count', 'desc')
                    ->limit(3)
                    ->get()
                    ->map(function ($p) {
                        $p->is_best_seller = true;
                        return $p;
                    });

                $top3Ids = $top3->pluck('id')->toArray();

                $remaining = (clone $query)
                    ->whereNotIn('id', $top3Ids)
                    ->selectRaw('*, (CASE WHEN has_variants = 1 THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_variants.product_id = products.id) ELSE stock END) as computed_stock')
                    ->orderByRaw('computed_stock > 0 DESC')
                    ->orderBy('computed_stock', 'desc')
                    ->limit(45)
                    ->get();

                $products = $top3->concat($remaining);
            }

            /* ---------- Riwayat Transaksi (dinamis filter & search) ---------- */
            $ordersQuery = Order::with(['items', 'cashier', 'voidBy', 'posReturns'])
                ->where('source', 'pos');

            if ($this->historyDateFilter === 'shift' && $this->activeSession) {
                $ordersQuery->where('pos_session_id', $this->activeSession->id);
            } elseif ($this->historyDateFilter === 'today') {
                $ordersQuery->whereDate('created_at', now()->toDateString());
            } elseif ($this->historyDateFilter === 'yesterday') {
                $ordersQuery->whereDate('created_at', now()->subDay()->toDateString());
            } elseif ($this->historyDateFilter === '7days') {
                $ordersQuery->where('created_at', '>=', now()->subDays(7)->startOfDay());
            } elseif ($this->historyDateFilter === '30days') {
                $ordersQuery->where('created_at', '>=', now()->subDays(30)->startOfDay());
            }

            if (!empty(trim($this->historySearch))) {
                $term = '%' . trim($this->historySearch) . '%';
                $ordersQuery->where(function ($q) use ($term) {
                    $q->where('order_number', 'like', $term)
                      ->orWhere('customer_name', 'like', $term)
                      ->orWhere('customer_phone', 'like', $term);
                });
            }

            if ($this->historyPaymentFilter === 'cash') {
                $ordersQuery->whereIn('payment_method', ['cash', 'tunai']);
            } elseif ($this->historyPaymentFilter === 'non_cash') {
                $ordersQuery->whereNotIn('payment_method', ['cash', 'tunai']);
            } elseif ($this->historyPaymentFilter !== 'all') {
                $filterVal = strtolower($this->historyPaymentFilter);
                $ordersQuery->where(function($q) use ($filterVal) {
                    $q->whereRaw('LOWER(payment_method) = ?', [$filterVal]);
                });
            }

            if ($this->historyStatusFilter !== 'all') {
                $ordersQuery->where('status', $this->historyStatusFilter);
            }

            $sessionOrders = $ordersQuery->latest()->limit(100)->get();

            /* ---------- Pelanggan POS (dinamis filter & search) ---------- */
            if ($this->customerDateFilter === 'all') {
                $allPosCustomers = \App\Models\PosCustomer::all();
                
                $orderAggregates = Order::where('status', '!=', 'cancelled')
                    ->whereNotNull('customer_name')
                    ->where('customer_name', '!=', '')
                    ->select(
                        'customer_name',
                        'customer_phone',
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('SUM(grand_total) as total_spent'),
                        DB::raw('MAX(created_at) as last_visit')
                    )
                    ->groupBy('customer_name', 'customer_phone')
                    ->get();

                $orderMapByPhone = $orderAggregates->keyBy(fn($o) => $o->customer_phone);
                $orderMapByName  = $orderAggregates->keyBy(fn($o) => strtolower($o->customer_name));

                $customerList = collect();

                foreach ($allPosCustomers as $pCust) {
                    $agg = ($pCust->phone ? $orderMapByPhone->get($pCust->phone) : null) ?? $orderMapByName->get(strtolower($pCust->name));
                    
                    $customerList->push((object)[
                        'customer_name'  => $pCust->name,
                        'customer_phone' => $pCust->phone,
                        'total_orders'   => $agg ? (int)$agg->total_orders : ($pCust->total_visits ?: 0),
                        'total_spent'    => $agg ? (float)$agg->total_spent : (float)($pCust->total_spent ?: 0),
                        'last_visit'     => $agg ? $agg->last_visit : $pCust->last_visit_at,
                        'stamp_count'    => (int)$pCust->stamp_count,
                        'active_stamps'  => (int)($pCust->stamp_count % 12),
                        'completed_cards_count' => (int)($pCust->completed_cards_count ?? floor($pCust->stamp_count / 12)),
                        'loyalty_points' => (int)$pCust->points_balance,
                        'customer_email' => $pCust->email,
                        'customer_address' => $pCust->address,
                    ]);
                }

                $existingPhones = $allPosCustomers->pluck('phone')->filter()->toArray();
                $existingNames  = $allPosCustomers->pluck('name')->map(fn($n) => strtolower($n))->toArray();

                foreach ($orderAggregates as $oAgg) {
                    $phoneKey = $oAgg->customer_phone;
                    $nameKey  = strtolower($oAgg->customer_name);
                    if (($phoneKey && !in_array($phoneKey, $existingPhones)) || (!in_array($nameKey, $existingNames))) {
                        $customerList->push((object)[
                            'customer_name'  => $oAgg->customer_name,
                            'customer_phone' => $oAgg->customer_phone,
                            'total_orders'   => (int)$oAgg->total_orders,
                            'total_spent'    => (float)$oAgg->total_spent,
                            'last_visit'     => $oAgg->last_visit,
                            'stamp_count'    => 0,
                            'active_stamps'  => 0,
                            'completed_cards_count' => 0,
                            'loyalty_points' => 0,
                            'customer_email' => null,
                            'customer_address' => null,
                        ]);
                    }
                }

                $sessionCustomers = $customerList;
            } else {
                $custQuery = Order::where('status', '!=', 'cancelled')
                    ->whereNotNull('customer_name')
                    ->where('customer_name', '!=', '');

                if ($this->customerDateFilter === 'shift' && $this->activeSession) {
                    $custQuery->where('pos_session_id', $this->activeSession->id);
                } elseif ($this->customerDateFilter === 'today') {
                    $custQuery->whereDate('created_at', now()->toDateString());
                } elseif ($this->customerDateFilter === 'yesterday') {
                    $custQuery->whereDate('created_at', now()->subDay()->toDateString());
                } elseif ($this->customerDateFilter === '7days') {
                    $custQuery->where('created_at', '>=', now()->subDays(7)->startOfDay());
                } elseif ($this->customerDateFilter === '30days') {
                    $custQuery->where('created_at', '>=', now()->subDays(30)->startOfDay());
                }

                $sessionCustomers = $custQuery->select(
                        'customer_name',
                        'customer_phone',
                        DB::raw('COUNT(*) as total_orders'),
                        DB::raw('SUM(grand_total) as total_spent'),
                        DB::raw('MAX(created_at) as last_visit')
                    )
                    ->groupBy('customer_name', 'customer_phone')
                    ->orderByDesc('total_spent')
                    ->get();
            }

            // Filter Stempel (ready_gift / completed_card)
            if ($this->customerStampFilter === 'ready_gift') {
                $sessionCustomers = $sessionCustomers->filter(fn($c) => ($c->stamp_count ?? 0) >= 3 || ($c->completed_cards_count ?? 0) > 0);
            } elseif ($this->customerStampFilter === 'completed_card') {
                $sessionCustomers = $sessionCustomers->filter(fn($c) => ($c->completed_cards_count ?? 0) > 0);
            }

            $posCustomerMap = \App\Models\PosCustomer::all()->keyBy(fn($c) => $c->phone ?: strtolower($c->name));

            $sessionCustomers->transform(function ($c) use ($posCustomerMap) {
                $key = $c->customer_phone ?: strtolower($c->customer_name);
                $posCust = $posCustomerMap->get($key) ?? \App\Models\PosCustomer::where('name', $c->customer_name)->first();
                $c->stamp_count = $posCust ? (int)$posCust->stamp_count : 0;
                $c->active_stamps = $c->stamp_count % 12;
                $c->completed_cards_count = $posCust ? (int)($posCust->completed_cards_count ?? floor($c->stamp_count / 12)) : (int)floor($c->stamp_count / 12);
                $c->loyalty_points = $posCust ? (int)($posCust->points_balance ?? 0) : 0;
                $c->customer_email = $posCust ? $posCust->email : null;
                $c->customer_address = $posCust ? $posCust->address : null;

                // Load all transaction orders for this customer for per-row log
                $customerOrders = Order::with(['items', 'user'])
                    ->where('status', '!=', 'cancelled')
                    ->where(function($q) use ($c) {
                        if ($c->customer_phone) {
                            $q->where('customer_phone', $c->customer_phone);
                        } else {
                            $q->where('customer_name', $c->customer_name);
                        }
                    })
                    ->orderByDesc('created_at')
                    ->get();

                $c->customer_orders = $customerOrders->map(function($o) use ($c) {
                    return [
                        'id' => $o->id,
                        'order_number' => $o->order_number,
                        'created_at' => $o->created_at ? $o->created_at->format('d M Y, H:i') : '-',
                        'customer_name' => $o->customer_name ?: ($c->customer_name ?? 'Pelanggan'),
                        'customer_phone' => $o->customer_phone ?: ($c->customer_phone ?? '-'),
                        'payment_method' => $o->payment_method_label ?? strtoupper($o->payment_method ?? 'TUNAI'),
                        'subtotal' => (float)($o->subtotal ?? $o->grand_total),
                        'discount_total' => (float)($o->discount_amount ?? 0),
                        'grand_total' => (float)$o->grand_total,
                        'cash_paid' => (float)($o->cash_paid ?? 0),
                        'cash_change' => (float)($o->cash_change ?? 0),
                        'payment_status' => $o->payment_status,
                        'cashier_name' => $o->user?->name ?? 'Kasir System',
                        'items' => $o->items->map(fn($item) => [
                            'name' => $item->product_name ?? 'Produk',
                            'variant' => $item->variant_name ?? '',
                            'price' => (float)$item->price,
                            'qty' => (int)$item->quantity,
                            'subtotal' => (float)$item->total,
                            'total' => (float)$item->total,
                        ])
                    ];
                })->values()->all();

                return $c;
            });

            // Apply range filters
            if ($this->customerMinSpend !== '') {
                $sessionCustomers = $sessionCustomers->filter(fn($c) => ($c->total_spent ?? 0) >= (float)$this->customerMinSpend);
            }
            if ($this->customerMaxSpend !== '') {
                $sessionCustomers = $sessionCustomers->filter(fn($c) => ($c->total_spent ?? 0) <= (float)$this->customerMaxSpend);
            }
            if ($this->customerMinStamps !== '') {
                $sessionCustomers = $sessionCustomers->filter(fn($c) => ($c->stamp_count ?? 0) >= (int)$this->customerMinStamps);
            }
            if ($this->customerMinPoints !== '') {
                $sessionCustomers = $sessionCustomers->filter(fn($c) => ($c->loyalty_points ?? 0) >= (int)$this->customerMinPoints);
            }

            // Apply dynamic sorting based on $this->customerSortColumn & $this->customerSortDirection
            $col = $this->customerSortColumn;
            $isAsc = $this->customerSortDirection === 'asc';

            $sessionCustomers = match($col) {
                'name'            => $isAsc ? $sessionCustomers->sortBy('customer_name') : $sessionCustomers->sortByDesc('customer_name'),
                'stamp_count'     => $isAsc ? $sessionCustomers->sortBy('stamp_count') : $sessionCustomers->sortByDesc('stamp_count'),
                'points'          => $isAsc ? $sessionCustomers->sortBy('loyalty_points') : $sessionCustomers->sortByDesc('loyalty_points'),
                'completed_cards' => $isAsc ? $sessionCustomers->sortBy('completed_cards_count') : $sessionCustomers->sortByDesc('completed_cards_count'),
                default           => $isAsc ? $sessionCustomers->sortBy('total_spent') : $sessionCustomers->sortByDesc('total_spent'),
            };

            $sessionCustomers = $sessionCustomers->values();

            /* ---------- Rekap Kas ---------- */
            $validOrders  = $sessionOrders->where('status', '!=', 'cancelled');
            $cashSales    = $validOrders->filter(fn($o) => in_array(strtolower($o->payment_method), ['cash', 'tunai']))->sum('grand_total');
            $nonCashSales = $validOrders->filter(fn($o) => !in_array(strtolower($o->payment_method), ['cash', 'tunai']))->sum('grand_total');

            // Breakdown dinamis per metode non-tunai yang ada di shift ini
            $nonCashOrders = $validOrders->filter(fn($o) => !in_array(strtolower($o->payment_method), ['cash', 'tunai']));
            $nonCashBreakdown = [];
            foreach ($nonCashOrders->groupBy(fn($o) => strtolower(trim($o->payment_method))) as $method => $orders) {
                $methodName = match($method) {
                    'qris'     => 'QRIS',
                    'transfer' => 'Transfer Bank',
                    'edc'      => 'Kartu Debit / EDC',
                    default    => strtoupper($method)
                };
                $nonCashBreakdown[] = [
                    'method'      => $method,
                    'short_label' => $methodName,
                    'amount'      => $orders->sum('grand_total'),
                ];
            }

            $sessionPettyCash = \App\Models\Cashflow::where('source', 'pos')
                ->whereIn('category', ['pos_petty_cash', 'pos_drawer_open'])
                ->where('created_at', '>=', $this->activeSession->opened_at)
                ->latest()
                ->get();

            $pettyCashIn  = $sessionPettyCash->where('type', 'in')->sum('amount');
            $pettyCashOut = $sessionPettyCash->where('type', 'out')->sum('amount');

            $voidRefundOut = \App\Models\Cashflow::where('source', 'pos')
                ->where('type', 'out')
                ->where('created_at', '>=', $this->activeSession->opened_at)
                ->where(function ($q) {
                    $q->where('category', 'pos_return_refund')
                      ->orWhere(function ($q2) {
                          $q2->where('category', 'pos_void')
                             ->whereHas('order', function ($q3) {
                                 $q3->whereIn('payment_method', ['cash', 'tunai']);
                             });
                      });
                })
                ->sum('amount');

            $exchangeIn = \App\Models\Cashflow::where('source', 'pos')
                ->where('category', 'pos_exchange_pay')
                ->where('type', 'in')
                ->where('created_at', '>=', $this->activeSession->opened_at)
                ->sum('amount');

            $sessionStats = [
                'total_trx'          => $validOrders->count(),
                'cash_sales'         => $cashSales,
                'non_cash_sales'     => $nonCashSales,
                'non_cash_breakdown' => $nonCashBreakdown,
                'total_sales'        => $cashSales + $nonCashSales,
                'opening_cash'       => $this->activeSession->opening_cash,
                'petty_cash_in'      => $pettyCashIn,
                'petty_cash_out'     => $pettyCashOut,
                'void_refund_out'    => $voidRefundOut,
                'exchange_in'        => $exchangeIn,
                'expected_cash'      => $this->activeSession->opening_cash
                    + $cashSales
                    + $pettyCashIn
                    + $exchangeIn
                    - $pettyCashOut
                    - $voidRefundOut,
                'opened_at'          => $this->activeSession->opened_at->format('d M Y, H:i'),
            ];

            /* ---------- Riwayat Retur (dinamis filter & search) ---------- */
            $returnsQuery = \App\Models\PosReturn::with([
                'order',
                'cashier',
                'supervisor',
                'returnedItems.product',
                'returnedItems.variant',
                'exchangedItems.product',
                'exchangedItems.variant'
            ]);

            if ($this->returnDateFilter === 'shift' && $this->activeSession) {
                $returnsQuery->where('pos_session_id', $this->activeSession->id);
            } elseif ($this->returnDateFilter === 'today') {
                $returnsQuery->whereDate('created_at', now()->toDateString());
            } elseif ($this->returnDateFilter === 'yesterday') {
                $returnsQuery->whereDate('created_at', now()->subDay()->toDateString());
            } elseif ($this->returnDateFilter === '7days') {
                $returnsQuery->where('created_at', '>=', now()->subDays(7)->startOfDay());
            } elseif ($this->returnDateFilter === '30days') {
                $returnsQuery->where('created_at', '>=', now()->subDays(30)->startOfDay());
            }

            if (!empty(trim($this->returnSearch))) {
                $term = '%' . trim($this->returnSearch) . '%';
                $returnsQuery->where(function ($q) use ($term) {
                    $q->where('return_number', 'like', $term)
                      ->orWhereHas('order', fn($o) => $o->where('order_number', 'like', $term))
                      ->orWhereHas('cashier', fn($c) => $c->where('name', 'like', $term));
                });
            }

            if ($this->returnTypeFilter !== 'all') {
                $returnsQuery->where('type', $this->returnTypeFilter);
            }


            $sessionReturns = $returnsQuery->latest()->get();
        }

        $activeEventPromos = \App\Models\PosEventPromotion::active()->get();

        $allProductsJson = collect($products)->map(function($p) use ($activeEventPromos) {
            $basePrice = (float)($p->pos_price ?: $p->price);
            $activePromo = $activeEventPromos->first(fn($ep) => $ep->isProductEligible($p->id, $p->category_id));
            $finalPrice = $basePrice;

            if ($activePromo) {
                if ($activePromo->discount_type === 'percent') {
                    $finalPrice = max(0, $basePrice * (1 - (((float)$activePromo->discount_amount) / 100)));
                } else {
                    $finalPrice = max(0, $basePrice - ((float)$activePromo->discount_amount));
                }
            } elseif ($p->pos_discount_price) {
                $finalPrice = (float)$p->pos_discount_price;
            }

            return [
                'id'           => $p->id,
                'name'         => $p->name,
                'price'        => $finalPrice,
                'original_price' => $basePrice,
                'stock'        => (int)($p->computed_stock ?? $p->stock),
                'has_variants' => (bool)$p->has_variants,
                'variants'     => $p->has_variants ? ($p->relationLoaded('variants') ? $p->variants->map(function($v) use ($p, $activePromo) {
                    $vBasePrice = (float)($v->pos_price ?: ($v->price ?: $p->price));
                    $vFinalPrice = $vBasePrice;

                    if ($activePromo) {
                        if ($activePromo->discount_type === 'percent') {
                            $vFinalPrice = max(0, $vBasePrice * (1 - (((float)$activePromo->discount_amount) / 100)));
                        } else {
                            $vFinalPrice = max(0, $vBasePrice - ((float)$activePromo->discount_amount));
                        }
                    } elseif ($p->pos_discount_price) {
                        $vFinalPrice = (float)$p->pos_discount_price;
                    }

                    return [
                        'id'         => $v->id,
                        'name'       => $v->name,
                        'price'      => $vFinalPrice,
                        'original_price' => $vBasePrice,
                        'stock'      => (int)$v->stock,
                        'attributes' => $v->attributeOptions ? $v->attributeOptions->map(fn($opt) => [
                            'attr_id'   => $opt->attribute_id,
                            'attr_name' => $opt->attribute->name ?? '',
                            'attr_slug' => $opt->attribute->slug ?? '',
                            'value'     => $opt->value,
                        ])->values()->all() : [],
                    ];
                })->values()->all() : []) : [],
            ];
        })->values()->all();

        $reservedQuery = Order::with(['items', 'cashier'])
            ->where('source', 'pos')
            ->where('status', 'reserved');

        // Stats counts
        $allReservedBase = (clone $reservedQuery)->get();
        $totalReservedCount = $allReservedBase->count();
        $todayStr = now()->toDateString();
        
        $todayCount = $allReservedBase->filter(fn($o) => $o->pickup_date && $o->pickup_date->toDateString() === $todayStr)->count();
        $overdueCount = $allReservedBase->filter(fn($o) => $o->pickup_date && $o->pickup_date->toDateString() < $todayStr)->count();
        $upcomingCount = $allReservedBase->filter(fn($o) => !$o->pickup_date || $o->pickup_date->toDateString() > $todayStr)->count();

        // Apply Status Filter
        if ($this->reservedFilterStatus === 'overdue') {
            $reservedQuery->whereDate('pickup_date', '<', $todayStr);
        } elseif ($this->reservedFilterStatus === 'today') {
            $reservedQuery->whereDate('pickup_date', '=', $todayStr);
        } elseif ($this->reservedFilterStatus === 'upcoming') {
            $reservedQuery->where(function ($q) use ($todayStr) {
                $q->whereNull('pickup_date')
                  ->orWhereDate('pickup_date', '>', $todayStr);
            });
        }

        // Apply Search Filter
        if (!empty(trim($this->reservedSearch))) {
            $s = '%' . trim($this->reservedSearch) . '%';
            $reservedQuery->where(function ($q) use ($s) {
                $q->where('order_number', 'like', $s)
                  ->orWhere('customer_name', 'like', $s)
                  ->orWhere('customer_phone', 'like', $s);
            });
        }

        $reservedOrders = $reservedQuery->latest()->get();

        return view('livewire.pos-manager', [
            'products'            => $products,
            'allProductsJson'     => $allProductsJson,
            'vouchers'            => $this->vouchers,
            'paymentMethods'      => $this->paymentMethods,
            'sessionOrders'       => $sessionOrders,
            'sessionCustomers'    => $sessionCustomers,
            'sessionPettyCash'    => $sessionPettyCash,
            'sessionReturns'      => $sessionReturns,
            'sessionStats'        => $sessionStats,
            'reservedOrders'      => $reservedOrders,
            'totalReservedCount'  => $totalReservedCount,
            'todayCount'          => $todayCount,
            'overdueCount'        => $overdueCount,
            'upcomingCount'       => $upcomingCount,
            'supervisorsList'     => $this->supervisorsList,
            'allPosCustomers'     => $this->allPosCustomers,
        ]);
    }

    #[\Livewire\Attributes\Computed]
    public function allPosCustomers()
    {
        $customers = \App\Models\PosCustomer::orderBy('name')
            ->get(['id', 'name', 'phone', 'stamp_count', 'points_balance', 'completed_cards_count']);

        return $customers->map(function ($c) {
            $usedVoucherIds = \App\Models\Order::where(function ($q) use ($c) {
                    if ($c->phone) {
                        $q->where('customer_phone', $c->phone);
                    }
                    if ($c->name) {
                        $q->orWhere('customer_name', $c->name);
                    }
                })
                ->whereNotNull('voucher_id')
                ->pluck('voucher_id')
                ->unique()
                ->values()
                ->toArray();

            return [
                'id'                    => $c->id,
                'name'                  => $c->name,
                'phone'                 => $c->phone,
                'stamp_count'           => (int) $c->stamp_count,
                'points_balance'        => (int) $c->points_balance,
                'completed_cards_count' => (int) $c->completed_cards_count,
                'used_voucher_ids'      => $usedVoucherIds,
            ];
        })->toArray();
    }

    #[\Livewire\Attributes\Computed]
    public function vouchers()
    {
        return \App\Models\Voucher::whereIn('usable_channel', ['pos_only', 'both'])
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')->orWhereColumn('used_count', '<', 'max_uses');
            })
            ->get();
    }

    #[\Livewire\Attributes\Computed]
    public function paymentMethods()
    {
        return \App\Models\PaymentMethod::where('is_active', true)
            ->get()
            ->filter(function ($method) {
                $config = is_array($method->config) ? $method->config : (json_decode($method->config ?? '[]', true) ?? []);
                $availability = $config['availability'] ?? 'both';
                return in_array($availability, ['both', 'offline']);
            })
            ->map(function ($method) {
                return [
                    'id'        => $method->id,
                    'name'      => $method->name,
                    'code'      => $method->code,
                    'logo'      => $method->logo ? asset('storage/' . $method->logo) : null,
                    'is_cash'   => strtolower($method->code) === 'tunai' || strtolower($method->name) === 'tunai',
                ];
            })->values();
    }

    #[\Livewire\Attributes\Computed]
    public function supervisorsList()
    {
        return User::whereNotNull('pos_pin')->get()->filter(function ($u) {
            return $u->hasAnyRole(['super_admin', 'owner', 'manager', 'finance']) || in_array($u->role, ['super_admin', 'owner', 'manager', 'finance']);
        })->map(function ($u) {
            return [
                'id'   => $u->id,
                'name' => $u->name,
                'role' => strtoupper($u->roles->pluck('name')->first() ?? $u->role ?? 'SUPERVISOR'),
            ];
        })->values();
    }

    #[\Livewire\Attributes\Computed]
    public function availableHistoryPaymentMethods()
    {
        $query = Order::where('source', 'pos');

        if ($this->historyDateFilter === 'shift' && $this->activeSession) {
            $query->where('pos_session_id', $this->activeSession->id);
        } elseif ($this->historyDateFilter === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($this->historyDateFilter === 'yesterday') {
            $query->whereDate('created_at', now()->subDay()->toDateString());
        } elseif ($this->historyDateFilter === '7days') {
            $query->where('created_at', '>=', now()->subDays(7)->startOfDay());
        } elseif ($this->historyDateFilter === '30days') {
            $query->where('created_at', '>=', now()->subDays(30)->startOfDay());
        }

        if ($this->historyStatusFilter === 'completed') {
            $query->where('status', 'completed');
        } elseif ($this->historyStatusFilter === 'cancelled') {
            $query->where('status', 'cancelled');
        }

        $rawMethods = $query->whereNotNull('payment_method')
            ->distinct()
            ->pluck('payment_method')
            ->map(fn($m) => strtolower(trim($m)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $formatted = [];
        $hasCash = false;

        foreach ($rawMethods as $m) {
            if (in_array($m, ['cash', 'tunai'])) {
                if (!$hasCash) {
                    $formatted['cash'] = 'Tunai';
                    $hasCash = true;
                }
            } elseif ($m === 'qris') {
                $formatted['qris'] = 'QRIS';
            } elseif ($m === 'transfer') {
                $formatted['transfer'] = 'Transfer';
            } elseif ($m === 'edc') {
                $formatted['edc'] = 'EDC';
            } else {
                $formatted[$m] = strtoupper($m);
            }
        }

        return $formatted;
    }

    #[\Livewire\Attributes\Computed]
    public function availableHistoryStatuses()
    {
        $query = Order::where('source', 'pos');

        if ($this->historyDateFilter === 'shift' && $this->activeSession) {
            $query->where('pos_session_id', $this->activeSession->id);
        } elseif ($this->historyDateFilter === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($this->historyDateFilter === 'yesterday') {
            $query->whereDate('created_at', now()->subDay()->toDateString());
        } elseif ($this->historyDateFilter === '7days') {
            $query->where('created_at', '>=', now()->subDays(7)->startOfDay());
        } elseif ($this->historyDateFilter === '30days') {
            $query->where('created_at', '>=', now()->subDays(30)->startOfDay());
        }

        $rawStatuses = $query->whereNotNull('status')
            ->distinct()
            ->pluck('status')
            ->map(fn($s) => strtolower(trim($s)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $labels = [
            'completed'  => 'Selesai',
            'cancelled'  => 'Dibatalkan',
            'pending'    => 'Menunggu',
            'processing' => 'Diproses',
            'refunded'   => 'Pengembalian Dana',
            'returned'   => 'Diretur',
        ];

        $formatted = [];
        foreach ($rawStatuses as $s) {
            $formatted[$s] = $labels[$s] ?? ucwords($s);
        }

        return $formatted;
    }

    #[\Livewire\Attributes\Computed]
    public function availableReturnTypes()
    {
        $query = \App\Models\PosReturn::query();

        if ($this->returnDateFilter === 'shift' && $this->activeSession) {
            $query->where('pos_session_id', $this->activeSession->id);
        } elseif ($this->returnDateFilter === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($this->returnDateFilter === 'yesterday') {
            $query->whereDate('created_at', now()->subDay()->toDateString());
        } elseif ($this->returnDateFilter === '7days') {
            $query->where('created_at', '>=', now()->subDays(7)->startOfDay());
        } elseif ($this->returnDateFilter === '30days') {
            $query->where('created_at', '>=', now()->subDays(30)->startOfDay());
        }

        $rawTypes = $query->whereNotNull('type')
            ->distinct()
            ->pluck('type')
            ->map(fn($t) => strtolower(trim($t)))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $labels = [
            'exchange' => 'Tukar Barang',
            'refund'   => 'Pengembalian Dana (Refund)',
        ];

        $formatted = [];
        foreach ($rawTypes as $t) {
            $formatted[$t] = $labels[$t] ?? ucwords($t);
        }

        return $formatted;
    }

    public function saveHeldCartToDb($payload)
    {
        $data = is_string($payload) ? json_decode($payload, true) : $payload;
        \App\Models\PosHeldCart::updateOrCreate(
            ['hold_id' => $data['hold_id']],
            [
                'cashier_name' => Auth::user()->name ?? 'Kasir',
                'user_id' => Auth::id(),
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'cart_data' => $data['cart_data'] ?? [],
                'total' => (float) ($data['total'] ?? 0),
            ]
        );
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Antrean keranjang berhasil disimpan ke server toko!']);
    }

    public function deleteHeldCartFromDb($holdId)
    {
        \App\Models\PosHeldCart::where('hold_id', $holdId)->delete();
    }

    #[\Livewire\Attributes\Computed]
    public function activeEventPromotions()
    {
        return \App\Models\PosEventPromotion::active()->get();
    }

    #[\Livewire\Attributes\Computed]
    public function sharedHeldCarts()
    {
        return \App\Models\PosHeldCart::latest()->get();
    }
}
