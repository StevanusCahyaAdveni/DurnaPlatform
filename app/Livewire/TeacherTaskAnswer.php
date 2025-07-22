<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassTaskAnswer;
use App\Models\ClassTaskAnswerMedia;
use App\Models\ClassTask;
use App\Models\ClassTaskAnswerComment;
use App\Models\ClassTaskPoint;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class TeacherTaskAnswer extends Component
{
    use WithFileUploads;

    public $taskId;
    public $taskAnswersId;
    public $formStatus = '0';
    public $answerText = '';
    public $answerMedia;
    public $answerName = '';

    public $commentText = '';
    public $commentMedia;
    public $point = '';

    public function mount($id)
    {
        $this->taskId = $id; // Set the task ID from the route parameter
    }

    public function render()
    {
        $data = [
            'singleTask' => ClassTask::join('class_groups', 'class_groups.id', '=', 'class_tasks.class_group_id')->select('class_tasks.*', 'class_groups.class_name as group_name')->where('class_tasks.id', $this->taskId)->first(),
            'taskAnswers' => ClassTaskAnswer::join('users', 'users.id', '=', 'class_task_answers.user_id')->select('class_task_answers.*', 'users.name as student_name')->where('task_id', $this->taskId)->get(),
            'taskAnswersComments' => ClassTaskAnswerComment::join('users', 'users.id', '=', 'class_task_answer_comments.user_id')->select('class_task_answer_comments.*', 'users.name as commenter_name')->where('answer_id', $this->taskAnswersId)->orderBy('class_task_answer_comments.created_at', 'desc')->get(),
            'singleAnswerPoint' => ClassTaskPoint::join('users', 'users.id', '=', 'class_task_points.user_id')->select('class_task_points.*', 'users.name as point_maker_name')->where('answer_id', $this->taskAnswersId)->first(),
        ];
        return view('livewire.teacher-task-answer', $data);
    }

    public function changeForm($form, $answerId = null)
    {
        $this->formStatus = $form;
        $this->taskAnswersId = $answerId; // Update taskAnswersId if needed
        if ($answerId != null) {
            $taskAnswer = ClassTaskAnswer::join('users', 'users.id', '=', 'class_task_answers.user_id')->select('class_task_answers.*', 'users.name as student_name')->where('class_task_answers.id', $answerId)->first();
            if ($taskAnswer) {
                $this->answerName = $taskAnswer->student_name;
                $this->answerText = $taskAnswer->answer_text;
                $this->answerMedia = ClassTaskAnswerMedia::where('answer_id', $answerId)->get();
            } else {
                $this->reset(['answerText', 'answerMedia']);
            }
        } else {
            $this->reset(['answerText']);
        }
    }

    public function addComent()
    {
        // $this->validate([
        //     'answerText' => 'required|string|max:1000',
        // ]);

        if (!empty($this->commentMedia)) {
            $path = $this->commentMedia->store('task_media', 'public');
        }else {
            $path = ''; // Handle case where no media is uploaded
        }

        ClassTaskAnswerComment::create(
            [
                'answer_id' => $this->taskAnswersId,
                'user_id' => Auth::id(),
                'comment_text' => $this->commentText,
                'comment_media' => basename($path),
            ]
        );

        $this->reset(['commentText', 'commentMedia']);
        // Logic to save the comment can be added here
        // For example, you can create a new ClassTaskAnswer record
        
    }

    public function upDataPoint($point)
    {
        $this->point = $point;
    }

    public function setPoint()
    {
        $this->validate([
            'point' => 'required|string|max:50',
        ]);

        $getAnswerPoint = ClassTaskPoint::where('task_id', $this->taskId)
            ->where('answer_id', $this->taskAnswersId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$getAnswerPoint) {
            ClassTaskPoint::create(
                [
                    'task_id' => $this->taskId,
                    'answer_id' => $this->taskAnswersId,
                    'user_id' => Auth::id(),
                    'point' => $this->point,
                ]
            );
        }else{
            $getAnswerPoint->update(['point' => $this->point]);
        }

        $this->reset(['point']);
        // Logic to save the point can be added here
    }
}
