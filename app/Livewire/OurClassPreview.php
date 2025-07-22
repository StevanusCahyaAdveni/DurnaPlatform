<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassGroup;
use App\Models\ClassJoin;

class OurClassPreview extends Component
{
    public $ClassGroup;
    public $classGroupId;
    public $classCode;

    public function __construct()
    {
        $this->ClassGroup = new ClassGroup();
    }

    public function mount($id){
        $this->classGroupId = $id;
    }

    public function render()
    {
        $data = [
            'singleData' => ClassGroup::join('users', 'class_groups.user_id', '=', 'users.id')->select('class_groups.*', 'users.name')->where('class_groups.id', $this->classGroupId)->first(),
            'getJoin' => ClassJoin::where('class_group_id', $this->classGroupId)->where('user_id', Auth::user()->id)->count(),
        ];
        return view('livewire.our-class-preview', $data);
    }

    public function joinClass(){
        ClassJoin::create([
            'class_group_id' => $this->classGroupId,
            'user_id' => Auth::user()->id
        ]);
        session()->flash('message', 'Successfully join class!');
        // Flux::modals()->close();
    }
    
    public function leaveClass() {
        ClassJoin::where('class_group_id', $this->classGroupId)->where('user_id', Auth::user()->id)->delete();
        session()->flash('message', 'Successfully leave class!');
    }

    
}
