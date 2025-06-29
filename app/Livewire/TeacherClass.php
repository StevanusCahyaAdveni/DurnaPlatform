<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassGroup; // Assuming you have a ClassModel for classes
use Livewire\WithPagination;

class TeacherClass extends Component
{
    use WithPagination;

    public $search = "";
    public $ClassGroup;
    public $user_id;
    public $name ="";
    public $description="";
    public $code; // Default to current timestamp as class code
    public $category="Public";
    public function __construct()
    {
        $this->ClassGroup = new ClassGroup();
    }

    public function mount()
    {
        $this->user_id = Auth::user()->id;
        $this->code = random_int(1000, 9999) . "-" . random_int(10000, 99999); // Set default class code to current timestamp
    }

    public function updatingSearch()
    {
        $this->resetPage(); // Mengatur ulang paginasi ke halaman 1 saat pencarian berubah
    }

    public function render()
    {
        // Mendapatkan ID user yang sedang login
        // Gunakan Auth::id() atau auth()->id() untuk keamanan
        $userId = Auth::id();

        // Membangun query dasar untuk ClassGroup yang dibuat oleh user ini
        $query = ClassGroup::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        // Menambahkan kondisi pencarian jika $this->search tidak kosong
        if (!empty($this->search)) {
            $query->where('class_name', 'like', '%' . $this->search . '%');
        }

        // Menggunakan metode paginate() untuk membagi hasil menjadi halaman-halaman
        // Angka 10 adalah jumlah item per halaman. Kamu bisa ubah sesuai kebutuhan.
        $classGroups = $query->paginate(10);

        // Mengembalikan view dengan data paginasi
        return view('livewire.teacher-class', [
            'classGroups' => $classGroups, // Mengubah nama variabel agar lebih jelas
        ]);
    }

    public function addClass()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'code' => 'required|string|max:10|unique:class_groups,class_code',
            'category' => 'required|string|in:Public,Private',
        ]);

        $this->ClassGroup->class_name = $this->name;
        $this->ClassGroup->class_description = $this->description;
        $this->ClassGroup->class_code = $this->code;
        $this->ClassGroup->class_category = $this->category;
        $this->ClassGroup->user_id = Auth::user()->id; 
        $this->ClassGroup->save();
        $this->reset(['name', 'description', 'code', 'category']); // Reset form fields

        session()->flash('message', 'Class successfully added!');
    }
}
