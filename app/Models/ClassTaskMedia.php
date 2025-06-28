<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class ClassTaskMedia extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan HasUuids dan SoftDeletes

    // Menentukan nama tabel jika tidak mengikuti konvensi Laravel
    protected $table = 'class_task_media';

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'class_task_id', // Sesuaikan nama kolom foreign key
        'media_name',
    ];

    // Relasi: Sebuah ClassTaskMedia dimiliki oleh satu ClassTask
    public function classTask()
    {
        return $this->belongsTo(ClassTask::class, 'class_task_id'); // Pastikan foreign key benar
    }
}
