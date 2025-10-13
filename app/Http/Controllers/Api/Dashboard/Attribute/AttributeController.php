<?php

namespace App\Http\Controllers\Api\Dashboard\Attribute;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Attribute\AttributeRequest;
use App\Models\Attribute;
use App\Services\Api\Dashboard\Attribute\AttributeService;
use Illuminate\Routing\Controllers\HasMiddleware;

class AttributeController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'attributes.view',
        'show' => 'attributes.view',
        'store' => 'attributes.create',
        'update' => 'attributes.update',
        'destroy' => 'attributes.delete',
        'toggleActive' => 'attributes.toggle_active',
    ];
    protected static $middleware = ['admin'];

	public function __construct(AttributeService $attributeService)
    {
        parent::__construct(
			$attributeService,
			AttributeRequest::class,
			Attribute::class
        );
    }

}
