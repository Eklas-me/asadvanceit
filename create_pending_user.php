<?php
use App\Models\User;
use Illuminate\Support\Facades\Hash;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $u = User::create([
        'name' => 'Pending Verification User',
        'email' => 'pending_verify_' . time() . '@example.com',
        'password' => Hash::make('123'),
        'role' => 'user',
        'status' => 'pending',
        'shift' => 'Day'
    ]);
    echo "Created Pending User ID: " . $u->id;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
