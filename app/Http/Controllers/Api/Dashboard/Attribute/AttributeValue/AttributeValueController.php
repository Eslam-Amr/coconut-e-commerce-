<?php

namespace App\Http\Controllers\Api\Dashboard\Attribute\AttributeValue;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Attribute\AttributeValue\AttributeValueRequest;
use App\Models\AttributeValue;
use App\Services\Api\Dashboard\Attribute\AttributeValue\AttributeValueService;

class AttributeValueController extends GenericCrudController
{
	public function __construct(AttributeValueService $attributeValueService)
    {
        parent::__construct(
			$attributeValueService,
			AttributeValueRequest::class,
			AttributeValue::class
        );
    }

    
}