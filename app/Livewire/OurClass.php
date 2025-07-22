<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassGroup; // Assuming you have a ClassModel for classes
use Livewire\WithPagination;

class OurClass extends Component
{
    use WithPagination;
    public $search = "";
    public $ClassGroup;
    public $classCode;
    public $user_id;

    public function __construct()
    {
        $this->ClassGroup = new ClassGroup();
    }

    public function mount()
    {
        $this->user_id = Auth::user()->id;
        // $this->code = random_int(1000, 9999) . "-" . random_int(10000, 99999); // Set default class code to current timestamp
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Mengatur ulang paginasi ke halaman 1 saat pencarian berubah
    }

    public function render()
    {
        // Membangun query dasar untuk ClassGroup yang dibuat oleh user ini
        $query = ClassGroup::join('users', 'class_groups.user_id', '=', 'users.id')->select('class_groups.*', 'users.name')->where('class_category', 'Public');
        // Menambahkan kondisi pencarian jika $this->search tidak kosong
        if (!empty($this->search)) {
            $query->Where('class_name', 'like', '%' . $this->search . '%');
        }
        // Menggunakan metode paginate() untuk membagi hasil menjadi halaman-halaman
        // Angka 10 adalah jumlah item per halaman. Kamu bisa ubah sesuai kebutuhan.
        $classGroups = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.our-class', [
            'classGroups' => $classGroups,
        ]);
    }

    public function srcByCode()
    {
        $this->validate([
            'classCode' => 'required|string',
        ]);

        $class = ClassGroup::where('class_code', $this->classCode)->first();

        if ($class) {
            // $this->classCode = $class->id;
            // session()->flash('message', 'Class found successfully!');
            return redirect()->route('our-class-preview', ['id' => $class->id]);
        } else {
            session()->flash('error', 'Class not found with the provided code.');
        }
    }
}
