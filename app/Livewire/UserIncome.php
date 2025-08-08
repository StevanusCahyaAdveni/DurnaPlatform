<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Income;
use App\Models\Subscription;
use App\Services\XenditService;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class UserIncome extends Component
{
    use WithPagination;

    public $nominal;
    public $payment_method;
    public $status = 'pending';
    public $UserSaldo;
    public $selectedIncome = null;

    protected $xenditService;

    public function boot(XenditService $xenditService)
    {
        $this->xenditService = $xenditService;
    }

    protected $listeners = ['refreshComponent'];

    public function render()
    {
        // Calculate balance from 3 tables: Income + Withdrawals - Subscriptions
            $getIncome = \App\Models\Income::where('user_id', Auth::user()->id)
                ->where('status', 'paid')
                ->sum('nominal') ?? 0;

            $getWithdrawals = \App\Models\Withdrawal::where('user_id', Auth::user()->id)
                ->where('status', 'completed')
                ->sum('total_amount') ?? 0;

            $getSubscription = \App\Models\Subscription::where('user_id', Auth::user()->id)->sum('nominal') ?? 0;
            $getIncomeClassBySubscription = \App\Models\Subscription::join('class_groups', 'class_groups.id', '=', 'subscriptions.class_uuid')->where('class_groups.user_id', Auth::user()->id)->sum('nominal') ?? 0;
            $getIncomeCourseBySubscription = \App\Models\Subscription::join('courses', 'courses.id', '=', 'subscriptions.course_uuid')->where('courses.user_id', Auth::user()->id)->sum('nominal') ?? 0;

            // Balance = Income - Withdrawals - Subscriptions
            $UserSaldo = ($getIncome + $getIncomeClassBySubscription + $getIncomeCourseBySubscription) - $getWithdrawals - $getSubscription;
            $this->UserSaldo = $UserSaldo;
        // Calculate balance from 3 tables: Income + Withdrawals - Subscriptions

        $data = [
            "getUserIncome" => Income::where('user_id', Auth::user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ];

        return view('livewire.user-income', $data);
    }

    public function createIncome()
    {
        $this->validate([
            'nominal' => 'required|numeric|min:10000|max:10000000', // Min 10k, Max 10M
            'payment_method' => 'required|string',
        ]);

        $income = Income::create([
            'user_id' => Auth::user()->id,
            'nominal' => $this->nominal,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
        ]);

        // Automatically create Xendit invoice
        $this->processPayment($income->id);

        $this->reset(['nominal', 'payment_method']);
        session()->flash('message', 'Top up created! Redirecting to payment...');
    }

    public function processPayment($incomeId)
    {
        $income = Income::find($incomeId);

        if (!$income || !$income->isPending()) {
            session()->flash('error', 'Invalid transaction or already processed.');
            return;
        }

        $user = Auth::user();
        $result = $this->xenditService->createInvoice(
            $income->id,
            $income->nominal,
            $user->email,
            $user->name
        );

        if ($result['success']) {
            $income->update([
                'xendit_invoice_id' => $result['invoice_id'],
                'xendit_invoice_url' => $result['invoice_url'],
            ]);

            // Redirect to Xendit payment page
            return redirect()->away($result['invoice_url']);
        } else {
            session()->flash('error', 'Failed to create payment: ' . $result['error']);
        }
    }

    public function payNow($incomeId)
    {
        $this->processPayment($incomeId);
    }

    public function checkPaymentStatus($incomeId)
    {
        $income = Income::find($incomeId);

        if (!$income) {
            session()->flash('error', 'Transaction not found.');
            return;
        }

        if (!$income->xendit_invoice_id) {
            session()->flash('error', 'No payment invoice found for this transaction.');
            return;
        }

        $invoice = $this->xenditService->getInvoice($income->xendit_invoice_id);

        if (!$invoice) {
            session()->flash('error', 'Unable to retrieve payment status from Xendit.');
            return;
        }

        // Use the helper method to check if payment is completed
        if ($this->xenditService->isInvoicePaid($invoice)) {
            $income->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_channel' => $invoice['payment_channel'] ?? $invoice['payment_method'] ?? null,
            ]);

            session()->flash('message', 'Payment confirmed successfully!');
        } else {
            $statusText = $this->xenditService->getStatusText($invoice['status']);
            session()->flash('error', "Payment status: {$statusText}. Please complete payment if still pending.");
        }
    }

    public function refreshComponent()
    {
        // Check all pending payments for this user
        $pendingIncomes = Income::where('user_id', Auth::user()->id)
            ->where('status', 'pending')
            ->whereNotNull('xendit_invoice_id')
            ->get();

        foreach ($pendingIncomes as $income) {
            $this->checkPaymentStatusSilent($income->id);
        }
    }

    private function checkPaymentStatusSilent($incomeId)
    {
        $income = Income::find($incomeId);

        if (!$income || !$income->xendit_invoice_id) {
            return;
        }

        $invoice = $this->xenditService->getInvoice($income->xendit_invoice_id);

        if ($invoice && $this->xenditService->isInvoicePaid($invoice)) {
            $income->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_channel' => $invoice['payment_channel'] ?? $invoice['payment_method'] ?? null,
            ]);
        }
    }
}
