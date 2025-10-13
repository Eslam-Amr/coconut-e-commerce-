<?php

namespace App\Http\Controllers\Api\Dashboard\Attribute\AttributeValue;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Attribute\AttributeValue\AttributeValueRequest;
use App\Models\AttributeValue;
use App\Services\Api\Dashboard\Attribute\AttributeValue\AttributeValueService;
use Illuminate\Routing\Controllers\HasMiddleware;

class AttributeValueController extends GenericCrudController implements HasMiddleware
{
    protected static $permissionsList = [
        'index' => 'attribute_values.view',
        'show' => 'attribute_values.view',
        'store' => 'attribute_values.create',
        'update' => 'attribute_values.update',
        'destroy' => 'attribute_values.delete',
        'toggleActive' => 'attribute_values.toggle_active',
    ];
    protected static $middleware = ['admin'];

	public function __construct(AttributeValueService $attributeValueService)
    {
        parent::__construct(
			$attributeValueService,
			AttributeValueRequest::class,
			AttributeValue::class
        );
    }

    
}