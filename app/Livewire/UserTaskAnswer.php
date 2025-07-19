<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ClassTask;
use App\Models\ClassTaskMedia;
use App\Models\ClassTaskAnswer;
use App\Models\ClassTaskAnswerMedia;
use Illuminate\Support\Facades\Auth;
use Livewire\WithFileUploads; // Import trait untuk upload file
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserTaskAnswer extends Component
{
    use WithFileUploads;
    public $taskId; // To store the task ID
    public $answer_text = ''; // To store the task answer
    public $task_media = []; // To store uploaded media files
    public $formStatus = '1'; // To track the current form step

    public function mount($id)
    {
        $this->taskId = $id; // Set the task ID from the route parameter
    }

    public function render()
    {
        $data = [
            'singleTask' => ClassTask::select('class_tasks.*', 'users.name', 'class_groups.class_name')->join('class_groups', 'class_tasks.class_group_id', '=', 'class_groups.id')->join('users', 'class_groups.user_id', '=', 'users.id')->where('class_tasks.id', $this->taskId)->first(),
            'getTaskMedia' => ClassTaskMedia::where('class_task_id', $this->taskId)->get(),
            'getAnswer' => ClassTaskAnswer::where('task_id', $this->taskId)->where('user_id', Auth::id())->first(), // Get the user's answer for the task
            'getAnswerMedia' => ClassTaskAnswerMedia::where('answer_id', optional(ClassTaskAnswer::where('task_id', $this->taskId)->where('user_id', Auth::id())->first())->id)->get(),
        ];

        return view('livewire.user-task-answer', $data);
    }

    public function resetForm()
    {
        $this->reset(['answer_text', 'task_media']);
    }

    public function changeForm($status)
    {
        $this->formStatus = $status; // Change the form status

        if ($status == '2') {
            $getSingleData = ClassTaskAnswer::where('task_id', $this->taskId)->where('user_id', Auth::id())->first(); // Get the user's answer for the task
            if ($getSingleData) {
                $this->answer_text = $getSingleData->answer_text;
            }
        }
        // $this->resetForm(); // Reset the form fields
    }

    public function submitTaskAnswer()
    {
        $getSingleData = ClassTaskAnswer::where('task_id', $this->taskId)->where('user_id', Auth::id())->first(); // Get the user's answer for the task
        if ($getSingleData) {
            $answer = $getSingleData->update([
                'answer_text' => $this->answer_text,
            ]);
            $answerId = $getSingleData->id; // Get the ID of the updated answer
        } else {
            $answer = ClassTaskAnswer::create([
                'task_id' => $this->taskId,
                'user_id' => Auth::user()->id, // Simpan HTML dari CKEditor
                'answer_text' => $this->answer_text,
            ]);
            $answerId = $answer->id; // Get the ID of the newly created answer
        }

        // Create media by answer id
        if (!empty($this->task_media)) {
            foreach ($this->task_media as $mediaFile) {
                $path = $mediaFile->store('task_media', 'public');
                ClassTaskAnswerMedia::create([
                    'answer_id' => $answerId,
                    'media_name' => basename($path),
                ]);
                // ClassTaskMedia::create([
                //     'class_task_id' => $task->id,
                //     'media_name' => basename($path),
                // ]);
            }
        }
        $this->resetForm(); // Reset form after submission
        $this->dispatch('taskAnswered'); // Emit an event to notify the parent component or page
        session()->flash('message', 'Successfully send answer!'); // Flash message

        $this->changeForm('1');
    }

    public function deleteAnswerMedia($id)
    {
        $getSingleData = ClassTaskAnswerMedia::find($id);
        if ($getSingleData) {
            Storage::disk('public')->delete('task_media/' . $getSingleData->media_name);
            $getSingleData->delete();
        }
    }
}
