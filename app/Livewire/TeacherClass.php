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
    public $classGroupId = ""; // Default to current timestamp as class code
    public $listenersForm = "Add"; // Default to current timestamp as class code
    public $category="Public";
    public $price = '0'; // Default price
    public $subscription = 'monthly'; // Default subscription type
    public $participants = '50'; // Default participants

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
        $this->code = random_int(1000, 9999) . "-" . random_int(10000, 99999); // Set default class code to current timestamp
        // Mendapatkan ID user yang sedang login
        // Gunakan Auth::id() atau auth()->id() untuk keamanan
        $userId = Auth::id();
        // Membangun query dasar untuk ClassGroup yang dibuat oleh user ini
        $query = ClassGroup::join('users', 'class_groups.user_id', '=', 'users.id')->select('class_groups.*', 'users.name')->where('user_id', $userId)
            ->orderBy('created_at', 'desc');
        // Menambahkan kondisi pencarian jika $this->search tidak kosong
        if (!empty($this->search)) {
            $query->Where('class_name', 'like', '%' . $this->search . '%');
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
            'price' => 'required|string|max:50',
            'subscription' => 'nullable|in:monthly,yearly,one_time',
            'participants' => 'required|string|max:100',
        ]);

        if($this->listenersForm == "Update"){
            // Update existing class group
            $classGroup = ClassGroup::find($this->classGroupId);
            if ($classGroup) {
                $classGroup->where('id', $this->classGroupId)->update([
                    'class_name' => $this->name,
                    'price' => $this->price,
                    'subscription' => $this->subscription,
                    'participants' => $this->participants,
                    'class_description' => $this->description,
                    // 'class_code' => $this->code,
                    'class_category' => $this->category,
                ]);
                session()->flash('message', 'Class successfully updated!');
            } else {
                session()->flash('error', 'Class not found!');
            }
            $this->listenersForm = "Add"; // Reset to Add after update

        }else{
            ClassGroup::create([
                'class_name' => $this->name,
                'price' => $this->price,
                'subscription' => $this->subscription,
                'participants' => $this->participants,
                'class_description' => $this->description,
                'class_code' => $this->code,
                'class_category' => $this->category,
                'user_id' => Auth::user()->id,
            ]);
        }
        
        $this->code = random_int(1000, 9999) . "-" . random_int(10000, 99999); // Set default class code to current timestamp
        $this->reset(['name', 'description', 'category', 'price', 'subscription', 'participants']); // Reset form fields
        session()->flash('message', 'Class successfully added!');
    }

    public function deleteClass($id) {
        classGroup::where('id', $id)->where('user_id', Auth::user()->id)->delete();        
        session()->flash('message', 'Class successfully deleted!');
    }

    public function upData($id){
        if($id != ''){
            $getClass = ClassGroup::find($id);
            if ($getClass) {
                $this->name = $getClass->class_name;
                $this->description = $getClass->class_description;
                $this->code = $getClass->class_code;
                $this->category = $getClass->class_category;
                $this->price = $getClass->price;
                $this->subscription = $getClass->subscription;
                $this->participants = $getClass->participants;
                $this->classGroupId = $getClass->id; // Set
                $this->listenersForm = "Update"; // Change listener to Update
            } else {
                session()->flash('error', 'Class not found!');
            }
        }elseif($id == ''){
            $this->code = random_int(1000, 9999) . "-" . random_int(10000, 99999); // Set default class code to current timestamp
            $this->reset(['name', 'description', 'category']);
        }
    }
}
