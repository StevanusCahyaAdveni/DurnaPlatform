<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class ClassTaskAnswerComment extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan HasUuids dan SoftDeletes

    // Menentukan nama tabel jika tidak mengikuti konvensi Laravel
    protected $table = 'class_task_answer_comments';

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'answer_id',
        'user_id',
        'comment_media',
        'comment_text',
    ];

    // Relasi: Sebuah ClassTaskAnswerComment dimiliki oleh satu ClassTaskAnswer
    public function answer()
    {
        return $this->belongsTo(ClassTaskAnswer::class, 'answer_id');
    }

    // Relasi: Sebuah ClassTaskAnswerComment dimiliki oleh satu User (pembuat komentar)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
