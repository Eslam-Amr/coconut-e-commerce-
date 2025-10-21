<?php

namespace App\Http\Controllers\Api\App\Guest\Slider;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Guest\Slider\SliderService;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function __construct(private SliderService $sliderService) {}

    public function index(Request $request)
    {
        return $this->sliderService->index($request);
    }

    public function show($id)
    {
        return $this->sliderService->show($id);
    }
}
