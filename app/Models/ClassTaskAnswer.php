<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class ClassTaskAnswer extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan HasUuids dan SoftDeletes

    // Menentukan nama tabel jika tidak mengikuti konvensi Laravel
    protected $table = 'class_task_answers';

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'task_id',
        'user_id',
        'answer_text',
    ];

    // Relasi: Sebuah ClassTaskAnswer dimiliki oleh satu ClassTask
    public function task()
    {
        return $this->belongsTo(ClassTask::class, 'task_id');
    }

    // Relasi: Sebuah ClassTaskAnswer dimiliki oleh satu User (pembuat jawaban)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Sebuah ClassTaskAnswer memiliki banyak ClassTaskAnswerMedia
    public function media()
    {
        return $this->hasMany(ClassTaskAnswerMedia::class, 'answer_id');
    }
}
