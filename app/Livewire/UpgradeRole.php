<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use app\Models\User;
use Livewire\Component;

class UpgradeRole extends Component
{
    public $User;

    public function __construct()
    {
        $this->User = new User();
    }
    public $email = "";
    public $role = "";
    public function mount()
    {
        // Pindahkan logika penetapan nilai ke sini
        $this->email = Auth::user()->email;
        $this->role = Auth::user()->role;
        // Atau dengan helper:
        // $this->userEmail = auth()->user()->email;

        // Lebih aman lagi (menggunakan nullsafe operator di PHP 8+):
        // $this->userEmail = Auth::user()?->email;
        // Atau dengan helper:
        // $this->userEmail = auth()->user()?->email;
    }
    public function render()
    {
        return view('livewire.upgrade-role');
    }

    public function upgradeRole()
    {
        // Validasi input
        $this->validate([
            'email' => 'required|email',
            'role' => 'required|string',
        ]);

        // Update role pengguna
        $data = [
            'role' => $this->role,
        ];

        $this->User->where('email', $this->email)->update($data);

        // Berikan umpan balik kepada pengguna
        session()->flash('message', 'Role berhasil diupgrade!');
        return redirect()->route('upgrade-role');
        

        // Redirect atau lakukan tindakan lain jika diperlukan
    }
}
