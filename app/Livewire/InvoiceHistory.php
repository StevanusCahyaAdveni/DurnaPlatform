<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use App\Models\Income;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\Subscription;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceHistory extends Component
{
    use WithPagination;

    public $UserSaldo;
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
        // End Calculate User Balance

        $data = [
            'getAllSubscriptions' => Subscription::where('user_id', Auth::user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10),
        ];

        return view('livewire.invoice-history', $data);
    }

    public function getNameClassOrCourse($type, $id)
    {
        if ($type === 'class') {
            return ClassGroup::where('id', $id)->first()->class_name ?? 'Unknown Class';
        } elseif ($type === 'course') {
            return Course::where('id', $id)->first()->course_name ?? 'Unknown Course';
        }
        return 'Unknown';
    }
}
