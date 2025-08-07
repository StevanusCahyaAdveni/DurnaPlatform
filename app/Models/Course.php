<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'course_name',
        'course_code',
        'course_description',
        'course_categori',
        'user_id',
        'price',
        'course_thumbnail',
    ];

    protected $casts = [
        'user_id' => 'string',
        'deleted_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function videos()
    {
        return $this->hasMany(CourseVideo::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'course_joins', 'course_id', 'user_id');
    }
}
