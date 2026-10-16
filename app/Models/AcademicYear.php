<?php

namespace App\Models;

use App\Core\Model;

class AcademicYear extends Model
{
    protected static string $table = 'academic_years';
    protected static array $fillable = ['name', 'start_date', 'end_date', 'is_active'];
}
