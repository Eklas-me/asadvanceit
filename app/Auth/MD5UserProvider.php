<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;

class MD5UserProvider extends EloquentUserProvider
{
    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];
        $hashedPassword = $user->getAuthPassword();

        // Check if user needs password upgrade (MD5)
        // Note: accessed via __get magic method for Eloquent models
        if ($user->needs_password_upgrade) {
            // Validate against MD5
            $md5Hash = md5($plain);
            // Use hash_equals to prevent timing attacks
            return hash_equals($md5Hash, $hashedPassword);
        }

        // Standard bcrypt validation - use password_verify directly to bypass 
        // BcryptHasher's strict check which throws exceptions on non-bcrypt hashes
        return password_verify($plain, $hashedPassword);
    }

    /**
     * Override to prevent needsRehash check on MD5 passwords and match parent signature
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @param  bool  $force
     * @return void
     */
    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
    {
        // Skip rehashing for MD5 passwords, our middleware handles the upgrade and flag clearing
        if ($user->needs_password_upgrade) {
            return;
        }

        // Call parent for standard behavior
        parent::rehashPasswordIfRequired($user, $credentials, $force);
    }
}
