<?php

namespace App\Http\Controllers\Api\Dashboard\Attribute;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\Attribute\AttributeRequest;
use App\Models\Attribute;
use App\Services\Api\Dashboard\Attribute\AttributeService;

class AttributeController extends GenericCrudController
{
	public function __construct(AttributeService $attributeService)
    {
        parent::__construct(
			$attributeService,
			AttributeRequest::class,
			Attribute::class
        );
    }

}
