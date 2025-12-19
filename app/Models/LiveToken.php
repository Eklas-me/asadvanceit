<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiveToken extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'user_name',
        'live_token',
        'user_type',
        'status',
        'insert_time'
    ];

    public $timestamps = true;

    protected $casts = [
        'insert_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
