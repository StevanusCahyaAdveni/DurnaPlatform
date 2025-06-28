<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class ClassTask extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan HasUuids dan SoftDeletes

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'task_name',
        'task_description',
        'task_deadline',
        'class_group_id',
    ];

    // Relasi: Sebuah ClassTask dimiliki oleh satu ClassGroup
    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    // Relasi: Sebuah ClassTask memiliki banyak ClassTaskMedia
    public function media()
    {
        return $this->hasMany(ClassTaskMedia::class);
    }
}
