<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExamQuestion extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'exam_id',
        'question_text',
        'question_type',
        'point', // Tambahan field point
    ];

    protected $casts = [
        'point' => 'integer',
    ];

    // Relationship to exam
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    // Relationship to question options
    public function options()
    {
        return $this->hasMany(ExamQuestionOption::class, 'question_id');
    }

    // Relationship to question media
    public function media()
    {
        return $this->hasMany(ExamQuestionMedia::class, 'question_id');
    }

    // Relationship to question answers
    public function questionAnswers()
    {
        return $this->hasMany(ExamQuestionAnswer::class, 'question_id');
    }

    // Get correct option for this question
    public function correctOption()
    {
        return $this->hasOne(ExamQuestionOption::class, 'question_id')->where('is_correct', true);
    }

    // Accessor for formatted point display
    public function getFormattedPointAttribute()
    {
        return $this->point . ' point' . ($this->point > 1 ? 's' : '');
    }
}
