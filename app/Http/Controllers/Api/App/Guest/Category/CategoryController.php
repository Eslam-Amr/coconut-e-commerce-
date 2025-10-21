<?php

namespace App\Http\Controllers\Api\App\Guest\Category;

use App\Http\Controllers\Controller;
use App\Services\Api\App\Guest\Category\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(Request $request)
    {
        return $this->categoryService->index($request);
    }
}
