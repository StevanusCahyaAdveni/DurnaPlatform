<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionMedia;
use App\Models\ExamQuestionOption;
use App\Models\ExamQuestionOptionMedia;
use App\Models\ExamQuestionAnswer;
use Livewire\WithPagination;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeacherExamDetail extends Component
{
    use WithPagination, WithFileUploads;

    public $exam;
    public $layoutStatus = 'home';
    public $questionSearch = '';
    public $questionsLoaded = false;

    // Form properties
    public $question_text = '';
    public $question_type = '';
    public $point = 1;
    public $files = [];
    public $isEditMode = false;
    public $editingQuestionId = null;

    // Option management properties
    public $selectedQuestionId = null;
    public $option_text = '';
    public $is_correct = false;
    public $option_files = [];
    public $isEditingOption = false;
    public $editingOptionId = null;

    // Validation rules for questions
    protected function getQuestionRules()
    {
        return [
            'question_text' => 'required|string|min:5|max:1000',
            'question_type' => 'required|in:multiple_choice,short_answer',
            'point' => 'required|integer|min:1|max:100',
            'files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // 2MB max per file
        ];
    }

    // Validation rules for options
    protected function getOptionRules()
    {
        return [
            'option_text' => 'required|string|min:1|max:500',
            'is_correct' => 'required|boolean',
            'option_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ];
    }

    protected $messages = [
        'question_text.required' => 'Question text is required.',
        'question_text.min' => 'Question must be at least 5 characters.',
        'question_text.max' => 'Question cannot exceed 1000 characters.',
        'question_type.required' => 'Please select a question type.',
        'question_type.in' => 'Invalid question type selected.',
        'point.required' => 'Point is required.',
        'point.integer' => 'Point must be a number.',
        'point.min' => 'Point must be at least 1.',
        'point.max' => 'Point cannot exceed 100.',
        'files.*.image' => 'Only image files are allowed.',
        'files.*.mimes' => 'Supported formats: JPEG, PNG, JPG, GIF, WEBP.',
        'files.*.max' => 'Each file cannot exceed 2MB.'
    ];

    public function mount($id)
    {
        // Ensure the user is authorized to view this exam
        $exam = Exam::join('class_groups', 'class_groups.id', '=', 'exams.classgroup_id')
            ->where('exams.id', $id)
            ->select('exams.*', 'class_groups.user_id')
            ->first();

        if (!$exam || $exam->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Load exam questions and related data
        $this->exam = $exam;
    }

    public function render()
    {
        $questions = null;

        if ($this->layoutStatus === 'questions' && $this->questionsLoaded) {
            $questions = ExamQuestion::where('exam_id', $this->exam->id)
                ->with(['media', 'options.media'])
                ->when($this->questionSearch, function ($query) {
                    $query->where('question_text', 'like', '%' . $this->questionSearch . '%');
                })
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('livewire.teacher-exam-detail', compact('questions'));
    }

    public function changeLayoutStatus($status)
    {
        $this->layoutStatus = $status;
        $this->resetForm();
        
        // Load questions data when switching to questions tab
        if ($status === 'questions' && !$this->questionsLoaded) {
            $this->loadQuestions();
        }
    }

    public function loadQuestions()
    {
        $this->questionsLoaded = true;
    }

    public function upDataForUpdate($id = null)
    {
        if ($id) {
            $this->isEditMode = true;
            $this->editingQuestionId = $id;

            $question = ExamQuestion::findOrFail($id);
            $this->question_text = $question->question_text;
            $this->question_type = $question->question_type;
            $this->point = $question->point;
        } else {
            $this->resetForm();
        }
    }

    public function saveQuestion()
    {
        $this->validate($this->getQuestionRules());

        try {
            // Debug file uploads
            if (!empty($this->files)) {
                Log::info('Files received for upload:', [
                    'count' => count($this->files),
                    'types' => array_map(function ($file) {
                        return $file ? get_class($file) : 'null';
                    }, $this->files)
                ]);
            }

            // Create or update question
            if ($this->isEditMode && $this->editingQuestionId) {
                $question = ExamQuestion::findOrFail($this->editingQuestionId);
                $question->update([
                    'question_text' => $this->question_text,
                    'question_type' => $this->question_type,
                    'point' => $this->point,
                ]);

                $message = 'Question updated successfully!';
            } else {
                $question = ExamQuestion::create([
                    'exam_id' => $this->exam->id,
                    'question_text' => $this->question_text,
                    'question_type' => $this->question_type,
                    'point' => $this->point,
                ]);

                $message = 'Question created successfully!';
            }

            // Handle file uploads
            if (!empty($this->files) && is_array($this->files)) {
                $uploadCount = $this->handleFileUploads($question);
                if ($uploadCount > 0) {
                    $message .= " {$uploadCount} file(s) uploaded.";
                }
            }            // Reset form and show success message
            $this->resetForm();
            $this->dispatch('close-modal', name: 'addQuestion');
            session()->flash('success', $message);
            
            // Refresh questions list
            $this->questionsLoaded = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save question: ' . $e->getMessage());
        }
    }

    private function handleFileUploads($question)
    {
        $uploadCount = 0;
        $uploadPath = 'examQuestion';

        // Create examQuestion directory if it doesn't exist
        if (!Storage::disk('public')->exists($uploadPath)) {
            Storage::disk('public')->makeDirectory($uploadPath);
        }

        foreach ($this->files as $file) {
            if ($file && is_object($file) && method_exists($file, 'isValid') && $file->isValid()) {
                try {
                    // Generate unique filename
                    $extension = $file->getClientOriginalExtension();
                    $filename = Str::uuid() . '.' . $extension;

                    // Store file in public/storage/examQuestion
                    $filePath = $file->storeAs($uploadPath, $filename, 'public');

                    if ($filePath) {
                        // Save to database
                        ExamQuestionMedia::create([
                            'question_id' => $question->id,
                            'media_name' => $filename,
                        ]);
                        $uploadCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('File upload error: ' . $e->getMessage());
                    session()->flash('error', 'Failed to upload file: ' . ($file->getClientOriginalName() ?? 'Unknown file'));
                }
            }
        }

        return $uploadCount;
    }

    public function deleteQuestion($questionId)
    {
        try {
            $question = ExamQuestion::findOrFail($questionId);

            // Delete associated media files
            foreach ($question->media as $media) {
                $filePath = 'examQuestion/' . $media->media_name;
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $media->delete();
            }

            // Delete question
            $question->delete();

            session()->flash('success', 'Question deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete question: ' . $e->getMessage());
        }
    }

    public function deleteMedia($mediaId)
    {
        try {
            $media = ExamQuestionMedia::findOrFail($mediaId);

            // Delete file from storage
            $filePath = 'examQuestion/' . $media->media_name;
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // Delete from database
            $media->delete();

            session()->flash('success', 'Media deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete media: ' . $e->getMessage());
        }
    }

    private function resetForm()
    {
        $this->question_text = '';
        $this->question_type = '';
        $this->point = 1;
        $this->files = [];
        $this->isEditMode = false;
        $this->editingQuestionId = null;
        $this->resetValidation(['question_text', 'question_type', 'point', 'files.*']);
    }

    // Method to debug file uploads
    public function updatedFiles()
    {
        $this->validate([
            'files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);
    }

    // Method to validate option files
    public function updatedOptionFiles()
    {
        $this->validate([
            'option_files.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ]);
    }

    // Option management methods
    public function openOptionModal($questionId)
    {
        $this->selectedQuestionId = $questionId;
        $this->resetOptionForm();
    }

    public function saveOption()
    {
        $this->validate($this->getOptionRules());

        try {
            if ($this->isEditingOption && $this->editingOptionId) {
                // Update existing option
                $option = ExamQuestionOption::findOrFail($this->editingOptionId);
                $option->update([
                    'option_text' => $this->option_text,
                    'is_correct' => $this->is_correct,
                ]);
                $message = 'Option updated successfully!';
            } else {
                // Create new option
                $option = ExamQuestionOption::create([
                    'question_id' => $this->selectedQuestionId,
                    'option_text' => $this->option_text,
                    'is_correct' => $this->is_correct,
                ]);
                $message = 'Option created successfully!';
            }

            // Handle option file uploads
            if (!empty($this->option_files) && is_array($this->option_files)) {
                $uploadCount = $this->handleOptionFileUploads($option);
                if ($uploadCount > 0) {
                    $message .= " {$uploadCount} file(s) uploaded.";
                }
            }

            $this->resetOptionForm();
            $this->dispatch('close-modal', name: 'manageOptions');
            session()->flash('success', $message);
            
            // Refresh questions list to show updated options
            $this->questionsLoaded = true;
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save option: ' . $e->getMessage());
        }
    }

    public function editOption($optionId)
    {
        $option = ExamQuestionOption::findOrFail($optionId);
        $this->selectedQuestionId = $option->question_id;
        $this->editingOptionId = $optionId;
        $this->isEditingOption = true;
        $this->option_text = $option->option_text;
        $this->is_correct = $option->is_correct;
        $this->option_files = [];
    }

    public function deleteOption($optionId)
    {
        try {
            $option = ExamQuestionOption::findOrFail($optionId);

            // Delete associated media files
            foreach ($option->media as $media) {
                $filePath = 'examQuestion/' . $media->media_name;
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
                $media->delete();
            }

            $option->delete();
            session()->flash('success', 'Option deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete option: ' . $e->getMessage());
        }
    }

    public function deleteOptionMedia($mediaId)
    {
        try {
            $media = ExamQuestionOptionMedia::findOrFail($mediaId);

            // Delete file from storage
            $filePath = 'examQuestion/' . $media->media_name;
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $media->delete();
            session()->flash('success', 'Option media deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete option media: ' . $e->getMessage());
        }
    }

    private function handleOptionFileUploads($option)
    {
        $uploadCount = 0;
        $uploadPath = 'examQuestion';

        foreach ($this->option_files as $file) {
            if ($file && is_object($file) && method_exists($file, 'isValid') && $file->isValid()) {
                try {
                    $extension = $file->getClientOriginalExtension();
                    $filename = Str::uuid() . '.' . $extension;
                    $filePath = $file->storeAs($uploadPath, $filename, 'public');

                    if ($filePath) {
                        ExamQuestionOptionMedia::create([
                            'option_id' => $option->id,
                            'media_name' => $filename,
                        ]);
                        $uploadCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Option file upload error: ' . $e->getMessage());
                    session()->flash('error', 'Failed to upload option file: ' . ($file->getClientOriginalName() ?? 'Unknown file'));
                }
            }
        }

        return $uploadCount;
    }

    private function resetOptionForm()
    {
        $this->option_text = '';
        $this->is_correct = false;
        $this->option_files = [];
        $this->isEditingOption = false;
        $this->editingOptionId = null;
        $this->resetValidation(['option_text', 'is_correct', 'option_files.*']);
    }
}
