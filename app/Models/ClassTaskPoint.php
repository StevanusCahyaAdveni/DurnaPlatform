<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids; // Import trait HasUuids
use Illuminate\Database\Eloquent\SoftDeletes; // Import trait SoftDeletes

class ClassTaskPoint extends Model
{
    use HasFactory, HasUuids, SoftDeletes; // Gunakan trait HasUuids dan SoftDeletes

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'class_task_points';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'answer_id',
        'user_id',
        'point',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'task_id' => 'string',
        'answer_id' => 'string',
        'user_id' => 'string',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the class task that owns the point.
     */
    public function task()
    {
        return $this->belongsTo(ClassTask::class, 'task_id');
    }

    /**
     * Get the class task answer that owns the point.
     */
    public function answer()
    {
        return $this->belongsTo(ClassTaskAnswer::class, 'answer_id');
    }

    /**
     * Get the user who made the point.
     */
    public function pointMaker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
