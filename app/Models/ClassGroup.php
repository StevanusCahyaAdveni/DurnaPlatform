<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class ClassGroup extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan HasUuids dan SoftDeletes

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'class_name',
        'class_code',
        'class_description',
        'class_category',
        'user_id',
    ];

    // Relasi: Sebuah ClassGroup dimiliki oleh satu User (pembuatnya)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Sebuah ClassGroup memiliki banyak ClassJoin
    public function joins()
    {
        return $this->hasMany(ClassJoin::class);
    }

    // Relasi: Sebuah ClassGroup memiliki banyak ClassTask
    public function tasks()
    {
        return $this->hasMany(ClassTask::class);
    }
}
