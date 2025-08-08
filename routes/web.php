<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
// use App\Livewire\Dashboard;
use App\Livewire\UserDashboard;
use App\Livewire\UserClass;
use App\Livewire\UserClassDetail;
use App\Livewire\OurClass;
use App\Livewire\OurClassPreview;
use App\Livewire\TeacherClass;
use App\Livewire\TeacherTask;
use App\Livewire\TeacherExam;
use App\Livewire\UpgradeRole;
use App\Livewire\UserTaskAnswer;
use App\Livewire\TeacherTaskAnswer;
use App\Livewire\AiChat;
use App\Livewire\UserIncome;
use App\Livewire\InvoiceHistory;
use App\Livewire\Withdraw;
// use App\Models\ClassTaskAnswer;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Route::view('dashboard', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::group(['middleware' => 'auth'], function () {
    // Route::get('/dashboard', Dashboard::class);
});

Route::post('/ckeditor/upload-image', [TeacherTask::class, 'uploadCkeditorImage'])
    ->name('ckeditor.upload-image');

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/user-dashboard', UserDashboard::class)->name('user-dashboard');

    // User Class And Submit Tast or Exam
    Route::get('/user-class', UserClass::class)->name('user-class');
    Route::get('/user-class-detail/{id}', UserClassDetail::class)->name('user-class-detail');

    // User Searching and Join Class
    Route::get('/our-class', OurClass::class)->name('our-class');
    Route::get('/our-class-preview/{id}', OurClassPreview::class)->name('our-class-preview');

    // Teacher Create And Manage Class, Task and Exam
    Route::get('/teacher-class', TeacherClass::class)->name('teacher-class');
    Route::get('/teacher-task', TeacherTask::class)->name('teacher-task');
    Route::get('/teacher-exam', TeacherExam::class)->name('teacher-exam');
    Route::get('/teacher-task-answer/{id}', TeacherTaskAnswer::class)->name('teacher-task-answer');

    // AI Chat
    Route::get('/ai-chat', AiChat::class)->name('ai-chat');

    // User create and manage tasks answer
    Route::get('/user-task-answer/{id}', UserTaskAnswer::class)->name('user-task-answer');

    // User Money Income
    Route::get('/user-income', UserIncome::class)->name('user-income');
    Route::get('/invoice-history', InvoiceHistory::class)->name('invoice-history');
    Route::get('/withdraw', Withdraw::class)->name('withdraw');

    // User
    Route::get('/upgrade-role', UpgradeRole::class)->name('upgrade-role');
    Route::redirect('settings', 'settings/profile');
    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

// Xendit payment redirect routes
Route::get('/income/success', function () {
    // Check if there's an external_id parameter from Xendit
    $externalId = request()->get('external_id');

    if ($externalId && str_starts_with($externalId, 'income_')) {
        $incomeId = str_replace('income_', '', $externalId);

        // Auto-check payment status for this income
        $income = \App\Models\Income::find($incomeId);
        if ($income && $income->status === 'pending') {
            $xenditService = app(\App\Services\XenditService::class);
            $invoice = $xenditService->getInvoice($income->xendit_invoice_id);

            if ($invoice && $xenditService->isInvoicePaid($invoice)) {
                $income->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_channel' => $invoice['payment_channel'] ?? $invoice['payment_method'] ?? null,
                ]);

                return redirect()->route('user-dashboard')->with('success', 'Payment completed successfully! Your top-up of Rp ' . number_format($income->nominal, 0, 0, '.') . ' has been processed.');
            }
        }
    }

    return redirect()->route('user-income')->with('success', 'Payment completed successfully! Your top-up has been processed.');
})->name('income.success');

Route::get('/income/failed', function () {
    return redirect()->route('user-income')->with('error', 'Payment failed or was cancelled. Please try again.');
})->name('income.failed');

// Xendit webhook route (outside auth middleware)
Route::post('/webhook/xendit', [App\Http\Controllers\WebhookController::class, 'xenditWebhook'])->name('webhook.xendit');

require __DIR__ . '/auth.php';
