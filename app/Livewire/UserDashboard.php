<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ClassGroup;
use App\Models\ClassTask;
use App\Models\ClassTaskAnswer;
use App\Models\ClassTaskPoint;

class UserDashboard extends Component
{
    public $userId;

    public function mount()
    {
        $this->userId = Auth::id();
    }

    public function render()
    {
        $data = [
            'NearestTaskDeadline' => ClassTask::join('class_groups', 'class_tasks.class_group_id', '=', 'class_groups.id')
                ->where('class_groups.user_id', $this->userId)
                ->where('class_tasks.task_deadline', '>=', now())
                ->orderBy('class_tasks.task_deadline', 'asc')
                ->get(),
            'AvgScoreClass' => ClassTask::join('class_groups', 'class_tasks.class_group_id', '=', 'class_groups.id')
                ->where('class_groups.user_id', $this->userId)->get(),
            'TaskAnswered' => ClassTaskAnswer::where('user_id',  $this->userId)->count(),
                
        ];
        return view('livewire.user-dashboard', $data);
    }

    public function getAvgScoreClass($classID)
    {
        $avgScore = ClassTask::join('class_groups', 'class_tasks.class_group_id', '=', 'class_groups.id')
            ->join('class_task_answers', 'class_tasks.id', '=', 'class_task_answers.task_id')
            ->join('class_task_points', 'class_task_answers.id', '=', 'class_task_points.answer_id')
            ->where('class_groups.user_id', $this->userId)
            ->where('class_tasks.class_group_id', $classID)
            ->avg('class_task_points.point');
        return $avgScore;
    }
}
