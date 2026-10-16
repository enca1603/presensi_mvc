<?php

namespace App\Models;

use App\Core\Model;

class Teacher extends Model
{
    protected static string $table = 'teachers';
    protected static array $fillable = [
        'nip', 'nuptk', 'full_name', 'gender', 'birth_place', 'birth_date', 'address',
        'phone', 'email', 'position', 'subject', 'photo', 'status',
    ];
}
