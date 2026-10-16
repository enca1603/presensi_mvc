<?php

namespace App\Models;

use App\Core\Model;

class Student extends Model
{
    protected static string $table = 'students';
    protected static array $fillable = [
        'nis', 'nisn', 'full_name', 'nickname', 'gender', 'birth_place', 'birth_date',
        'address', 'phone', 'email', 'photo', 'class_id', 'major_id', 'academic_year_id',
        'guardian_id', 'entry_year', 'status',
    ];
}
