<?php

namespace App\Http\Controllers\Api\App\Guest\Banner;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Guest\Banner\BannerService;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct(private BannerService $bannerService) {}

    public function index(Request $request)
    {
        return $this->bannerService->index($request);
    }

    public function show($id)
    {
        return $this->bannerService->show($id);
    }
}
