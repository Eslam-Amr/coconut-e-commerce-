<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use App\Services\Utilities\InteractionPointsService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule time decay for interaction points
Schedule::call(function () {
    $interactionService = app(InteractionPointsService::class);
    $affectedRows = $interactionService->applyTimeDecay();
    Log::info("Applied time decay to {$affectedRows} interaction records");
})->weekly();

// Schedule cleanup of zero-point interactions
Schedule::call(function () {
    $interactionService = app(InteractionPointsService::class);
    $deletedRows = $interactionService->cleanupZeroPointInteractions();
    Log::info("Cleaned up {$deletedRows} zero-point interaction records");
})->monthly();
