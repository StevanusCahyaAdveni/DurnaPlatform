<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Withdrawal;
use App\Models\User;
use App\Services\XenditService;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class Withdraw extends Component
{
    use WithPagination;

    public $withdrawAmount;
    public $withdrawalType;
    public $bankCode;
    public $accountNumber;
    public $accountHolderName;
    public $ewalletType;
    public $phoneNumber;
    public $notes;
    public $adminFee = 5000;

    protected $xenditService;

    public function boot(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    public function render()
    {
        // Calculate balance from 3 tables: Income + Withdrawals - Subscriptions
        $userSaldo = $this->calculateUserBalance();

        $withdrawals = Withdrawal::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.withdraw', [
            'userSaldo' => $userSaldo,
            'withdrawals' => $withdrawals
        ]);
    }

    /**
     * Calculate user balance from Income, Withdrawals, and Subscriptions
     */
    private function calculateUserBalance()
    {
        $userId = Auth::id();

        // Calculate balance from 3 tables: Income + Withdrawals - Subscriptions
        $getIncome = \App\Models\Income::where('user_id', $userId)
            ->where('status', 'paid')
            ->sum('nominal') ?? 0;

        $getWithdrawals = \App\Models\Withdrawal::where('user_id', $userId)
            ->where('status', 'completed')
            ->sum('total_amount') ?? 0;

        $getSubscription = \App\Models\Subscription::where('user_id', $userId)->sum('nominal') ?? 0;
        $getIncomeClassBySubscription = \App\Models\Subscription::join('class_groups', 'class_groups.id', '=', 'subscriptions.class_uuid')->where('class_groups.user_id', $userId)->sum('nominal') ?? 0;
        $getIncomeCourseBySubscription = \App\Models\Subscription::join('courses', 'courses.id', '=', 'subscriptions.course_uuid')->where('courses.user_id', $userId)->sum('nominal') ?? 0;

        // Balance = Income - Withdrawals - Subscriptions
        $UserSaldo = ($getIncome + $getIncomeClassBySubscription + $getIncomeCourseBySubscription) - $getWithdrawals - $getSubscription;

        // Balance = Income - Withdrawals - Subscriptions
        return $UserSaldo;
    }

    public function updatedWithdrawAmount()
    {
        if ($this->withdrawAmount && $this->withdrawAmount < 50000) {
            session()->flash('error', 'Minimum withdrawal amount is Rp 50.000');
        }
    }

    public function processWithdrawal()
    {
        $this->validate([
            'withdrawAmount' => 'required|numeric|min:50000',
            'withdrawalType' => 'required|in:bank,ewallet',
        ]);

        $userSaldo = $this->calculateUserBalance();
        $totalAmount = $this->withdrawAmount + $this->adminFee;

        // Check if user has enough balance
        if ($userSaldo < $totalAmount) {
            session()->flash('error', 'Insufficient balance. You need Rp ' . number_format($totalAmount, 0, 0, '.') . ' (including admin fee)');
            return;
        }

        // Validate specific withdrawal method fields
        if ($this->withdrawalType === 'bank') {
            $this->validate([
                'bankCode' => 'required|string',
                'accountNumber' => 'required|string|min:5',
                'accountHolderName' => 'required|string|min:3',
            ]);
        } elseif ($this->withdrawalType === 'ewallet') {
            $this->validate([
                'ewalletType' => 'required|string',
                'phoneNumber' => 'required|string|min:10|max:15',
            ]);
        }

        // Create withdrawal record
        $externalId = 'withdraw_' . Auth::id() . '_' . time();

        $withdrawal = Withdrawal::create([
            'user_id' => Auth::id(),
            'amount' => $this->withdrawAmount,
            'withdrawal_type' => $this->withdrawalType,
            'bank_code' => $this->bankCode,
            'account_number' => $this->accountNumber,
            'account_holder_name' => $this->accountHolderName,
            'ewallet_type' => $this->ewalletType,
            'phone_number' => $this->phoneNumber,
            'status' => 'pending',
            'external_id' => $externalId,
            'admin_fee' => $this->adminFee,
            'total_amount' => $totalAmount,
            'notes' => $this->notes,
        ]);

        // No need to deduct balance from users table - using mathematical calculation

        // Process with Xendit (for production)
        if (config('app.env') === 'production') {
            $this->processWithXendit($withdrawal);
        } else {
            // For development, mark as processing
            $withdrawal->update(['status' => 'processing']);
            session()->flash('success', 'Withdrawal request submitted successfully! (Development Mode)');
        }

        // Reset form
        $this->reset([
            'withdrawAmount',
            'withdrawalType',
            'bankCode',
            'accountNumber',
            'accountHolderName',
            'ewalletType',
            'phoneNumber',
            'notes'
        ]);
    }

    private function processWithXendit($withdrawal)
    {
        try {
            if ($withdrawal->withdrawal_type === 'bank') {
                $result = $this->xenditService->createBankDisbursement(
                    $withdrawal->external_id,
                    $withdrawal->amount,
                    $withdrawal->bank_code,
                    $withdrawal->account_number,
                    $withdrawal->account_holder_name,
                    'Withdrawal from DurnaPlatform'
                );
            } else {
                $result = $this->xenditService->createEwalletDisbursement(
                    $withdrawal->external_id,
                    $withdrawal->amount,
                    $withdrawal->ewallet_type,
                    $withdrawal->phone_number,
                    'Withdrawal from DurnaPlatform'
                );
            }

            if ($result['success']) {
                $withdrawal->update([
                    'xendit_disbursement_id' => $result['disbursement_id'],
                    'status' => 'processing'
                ]);

                session()->flash('success', 'Withdrawal request submitted successfully!');
            } else {
                // Mark as failed if Xendit fails (no need to refund - using mathematical calculation)
                $withdrawal->update(['status' => 'failed']);
                session()->flash('error', 'Withdrawal failed: ' . $result['error']);
            }
        } catch (\Exception $e) {
            // Mark as failed on exception (no need to refund - using mathematical calculation)
            $withdrawal->update(['status' => 'failed']);
            session()->flash('error', 'Withdrawal failed: ' . $e->getMessage());
        }
    }

    public function simulateComplete($withdrawalId)
    {
        if (config('app.env') !== 'local') {
            session()->flash('error', 'Simulation only available in development.');
            return;
        }

        $withdrawal = Withdrawal::find($withdrawalId);
        if ($withdrawal && $withdrawal->status === 'processing') {
            $withdrawal->update([
                'status' => 'completed',
                'processed_at' => now()
            ]);

            session()->flash('success', 'Withdrawal simulation completed!');
        }
    }

    public function cancelWithdrawal($withdrawalId)
    {
        $withdrawal = Withdrawal::where('id', $withdrawalId)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->first();

        if ($withdrawal) {
            // Simply mark as cancelled (no need to refund - using mathematical calculation)
            $withdrawal->update(['status' => 'cancelled']);

            session()->flash('success', 'Withdrawal cancelled successfully.');
        } else {
            session()->flash('error', 'Cannot cancel this withdrawal.');
        }
    }
}
