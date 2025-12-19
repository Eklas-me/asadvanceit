<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'profile_photo',
        'shift',
        'status',
        'is_core_admin',
        'gender',
        'old_id',
        'needs_password_upgrade',
        'last_seen',
        'verification_code',
        'verification_code_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // Removed 'password' => 'hashed' to allow MD5UserProvider to handle validation
            'is_core_admin' => 'boolean',
            'last_seen' => 'datetime',
            'verification_code_expires_at' => 'datetime',
        ];
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCoreAdmin()
    {
        return $this->is_core_admin === true;
    }

    public function isOnline()
    {
        return $this->last_seen && $this->last_seen->gt(now()->subMinutes(5));
    }

    public function liveTokens()
    {
        return $this->hasMany(LiveToken::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Set the password attribute - store as-is without automatic hashing
     * Our MD5UserProvider handles validation for both MD5 and bcrypt
     */
    public function setPasswordAttribute($value)
    {
        // Store password as-is, don't auto-hash
        // This allows us to store both MD5 and bcrypt passwords
        $this->attributes['password'] = $value;
    }
}
