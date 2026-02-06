<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'hardware_id',
        'computer_name',
        'user_id',
        'last_seen'
    ];
}
