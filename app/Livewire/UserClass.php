<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassGroup;
use App\Models\ClassJoin;
use Livewire\WithPagination;

class UserClass extends Component
{
    use WithPagination;
    public $searchClass = "";

    public function render()
    {
        $classUser = ClassJoin::join('class_groups', 'class_joins.class_group_id', '=', 'class_groups.id')
            ->join('users', 'class_groups.user_id', '=', 'users.id')
            ->where('class_joins.user_id', Auth::id())
            ->select('class_groups.*', 'users.name')
            ->when($this->searchClass, function ($query) {
                $search = '%' . $this->searchClass . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('users.name', 'like', $search)
                        ->orWhere('class_groups.class_name', 'like', $search)
                        ->orWhere('class_groups.class_category', 'like', $search);
                });
            })
            ->paginate(10);

        return view('livewire.user-class', [
            'classUser' => $classUser
        ]);
    }

    public function leaveClass($classId)
    {
        $classJoin = ClassJoin::where('user_id', Auth::id())
            ->where('class_group_id', $classId)
            ->first();

        if ($classJoin) {
            $classJoin->delete();
            session()->flash('message', 'You have left the class successfully.');
        } else {
            session()->flash('error', 'You are not a member of this class.');
        }
    }
}