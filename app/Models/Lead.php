<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'user_id',
        'task_date',
        'account_email',
        'password',
        'tinder_username',
        'token',
        'numbers',
        'lat_long',
        'comments',
        'recovery',
        'admin_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
