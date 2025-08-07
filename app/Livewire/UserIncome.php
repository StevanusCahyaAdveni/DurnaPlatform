<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Income; // Assuming you have an Income model
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class UserIncome extends Component
{
    use WithPagination;
    public $nominal;
    public $payment_method;
    public $status = 'pending'; // Default status

    public function render()
    {
        $data = [
            "getUserIncome" => Income::where('user_id', Auth::user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10), // Paginate the income records for the user
        ];

        return view('livewire.user-income', $data);
    }

    public function createIncome()
    {
        Income::create([
            'user_id' => Auth::user()->id,
            'nominal' => $this->nominal,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
        ]);
        $this->reset(['nominal', 'payment_method', 'status']); // Reset the input fields after creation
        session()->flash('message', 'Income created successfully!'); // Flash message for success
    }
}
