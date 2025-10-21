<?php

namespace App\Http\Controllers\Api\App\Guest\Brand;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Guest\Brand\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(private BrandService $brandService) {}

    public function index(Request $request)
    {
        return $this->brandService->index($request);
    }
}
