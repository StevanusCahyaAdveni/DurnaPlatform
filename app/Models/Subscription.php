<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'tipe',
        'class_uuid',
        'course_uuid',
        'user_id',
        'nominal',
        'payment_method',
        'expired_at',
    ];

    protected $casts = [
        'class_uuid' => 'string',
        'course_uuid' => 'string',
        'user_id' => 'string',
        'expired_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function subscriber()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_uuid');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_uuid');
    }
}
