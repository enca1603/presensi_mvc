<?php

namespace App\Models;

use App\Core\Model;

class Guardian extends Model
{
    protected static string $table = 'guardians';
    protected static array $fillable = ['name', 'relationship', 'phone', 'email', 'address', 'status'];
}
