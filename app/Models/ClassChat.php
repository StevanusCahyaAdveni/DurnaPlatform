<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes

class ClassChat extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan HasUuids dan SoftDeletes

    // Menentukan nama tabel jika tidak mengikuti konvensi Laravel
    protected $table = 'class_chats';

    // Menentukan kolom-kolom yang dapat diisi secara massal
    protected $fillable = [
        'class_group_id',
        'user_id',
        'class_chat_id', // Untuk komentar atau balasan chat
        'chat_media',
        'chat_text',
    ];

    // Relasi: Sebuah ClassChat dimiliki oleh satu ClassGroup
    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }

    // Relasi: Sebuah ClassChat dimiliki oleh satu User (pembuat chat)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi: Sebuah ClassChat bisa memiliki parent chat (untuk balasan/komentar)
    public function parentChat()
    {
        return $this->belongsTo(ClassChat::class, 'class_chat_id');
    }

    // Relasi: Sebuah ClassChat bisa memiliki banyak child chat (balasan/komentar)
    public function replies()
    {
        return $this->hasMany(ClassChat::class, 'class_chat_id');
    }
}
