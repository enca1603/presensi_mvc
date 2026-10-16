<?php

namespace App\Models;

use App\Core\Model;

class ClassRoom extends Model
{
    protected static string $table = 'classes';
    protected static array $fillable = [
        'name', 'grade', 'major_id', 'academic_year_id', 'homeroom_teacher_id', 'status',
    ];
}
