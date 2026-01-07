<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


// Landing Page (Public)
Route::get('/', function () {
    return view('welcome');
})->name('home');


// Authentication Routes (Guest only - redirect if logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('user.dashboard');
    })->name('dashboard');

    // Admin Specific Routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/admin/dashboard', [App\Http\Controllers\DashboardController::class, 'adminDashboard'])->name('admin.dashboard');

        // Worker Management
        Route::get('admin/admins', [App\Http\Controllers\WorkerController::class, 'admins'])->name('admin.workers.admins');
        Route::get('admin/pending-users', [App\Http\Controllers\WorkerController::class, 'pending'])->name('admin.workers.pending');
        Route::get('admin/suspended-users', [App\Http\Controllers\WorkerController::class, 'suspended'])->name('admin.workers.suspended');
        Route::get('admin/rejected-users', [App\Http\Controllers\WorkerController::class, 'rejected'])->name('admin.workers.rejected');
        Route::get('admin/users/{id}/approve', [App\Http\Controllers\WorkerController::class, 'approve'])->name('admin.workers.approve');
        Route::match(['get', 'post'], 'admin/users/{id}/reject', [App\Http\Controllers\WorkerController::class, 'reject'])->name('admin.workers.reject');
        Route::post('admin/users/{id}/suspend', [App\Http\Controllers\WorkerController::class, 'suspend'])->name('admin.workers.suspend');
        Route::post('admin/users/{id}/activate', [App\Http\Controllers\WorkerController::class, 'activate'])->name('admin.workers.activate');
        Route::post('admin/workers/bulk-action', [App\Http\Controllers\WorkerController::class, 'bulkAction'])->name('admin.workers.bulk_action');
        Route::resource('admin/workers', App\Http\Controllers\WorkerController::class, ['as' => 'admin']);

        // Token Management (Admin Index)
        Route::get('/admin/tokens', [App\Http\Controllers\TokenController::class, 'index'])->name('admin.tokens.index');

        // Chat Routes (Admin)
        Route::get('/admin/chat', [App\Http\Controllers\ChatController::class, 'indexAdmin'])->name('admin.chat');

        // Settings Routes
        Route::get('/admin/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('admin.settings.index');
        Route::post('/admin/settings/logo', [App\Http\Controllers\SettingsController::class, 'updateLogo'])->name('admin.settings.update_logo');
        Route::delete('/admin/settings/logo', [App\Http\Controllers\SettingsController::class, 'removeLogo'])->name('admin.settings.remove_logo');
        Route::post('/admin/settings/favicon', [App\Http\Controllers\SettingsController::class, 'updateFavicon'])->name('admin.settings.update_favicon');
        Route::delete('/admin/settings/favicon', [App\Http\Controllers\SettingsController::class, 'removeFavicon'])->name('admin.settings.remove_favicon');
        Route::post('/admin/settings/cache/clear', [App\Http\Controllers\SettingsController::class, 'clearCache'])->name('admin.settings.clear_cache');
        Route::post('/admin/settings/telegram', [App\Http\Controllers\SettingsController::class, 'updateTelegram'])->name('admin.settings.update_telegram');
        Route::post('/admin/settings/sheets', [App\Http\Controllers\SettingsController::class, 'updateSheetVisibility'])->name('admin.settings.update_sheets');

        // Google Sheets CRUD Routes
        Route::post('/admin/settings/sheets/store', [App\Http\Controllers\SettingsController::class, 'storeSheet'])->name('admin.settings.sheets.store');
        Route::put('/admin/settings/sheets/{sheet}', [App\Http\Controllers\SettingsController::class, 'updateSheet'])->name('admin.settings.sheets.update');
        Route::delete('/admin/settings/sheets/{sheet}', [App\Http\Controllers\SettingsController::class, 'deleteSheet'])->name('admin.settings.sheets.delete');
        Route::post('/admin/settings/sheets/{sheet}/toggle', [App\Http\Controllers\SettingsController::class, 'toggleSheetVisibility'])->name('admin.settings.sheets.toggle');

        // Telegram Test Route
        Route::get('/admin/telegram/test', function () {
            $telegram = app(\App\Services\TelegramService::class);
            $result = $telegram->testConnection();
            return $result ? 'Telegram connected successfully!' : 'Telegram connection failed. Check logs.';
        });

        // Notification Routes (Admin)
        Route::get('/admin/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::get('/admin/notifications/create', [App\Http\Controllers\NotificationController::class, 'create'])->name('admin.notifications.create');
        Route::post('/admin/notifications', [App\Http\Controllers\NotificationController::class, 'store'])->name('admin.notifications.store');
        Route::delete('/admin/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('admin.notifications.destroy');
    });

    // User Specific Routes
    Route::middleware(['user'])->group(function () {
        Route::get('/user/dashboard', [App\Http\Controllers\DashboardController::class, 'userDashboard'])->name('user.dashboard');
        Route::get('/tokens/my', [App\Http\Controllers\TokenController::class, 'myTokens'])->name('tokens.my');
        Route::get('/tokens/create', [App\Http\Controllers\TokenController::class, 'create'])->name('tokens.create');
        Route::post('/tokens', [App\Http\Controllers\TokenController::class, 'store'])->name('tokens.store');
        Route::get('/tokens', function () {
            return auth()->user()->role === 'admin' ? redirect()->route('admin.tokens.index') : redirect()->route('tokens.my');
        });
        Route::delete('/tokens/{token}', [App\Http\Controllers\TokenController::class, 'destroy'])->name('tokens.destroy');

        Route::get('/user/chat', [App\Http\Controllers\ChatController::class, 'indexUser'])->name('user.chat');
        Route::get('/user/notifications', [App\Http\Controllers\NotificationController::class, 'userIndex'])->name('user.notifications.index');
    });

    // Common Accessible Routes (Logic handled in controllers)
    Route::get('/chat/contacts', [App\Http\Controllers\ChatController::class, 'fetchContacts'])->name('chat.contacts');
    Route::get('/chat/messages/{contactId}/{contactType}', [App\Http\Controllers\ChatController::class, 'fetchMessages'])->name('chat.messages');
    Route::get('/chat/unread-count', [App\Http\Controllers\ChatController::class, 'getUnreadCount'])->name('chat.unread_count');
    Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');

    Route::post('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');


    // Profile Routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Duplicate Checker Route
    Route::get('/duplicate-checker', [App\Http\Controllers\DuplicateCheckerController::class, 'index'])->name('duplicate.checker');

    // Sheets Route
    Route::get('/sheets', [App\Http\Controllers\SheetsController::class, 'index'])->name('sheets.index');
    Route::get('/sheets/{type}', [App\Http\Controllers\SheetsController::class, 'show'])->name('sheets.show');
});
