<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'hardware_id',
        'computer_name',
        'agent_version',
        'user_id',
        'last_seen'
    ];

    protected $casts = [
        'last_seen' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
