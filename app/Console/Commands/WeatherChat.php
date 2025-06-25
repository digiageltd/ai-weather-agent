<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Services\ChatService;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;

class WeatherChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:chat {--user_id=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start an AI weather chat session';

    protected ChatService $chat;

    public function __construct()
    {
        $this->chat = new ChatService();
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @throws ConnectionException
     */
    public function handle(): void
    {
        $userId = (int) $this->option('user_id');

        $conversation = Conversation::firstOrCreate(
            ['user_id' => $userId],
            ['messages' => []],
        );

        $this->info('🌤️  AI Weather Chatbot. Type "exit" to quit.');

        while ($input = $this->ask('You')) {
            if (strtolower($input) === 'exit') break;

            $this->output->write('Assistant: ⏳  Thinking …');
            $reply = $this->chat->handleUserMessage(
                message      : $input,
                userId       : $userId,
                conversation : $conversation    // 👈 pass conversation obj
            );

            $this->output->write("\r");
            $this->line($reply);

            // append turn
            $conversation->messages = array_merge($conversation->messages, [
                ['role' => 'user', 'content' => $input],
                ['role' => 'assistant', 'content' => $reply],
            ]);
            $conversation->save();
        }

        $this->info('Goodbye!');
    }
}
