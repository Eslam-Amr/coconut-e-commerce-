<?php

namespace App\Http\Controllers\Api\Dashboard\Category;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Category\CategoryRequest;
use App\Models\Category;
use App\Services\Api\Dashboard\Category\CategoryService;
use Illuminate\Routing\Controllers\HasMiddleware;

class CategoryController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'categories.view',
        'show' => 'categories.view',
        'store' => 'categories.create',
        'update' => 'categories.update',
        'destroy' => 'categories.delete',
        'toggleActive' => 'categories.toggle_active',
        // 'global' => [
        //     'admin'
        // ]
    ];
    protected static $middleware = ['admin'];

    public function __construct(CategoryService $categoryService)
    {
        parent::__construct(
            $categoryService,
            CategoryRequest::class,
            Category::class
        );
    }
}
