<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $botToken;
    protected $chatIds = [];

    public function __construct()
    {
        // Try to get from database first, then fallback to config
        $this->botToken = \App\Models\SiteSetting::get('telegram_bot_token') ?? config('telegram.bot_token');

        $rawChatIds = \App\Models\SiteSetting::get('telegram_admin_chat_id') ?? config('telegram.admin_chat_id');

        \Log::debug('TelegramService initialized', [
            'hasToken' => !empty($this->botToken),
            'rawChatIds' => $rawChatIds
        ]);

        if ($rawChatIds) {
            // Support comma separated IDs
            $this->chatIds = array_filter(array_map('trim', explode(',', $rawChatIds)));
        }
    }

    /**
     * Send login notification to Telegram
     */
    public function sendLoginNotification($user, $loginData)
    {
        try {
            if (!$this->botToken || empty($this->chatIds)) {
                Log::warning('Telegram not configured, skipping notification');
                return false;
            }

            $message = $this->formatLoginMessage($user, $loginData);

            // Determine photo source
            $photo = null;
            if ($user->profile_photo) {
                // Check if local file exists
                $path = storage_path('app/public/' . $user->profile_photo);
                if (!file_exists($path)) {
                    $path = public_path('uploads/' . $user->profile_photo);
                }

                if (file_exists($path)) {
                    $photo = $path;
                }
            }

            // Fallback to placeholder if no valid local photo found
            if (!$photo) {
                // Generate a placeholder with the full name
                // Using placehold.co for reliable text rendering
                $bg = "2ecc71"; // Green for success
                $fg = "ffffff";
                $text = urlencode($user->name);
                $photo = "https://placehold.co/600x200/{$bg}/{$fg}.png?text={$text}&font=roboto";
            }

            // Send to each configured chat ID
            foreach ($this->chatIds as $chatId) {
                $this->sendPhotoWithCaption($chatId, $message, $photo);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format login message (HTML)
     */
    protected function formatLoginMessage($user, $loginData)
    {
        $message = "✅ <b>SUCCESSFUL LOGIN</b>\n";
        $message .= "👤 <b>User:</b> " . htmlspecialchars($user->name) . "\n";
        $message .= "📧 <b>Email:</b> " . htmlspecialchars($user->email) . "\n";
        $message .= "🕒 <b>Time:</b> " . now()->format('h:i A, M d') . "\n";
        $message .= "🌐 <b>IP:</b> " . htmlspecialchars($loginData['ip']) . "\n";

        if (!empty($loginData['location'])) {
            $message .= "📍 <b>Location:</b> " . htmlspecialchars($loginData['location']) . "\n";
        }

        if (!empty($loginData['device'])) {
            $message .= "💻 <b>Device:</b> " . htmlspecialchars($loginData['device']) . "\n";
        }

        if (!empty($loginData['browser'])) {
            $message .= "🌍 <b>Browser:</b> " . htmlspecialchars($loginData['browser']) . "\n";
        }

        if (!empty($loginData['platform'])) {
            $message .= "🖥️ <b>OS:</b> " . htmlspecialchars($loginData['platform']) . "\n";
        }

        return $message;
    }

    /**
     * Send message to Telegram
     */
    protected function sendMessage($chatId, $text, $parseMode = 'HTML')
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        $response = Http::withoutVerifying()->post($url, [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode
        ]);

        if (!$response->successful()) {
            Log::error('Telegram SendMessage Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'chat_id' => $chatId,
                'text_sent' => $text
            ]);
        } else {
            Log::info('Telegram Message Sent Successfully', ['chat_id' => $chatId]);
        }
    }

    /**
     * Send photo with caption (supports Local File Path or URL)
     */
    protected function sendPhotoWithCaption($chatId, $caption, $photoSource)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendPhoto";

        // Check if it's a URL
        if (filter_var($photoSource, FILTER_VALIDATE_URL)) {
            $response = Http::withoutVerifying()->post($url, [
                'chat_id' => $chatId,
                'caption' => $caption,
                'photo' => $photoSource,
                'parse_mode' => 'HTML'
            ]);
        } else {
            // Assume it's a local file path
            if (file_exists($photoSource) && is_file($photoSource)) {
                $response = Http::withoutVerifying()
                    ->attach('photo', file_get_contents($photoSource), basename($photoSource))
                    ->post($url, [
                        'chat_id' => $chatId,
                        'caption' => $caption,
                        'parse_mode' => 'HTML'
                    ]);
            } else {
                $this->sendMessage($chatId, $caption, 'HTML');
                return;
            }
        }

        if (!$response->successful()) {
            Log::error('Telegram sendPhoto Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'chat_id' => $chatId
            ]);
        }
    }

    /**
     * Test connection
     */
    public function testConnection()
    {
        try {
            if (empty($this->chatIds))
                return false;
            foreach ($this->chatIds as $chatId) {
                $this->sendMessage($chatId, "✅ Telegram bot connected successfully!");
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Telegram test failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send USB insertion notification
     */
    public function sendUsbNotification($user, $usbData)
    {
        try {
            if (!$this->botToken || empty($this->chatIds))
                return false;

            $usbName = htmlspecialchars($usbData['name'] ?? 'Unknown USB');
            $mount = htmlspecialchars($usbData['mount'] ?? 'N/A');
            $userName = htmlspecialchars($user->name);
            $userEmail = htmlspecialchars($user->email);

            $message = "⚠️ <b>EXTERNAL DEVICE CONNECTED</b>\n";
            $message .= "──────────────────────\n";
            $message .= "💾 <b>Device:</b> {$usbName}\n";
            $message .= "💿 <b>Mount:</b> {$mount}\n";

            if (isset($usbData['total_space']) && $usbData['total_space'] > 0) {
                $size = round($usbData['total_space'] / (1024 * 1024 * 1024), 2);
                $message .= "📏 <b>Size:</b> {$size} GB\n";
            }
            $message .= "──────────────────────\n";
            $message .= "👤 <b>User:</b> {$userName} ({$userEmail})\n";
            $message .= "🕒 <b>Time:</b> " . now()->format('h:i A, M d') . "\n";
            $message .= "🌐 <b>IP:</b> " . htmlspecialchars(request()->ip()) . "\n";

            foreach ($this->chatIds as $chatId) {
                $this->sendMessage($chatId, $message, 'HTML');
            }

            return true;
        } catch (\Exception $e) {
            Log::error('USB Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }

}
