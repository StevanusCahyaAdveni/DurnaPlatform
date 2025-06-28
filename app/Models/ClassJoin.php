<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class ClassJoin extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan HasUuids dan SoftDeletes

    // Menentukan nama tabel jika tidak mengikuti konvensi Laravel (snake_case plural dari nama model)
    protected $table = 'class_joins';

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'class_group_id',
        'user_id',
    ];

    // Relasi: Sebuah ClassJoin dimiliki oleh satu ClassGroup
    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class);
    }

    // Relasi: Sebuah ClassJoin dimiliki oleh satu User (yang bergabung)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
