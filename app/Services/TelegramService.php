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
            // Don't block login if Telegram is not configured
            if (!$this->botToken || empty($this->chatIds)) {
                Log::warning('Telegram not configured, skipping notification');
                return false;
            }

            $message = $this->formatLoginMessage($user, $loginData);

            // Send to each configured chat ID
            foreach ($this->chatIds as $chatId) {
                if ($user->profile_photo) {
                    $this->sendPhotoWithCaption($chatId, $message, $user->profile_photo);
                } else {
                    $this->sendMessage($chatId, $message);
                }
            }

            return true;
        } catch (\Exception $e) {
            // Log error but don't throw - don't block login
            Log::error('Telegram notification failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Format login message
     */
    protected function formatLoginMessage($user, $loginData)
    {
        $message = "🔐 *New Login Detected*\n\n";
        $message .= "👤 *User:* {$user->name}\n";
        $message .= "📧 *Email:* {$user->email}\n";
        $message .= "🕐 *Time:* " . now()->format('M d, Y h:i A') . "\n";
        $message .= "🌐 *IP:* {$loginData['ip']}\n";

        if (!empty($loginData['location'])) {
            $message .= "📍 *Location:* {$loginData['location']}\n";
        }

        if (!empty($loginData['device'])) {
            $message .= "💻 *Device:* {$loginData['device']}\n";
        }

        if (!empty($loginData['browser'])) {
            $message .= "🌍 *Browser:* {$loginData['browser']}\n";
        }

        if (!empty($loginData['platform'])) {
            $message .= "🖥️ *OS:* {$loginData['platform']}\n";
        }

        return $message;
    }

    /**
     * Send message to Telegram
     */
    protected function sendMessage($chatId, $text)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

        Http::withoutVerifying()->post($url, [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown'
        ]);
    }

    /**
     * Send photo with caption
     */
    protected function sendPhotoWithCaption($chatId, $caption, $profilePhoto)
    {
        $url = "https://api.telegram.org/bot{$this->botToken}/sendPhoto";

        // Get absolute file path
        // Try storage path first (new system)
        $photoPath = storage_path('app/public/' . $profilePhoto);

        // Fallback to legacy path if not found in storage
        if (!file_exists($photoPath) || !is_file($photoPath)) {
            $photoPath = public_path('uploads/' . $profilePhoto);
        }

        if (file_exists($photoPath) && is_file($photoPath)) {
            $response = Http::withoutVerifying()
                ->attach('photo', file_get_contents($photoPath), basename($photoPath))
                ->post($url, [
                    'chat_id' => $chatId,
                    'caption' => $caption,
                    'parse_mode' => 'Markdown'
                ]);
        } else {
            // Fallback to text only if file not found
            $this->sendMessage($chatId, $caption);
            return;
        }

        if (!$response->successful()) {
            Log::error('Telegram API Error for Chat ID ' . $chatId . ': ' . $response->body());
        }
    }

    /**
     * Test connection by sending a test message
     */
    public function testConnection()
    {
        try {
            if (empty($this->chatIds)) {
                return false;
            }

            foreach ($this->chatIds as $chatId) {
                $this->sendMessage($chatId, "✅ Telegram bot connected successfully to this chat!");
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Telegram test failed: ' . $e->getMessage());
            return false;
        }
    }

}
