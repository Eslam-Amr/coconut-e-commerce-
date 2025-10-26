<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Redis\Connections\PhpRedisConnection;

class SubscriberCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:subscriber';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to Redis messages and store them as notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Connecting to Redis...');

            $channel = "laravel-events";

            Redis::subscribe([$channel], function ($message) {
                $data = json_decode($message, true);
                Log::info("Message from Redis: " . json_encode($data));
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                    $source = isset($data['source']) ? $data['source'] : 'unknown';
                    $msg = isset($data['message']) ? $data['message'] : $message;
                    $timestamp = now()->format('Y-m-d H:i:s');
                    echo "[{$timestamp}] [" . strtoupper($source) . "] {$msg}\n";
                } else {
                    $timestamp = now()->format('Y-m-d H:i:s');
                    echo "[{$timestamp}] [RAW] {$message}\n";
                }
            });

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            sleep(2);
            $this->handle(); // Retry connection
        }
    }
}
