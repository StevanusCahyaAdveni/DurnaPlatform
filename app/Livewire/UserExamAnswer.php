<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamQuestionAnswer;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionMedia;
use App\Models\ExamQuestionOption;
use App\Models\ExamQuestionOptionMedia;
use Illuminate\Support\Facades\Auth;

class UserExamAnswer extends Component
{
    public $singleDataExam;
    public $time;
    public $timeEnd;
    public $layoutStatus;
    public $questions;
    public $singleQuestion;
    public $singleQuestionMedia;
    public $singleQuestionOptions;
    public $optionSelected;


    public function mount($id)
    {
        $this->time = 90;
        $this->singleDataExam = Exam::join('class_groups', 'class_groups.id', '=', 'exams.classgroup_id')
            ->where('exams.id', $id)
            ->select('exams.*', 'class_groups.class_name as class_name')
            ->first();

        $this->questions = ExamQuestion::where('exam_id', $this->singleDataExam->id)
            ->with(['media', 'options.media'])
            ->inRandomOrder()
            ->get();

        $this->singleQuestion = $this->questions[0];
        $this->singleQuestionMedia = ExamQuestionMedia::where('question_id', $this->singleQuestion->id)->get();
        $this->singleQuestionOptions = ExamQuestionOption::where('question_id', $this->singleQuestion->id)->get();
    }

    public function render()
    {
        $this->layoutStatus = 'Preview';
        $examAnswer = ExamAnswer::where('user_id', Auth::id())->where('exam_id', $this->singleDataExam->id)->first();
        if ($examAnswer) {
            $this->layoutStatus = 'Question';
            $this->timeEnd = date('Y-m-d H:i:s', strtotime($examAnswer->created_at . " +{$this->time} minutes"));
        }

        $this->optionSelected = ExamQuestionAnswer::where('exam_answer_id', $examAnswer->id)->where('question_id', $this->singleQuestion->id)->where('user_id', Auth::id())->first();


        return view('livewire.user-exam-answer');
    }

    public function changeLayoutStatus($value)
    {
        $this->layoutStatus = $value;

        if ($value == 'Question') {
            ExamAnswer::create([
                'user_id' => Auth::id(),
                'exam_id' => $this->singleDataExam->id,
                'point' => '-'
            ]);
        }
    }

    public function setQuestion($id)
    {
        $this->singleQuestion = ExamQuestion::where('id', $id)->first();
        $this->singleQuestionMedia = ExamQuestionMedia::where('question_id', $id)->get();
        $this->singleQuestionOptions = ExamQuestionOption::where('question_id', $id)->get();

        $examAnswer = ExamAnswer::where('user_id', Auth::id())->where('exam_id', $this->singleDataExam->id)->first();
        $this->optionSelected = ExamQuestionAnswer::where('exam_answer_id', $examAnswer->id)->where('question_id', $this->singleQuestion->id)->where('user_id', Auth::id())->first();
    }

    public function setSelectedOption($id)
    {
        $examAnswer = ExamAnswer::where('user_id', Auth::id())->where('exam_id', $this->singleDataExam->id)->first();
        $question = ExamQuestionAnswer::where('exam_answer_id', $examAnswer->id)->where('question_id', $this->singleQuestion->id)->where('user_id', Auth::id())->first();

        if(empty($question)){
            ExamQuestionAnswer::create([
                'exam_answer_id' => $examAnswer->id,
                'question_id' => $this->singleQuestion->id,
                'user_id' => Auth::id(),
                'answer_text' => $id
            ]);
        }else{
            ExamQuestionAnswer::where('id', $question->id)->update([
                'answer_text' => $id
            ]);
        }

    }
}
