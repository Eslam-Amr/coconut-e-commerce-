<?php

namespace App\Http\Controllers\Api\Dashboard\Category;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Category\CategoryRequest;
use App\Models\Category;
use App\Services\Api\Dashboard\Category\CategoryService;

class CategoryController extends GenericCrudController
{
    public function __construct(CategoryService $categoryService)
    {
        parent::__construct(
            $categoryService,
            CategoryRequest::class,

            Category::class
        );
    }
}
