<?php

namespace App\Http\Controllers\Api\Dashboard\StaticPage;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Models\StaticPage;
use App\Services\Api\Dashboard\StaticPage\StaticPageService;
use App\Http\Requests\Api\Dashboard\StaticPage\StaticPageRequest;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class StaticPageController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'static_pages.view',
        'show' => 'static_pages.view',
        'store' => 'static_pages.create',
        'update' => 'static_pages.update',
        'destroy' => 'static_pages.delete',
    ];

    protected static $middleware = ['admin'];

    public function __construct(StaticPageService $staticPageService)
    {
        parent::__construct(
            $staticPageService,
            StaticPageRequest::class,
            StaticPage::class
        );
    }
}
