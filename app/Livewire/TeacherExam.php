<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Exam;
use App\Models\ClassGroup;
use Illuminate\Support\Facades\Auth;

class TeacherExam extends Component
{
    use WithPagination;

    public $classgroup_id;
    public $exam_name;
    public $exam_deadline;
    public $exam_description;
    public $formStatus = 'Add';
    public $exam_id;
    public $searchTerm = '';

    public function render()
    {
        $getExam = Exam::join('class_groups', 'class_groups.id', '=', 'exams.classgroup_id')->select('exams.*', 'class_groups.*', 'exams.id as exam_id')->where('class_groups.user_id', Auth::user()->id);
        if($this->searchTerm != '') {
            $getExam->where('exams.exam_name', 'like', '%'.$this->searchTerm.'%');
        }

        $data = [
            'getClass' => ClassGroup::where('user_id', Auth::id())->get(),
            'getExam' => $getExam->orderBy('exams.created_at','DESC')->paginate(10),
        ];
        return view('livewire.teacher-exam', $data);
    }

    public function addExam()
    {
        if($this->formStatus == 'Add') {
            Exam::create([
                'exam_name' => $this->exam_name,
                'exam_deadline' => $this->exam_deadline,
                'exam_description' => $this->exam_description,
                'classgroup_id' => $this->classgroup_id,
            ]);
            session()->flash('message', 'Exam created successfully.');
        }elseif($this->formStatus == 'Update'){
            $exam = Exam::find($this->exam_id);
            if ($exam) {
                $exam->update([
                    'exam_name' => $this->exam_name,
                    'exam_deadline' => $this->exam_deadline,
                    'exam_description' => $this->exam_description,
                    'classgroup_id' => $this->classgroup_id,
                ]);
                session()->flash('message', 'Exam updated successfully.');
            } else {
                session()->flash('error', 'Exam not found.');
            }
        }
        $this->formStatus = 'Add';
        $this->reset('classgroup_id', 'exam_name', 'exam_deadline', 'exam_description');
    }

    public function deleteExam($id)
    {
        $exam = Exam::find($id);
        if ($exam) {
            $exam->delete();
            session()->flash('message', 'Exam deleted successfully.');
        } else {
            session()->flash('error', 'Exam not found.');
        }
    }

    public function upDataForUpdate($id)
    {
        if($id != '') {
            $exam = Exam::find($id);
            if ($exam) {
                $this->classgroup_id = $exam->classgroup_id;
                $this->exam_name = $exam->exam_name;
                $this->exam_deadline = $exam->exam_deadline;
                $this->exam_description = $exam->exam_description;
                $this->exam_id = $exam->id;
                $this->formStatus = 'Update';
            } else {
                $this->formStatus = 'Add';
                $this->reset('classgroup_id', 'exam_name', 'exam_deadline', 'exam_description');
                session()->flash('error', 'Exam not found.');
            }
        }else {
            $this->formStatus = 'Add';
            $this->reset('classgroup_id', 'exam_name', 'exam_deadline', 'exam_description');
        }
    }
}
