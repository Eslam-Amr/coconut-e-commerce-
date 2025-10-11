<?php

namespace App\Http\Controllers\Api\General\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\General\Settings\ChangeLanguageRequest;
use App\Services\Api\General\Settings\SettingsService;
use Illuminate\Routing\Controllers\HasMiddleware;

class SettingsController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return ['authenticated'];
    }

    public function __construct(public SettingsService $settingsService) {}

    public function changeLanguage(ChangeLanguageRequest $request)
    {
        return $this->settingsService->changeLanguage($request->validated());
    }

    public function toggleNotification()
    {
        return $this->settingsService->toggleNotification();
    }

    public function toggleDarkMode()
    {
        return $this->settingsService->toggleDarkMode();
    }

}
