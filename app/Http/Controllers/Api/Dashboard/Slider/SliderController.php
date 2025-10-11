<?php

namespace App\Http\Controllers\Api\Dashboard\Slider;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Slider\SliderRequest;
use App\Models\Slider;
use App\Services\Api\Dashboard\Slider\SliderService;

class SliderController extends GenericCrudController
{
    public function __construct(SliderService $sliderService)
    {
        parent::__construct(
            $sliderService,
            SliderRequest::class,
            Slider::class
        );
    }
}
