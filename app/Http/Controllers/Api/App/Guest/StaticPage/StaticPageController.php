<?php

namespace App\Http\Controllers\Api\App\Guest\StaticPage;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Guest\StaticPage\StaticPageService;
use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    public function __construct(private StaticPageService $staticPageService) {}

    public function index(Request $request)
    {
        return $this->staticPageService->index($request);
    }

    public function show($title)
    {
        return $this->staticPageService->show($title);
    }
}
