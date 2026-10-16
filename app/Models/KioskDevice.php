<?php

namespace App\Models;

use App\Core\Model;

class KioskDevice extends Model
{
    protected static string $table = 'kiosk_devices';
    protected static array $fillable = ['name', 'device_code', 'location', 'pin_hash', 'status', 'last_seen_at'];
}
