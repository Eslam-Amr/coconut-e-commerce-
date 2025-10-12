<?php

namespace App\Http\Controllers\Api\Dashboard\Slider;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Slider\SliderRequest;
use App\Models\Slider;
use App\Services\Api\Dashboard\Slider\SliderService;
use Illuminate\Routing\Controllers\HasMiddleware;

class SliderController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'sliders.view',
        'show' => 'sliders.view',
        'store' => 'sliders.create',
        'update' => 'sliders.update',
        'destroy' => 'sliders.delete',
        'toggleActive' => 'sliders.toggle_active',
        // 'global' => [
        //     'admin'
        // ]
    ];
    protected static $middleware = ['admin'];

    public function __construct(SliderService $sliderService)
    {
        parent::__construct(
            $sliderService,
            SliderRequest::class,
            Slider::class
        );
    }
}
