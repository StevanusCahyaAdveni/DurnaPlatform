<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassGroup;
use App\Models\ClassJoin;
use App\Models\Subscription;
use App\Models\Income;

class OurClassPreview extends Component
{
    public $ClassGroup;
    public $classGroupId;
    public $classCode;
    public $singleDataController;
    public $currentMembersController;
    public $UserSaldo;

    public function __construct()
    {
        $this->ClassGroup = new ClassGroup();
    }

    public function mount($id){
        $this->classGroupId = $id;

        if (!ClassGroup::join('users', 'class_groups.user_id', '=', 'users.id')->select('class_groups.*', 'users.name')->where('class_groups.id', $this->classGroupId)->first()) {
            session()->flash('message', 'Class not found!');
            return $this->redirect(route('our-class'), navigate: true);
        }
    }
    
    public function render()
    {
        // Get Saldo User 
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
            $this->UserSaldo = ($getIncome + $getIncomeClassBySubscription + $getIncomeCourseBySubscription) - $getWithdrawals - $getSubscription;
        // End Get Saldo User 


        $this->singleDataController = ClassGroup::join('users', 'class_groups.user_id', '=', 'users.id')->select('class_groups.*', 'users.name')->where('class_groups.id', $this->classGroupId)->first();
        $this->currentMembersController = ClassJoin::where('class_group_id', $this->classGroupId)->count();
        $data = [
            'singleData' => $this->singleDataController,
            'getJoin' => ClassJoin::where('class_group_id', $this->classGroupId)->where('user_id', Auth::user()->id)->count(),
            'currentMembers' => $this->currentMembersController,
        ];
        return view('livewire.our-class-preview', $data);
    }

    public function joinClass(){
        if($this->singleDataController->participants > $this->currentMembersController){
            // User can join the class
            if($this->singleDataController->price <= $this->UserSaldo){  
                if($this->singleDataController->subscription == 'monthly'){ // Use the set price for other subscriptions
                    $expiredAt = now()->addMonth();
                }elseif($this->singleDataController->subscription == 'yearly'){
                    $expiredAt = now()->addYear();
                }else {
                    // Set an arbitrary far future date for one-time subscription
                    $expiredAt = '9999-12-31';
                }
    
                Subscription::create([
                    'tipe' => 'class',
                    'class_uuid' => $this->classGroupId,
                    'course_uuid' => null, // Assuming this is not applicable for class joins
                    'user_id' => Auth::user()->id,
                    'nominal' => $this->singleDataController->price, // Assuming no fee for joining classes
                    'payment_method' => 'Qris BCA', // Assuming free join
                    'expired_at' => $expiredAt // Assuming a year validity for the join
                ]);
                ClassJoin::create([
                    'class_group_id' => $this->classGroupId,
                    'user_id' => Auth::user()->id
                ]);
                session()->flash('message', 'Successfully join class!');
            }else{
                session()->flash('message', 'Insufficient balance to join this class!');
                // return $this->redirect(route('user-income'), navigate: true); // Redirect to income page if insufficient balance
            }

        }else{
            session()->flash('message', 'Failed to join class!');
        }

        // Flux::modals()->close();
    }
    
    public function leaveClass() {
        ClassJoin::where('class_group_id', $this->classGroupId)->where('user_id', Auth::user()->id)->delete();
        session()->flash('message', 'Successfully leave class!');
    }

    
}
