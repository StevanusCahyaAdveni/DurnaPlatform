<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class ClassTaskAnswerMedia extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan HasUuids dan SoftDeletes

    // Menentukan nama tabel jika tidak mengikuti konvensi Laravel
    protected $table = 'class_task_answer_media';

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'answer_id',
        'media_name',
    ];

    // Relasi: Sebuah ClassTaskAnswerMedia dimiliki oleh satu ClassTaskAnswer
    public function answer()
    {
        return $this->belongsTo(ClassTaskAnswer::class, 'answer_id');
    }
}
