<?php

namespace App\Services;

use App\Models\User;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Nette\Utils\Callback;

class TelegramService
{
    public string $telegramToken;
    public string $telegramApiEndpoint;
    public string $url;

    public function __construct()
    {
        $this->telegramToken = config('telegram.token');
        $this->telegramApiEndpoint = config('telegram.api_endpoint');
        $this->url = "$this->telegramApiEndpoint/bot$this->telegramToken";
    }

    public function getChatMember(int $userId, int|string $chatId): ?object
    {
        return Http::post("$this->url/" . __FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ])->object();
    }

    public function isChatMember(int $userId, int|string $chatId): bool
    {
        return $this->getChatMember($userId, $chatId)?->result?->status != 'left';
    }

    public function deleteMessage(int|string $chatId, int $messageId): bool
    {
        return (bool)Http::post("$this->url/" . __FUNCTION__, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ])->object()?->result;
    }

    public function sendAudio(int|string $chatId, string $path): ?object
    {
        $file = fopen($path, 'r');

        return Http::attach('audio', $file)
            ->post("$this->url/" . __FUNCTION__, [
                'chat_id' => $chatId,
            ])->object();
    }

    public function sendVideo(int|string $chatId, string $path): ?object
    {
        $file = fopen($path, 'r');

        return Http::attach('video', $file)
            ->post("$this->url/" . __FUNCTION__, [
                'chat_id' => $chatId,
            ])->object();
    }

    public function sendFile(int|string $chatId, string $path, string $type): ?object
    {
        return match ($type) {
            'audio' => $this->sendAudio($chatId, $path),
            'video' => $this->sendVideo($chatId, $path),
        };
    }

    public function deleteMessages(int|string $chatId, array $messageIds): void
    {
        foreach ($messageIds as $messageId) {
            $this->deleteMessage($chatId, $messageId);
        }
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
