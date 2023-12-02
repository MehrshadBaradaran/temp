<?php

namespace App\Handlers;

use App\Downloader\DirectDownloader;
use App\Services\TelegramService;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Stringable;
use Log;

class MyWebhookHandler extends WebhookHandler
{
    protected string $stage = 'await';

    protected function allowUnknownChat(): bool
    {
        return true;
    }

    public function check(): void
    {
        Log::channel('logs')->info('action called');
    }

    protected function handleChatMessage(Stringable $text): void
    {
        Log::channel('logs')->info($text);

    }

    public function handle(Request $request, TelegraphBot $bot): void
    {
        $requestData = $request->all();
        Log::channel('telegram')->info($requestData);

        if (isset($requestData['callback_query'])) {
            $command = $requestData['callback_query']['message']['text'];
            $commandType = $requestData['callback_query']['message']['text'];
            $chatId = $requestData['callback_query']['message']['chat']['id'];
            $userId = $requestData['callback_query']['from']['id'];
            $userUsername = $requestData['callback_query']['from']['username'];

        } else if (isset($requestData['edited_message'])) {
            $command = $requestData['edited_message']['text'];
            $commandType = $requestData['edited_message']['text'];
            $chatId = $requestData['edited_message']['chat']['id'];
            $userId = $requestData['edited_message']['from']['id'];
            $userUsername = $requestData['edited_message']['from']['username'];

        } else if (isset($requestData['my_chat_member'])) {
            $command = null;
            $commandType = 'text';
            $chatId = $requestData['my_chat_member']['chat']['id'];
            $userId = $requestData['my_chat_member']['from']['id'];
            $userUsername = $requestData['my_chat_member']['from']['username'];

        } else {
            $command = $requestData['message']['text'];
            $commandType = isset($requestData['message']['entities']) ? $requestData['message']['entities'][0]['type'] : 'text';
            $userId = $requestData['message']['from']['id'];
            $chatId = $requestData['message']['chat']['id'];
            $userUsername = $requestData['message']['from']['username'];
        }
        if (!isset($requestData['my_chat_member'])) {
            $chat = $bot->chats()->createOrFirst(
                [
                    'chat_id' => $chatId,
                ],
                [
                    'chat_id' => $chatId,
                    'name' => $userUsername,
                ]);

            $service = new TelegramService();
            $isMemberOfOurChannel = $service->isChatMember($userId, '@bottestchannelmehrshad');

            try {
                if ($command == '/start') {
                    $chat->html("Hello!😊\n\nwelcome to MediaGirBot🤖")->send()->object();

                } elseif ($isMemberOfOurChannel) { // command is a URL
                    if ($commandType == 'url') {
                        $this->stage = 'processing';
                        $chat->html('Processing...')->send()->object();
                        $directDownloader = new DirectDownloader($command);

                        if ($directDownloader->validate()) {
                            $chat->html('Downloading⬇️')->send()->object();
                            $path = $directDownloader->download();
                            $service->sendFile($chatId, $path, $directDownloader->getType());
                            $this->stage = 'await';

                        } else {
                            $this->stage = 'await';
                            $chat->html('Only mp3🎧 and mp4📽️ files are downloadable!')
                                ->send()->object();
                        }

                    } else { // command is something we don't need
                        $this->stage = 'await';
                        if (!isset($requestData['callback_query'])) {
                            $chat->html('Please enter a valid URL!')->send()->object();
                        }
                    }
                }
                $cacheKey = "{$chatId}_last_join_message";

                if (isset($requestData['callback_query'])) {
                    if (!$isMemberOfOurChannel) {
                        Telegraph::replyWebhook($requestData['callback_query']['id'], 'Please join to all of our channels', true)->send();
                    } else {
                        $service->deleteMessage($chatId, Cache::get($cacheKey));
                        $chat->html('Send any link to download: ')->send();
                    }

                } else {
                    if (!$isMemberOfOurChannel) {
                        $joinMessage = $chat->html('please join to our channels:')
                            ->keyboard(Keyboard::make()->buttons([
                                Button::make('BotTestChannelMehrshad❤️')->url('https://t.me/bottestchannelmehrshad'),
                                Button::make('check')->action('check'),
                            ]))
                            ->send()
                            ->object();

                        Cache::forget($cacheKey);
                        Cache::put($cacheKey, $joinMessage->result->message_id);

                    } else {
                        if ($this->stage == 'await') {
                            $chat->html('Send any link to download: ')->send();
                        }

                    }
                }

            } catch (Exception $exception) {
                $chat->html('Something Went Wrong! ☹️')->send();
                Log::channel('error')->error($exception->getMessage());
            }
        }
    }
}
