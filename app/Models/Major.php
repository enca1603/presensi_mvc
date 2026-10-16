<?php

namespace App\Models;

use App\Core\Model;

class Major extends Model
{
    protected static string $table = 'majors';
    protected static array $fillable = ['name', 'code', 'status'];
}
