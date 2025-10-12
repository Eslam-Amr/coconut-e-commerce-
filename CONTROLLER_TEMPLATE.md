# Controller Template for GenericCrudController

## How to Update Your Controllers

For each controller that extends `GenericCrudController`, follow this template:

### 1. Add the HasMiddleware Interface

```php
use Illuminate\Routing\Controllers\HasMiddleware;

class YourController extends GenericCrudController implements HasMiddleware
{
    // ... rest of the controller
}
```

### 2. Add Static Middleware Permissions Property

Add a static `$middlewarePermissions` property to define the middleware configuration:

```php
protected static $middlewarePermissions = [
    'index' => 'your_resource.view',
    'show' => 'your_resource.view',
    'store' => 'your_resource.create',
    'update' => 'your_resource.update',
    'destroy' => 'your_resource.delete',
    'toggleActive' => 'your_resource.toggle_active',
    'global' => [
        'auth',
        'admin'
    ]
];
```

### 3. Update the Constructor

Remove the permissions parameter from the parent constructor:

```php
public function __construct(YourService $yourService)
{
    parent::__construct(
        $yourService,
        YourRequest::class,
        YourModel::class
    );
}
```

## Complete Template

Here's the complete template for any controller:

```php
<?php

namespace App\Http\Controllers\Api\Dashboard\YourModule;

use App\Http\Controllers\Api\Dashboard\GenericCrudController;
use App\Http\Requests\Api\Dashboard\YourModule\YourRequest;
use App\Models\YourModel;
use App\Services\Api\Dashboard\YourModule\YourService;
use Illuminate\Routing\Controllers\HasMiddleware;

class YourController extends GenericCrudController implements HasMiddleware
{
    protected static $middlewarePermissions = [
        'index' => 'your_resource.view',
        'show' => 'your_resource.view',
        'store' => 'your_resource.create',
        'update' => 'your_resource.update',
        'destroy' => 'your_resource.delete',
        'toggleActive' => 'your_resource.toggle_active',
        'global' => [
            'auth',
            'admin'
        ]
    ];

    public function __construct(YourService $yourService)
    {
        parent::__construct(
            $yourService,
            YourRequest::class,
            YourModel::class
        );
    }
}
```

## Permission Naming Convention

Use this naming convention for permissions:

- **Products**: `products.view`, `products.create`, `products.update`, `products.delete`, `products.toggle_active`
- **Categories**: `categories.view`, `categories.create`, `categories.update`, `categories.delete`, `categories.toggle_active`
- **Brands**: `brands.view`, `brands.create`, `brands.update`, `brands.delete`, `brands.toggle_active`
- **Attributes**: `attributes.view`, `attributes.create`, `attributes.update`, `attributes.delete`, `attributes.toggle_active`
- **Banners**: `banners.view`, `banners.create`, `banners.update`, `banners.delete`, `banners.toggle_active`
- **Sliders**: `sliders.view`, `sliders.create`, `sliders.update`, `sliders.delete`, `sliders.toggle_active`
- **Vouchers**: `vouchers.view`, `vouchers.create`, `vouchers.update`, `vouchers.delete`, `vouchers.toggle_active`
- **Flash Sales**: `flash_sales.view`, `flash_sales.create`, `flash_sales.update`, `flash_sales.delete`, `flash_sales.toggle_active`

## Controllers to Update

Here are the controllers you need to update:

1. ✅ **ProductController** - Already updated
2. ✅ **CategoryController** - Already updated  
3. ✅ **BrandController** - Already updated
4. ⏳ **AttributeController**
5. ⏳ **AttributeValueController**
6. ⏳ **BannerController**
7. ⏳ **SliderController**
8. ⏳ **FlashSaleController**
9. ⏳ **VoucherController**
10. ⏳ **CountryController**
11. ⏳ **CityController**
12. ⏳ **DistrictController**
13. ⏳ **ProductAttributeController**
14. ⏳ **ProductVariantController**

## How It Works

1. **Child Controllers** define their middleware configuration in a static `$middlewarePermissions` property
2. The `middleware()` method reads from this static property and returns the middleware configuration
3. Laravel automatically applies the global middleware to all controller methods
4. Method-specific permissions are handled through route middleware in your route files
5. Each controller method is protected by its corresponding permission

## Global Middleware Support

You can now specify global middleware that will be applied to **all methods** in the controller using the `global` key:

```php
[
    'index' => 'products.view',
    'show' => 'products.view',
    'store' => 'products.create',
    'update' => 'products.update',
    'destroy' => 'products.delete',
    'toggleActive' => 'products.toggle_active',
    'global' => [
        'auth',      // Apply to all methods
        'admin',     // Apply to all methods
        'throttle:60' // Apply rate limiting to all methods
    ]
]
```

### Supported Global Middleware Types:
- **Authentication**: `'auth'`, `'auth:admin'`, `'auth:sanctum'`
- **Authorization**: `'admin'`, `'role:admin'`
- **Rate Limiting**: `'throttle:60'`, `'throttle:api'`
- **Custom Middleware**: Any custom middleware you've registered

### Advanced Permission Configuration

You can also specify multiple permissions for a single method:

```php
[
    'store' => ['products.create', 'inventory.manage'], // Both permissions required
    'update' => ['products.update', 'products.edit'],   // Both permissions required
    'global' => ['auth', 'admin']
]
```

## Benefits

- ✅ **DRY Principle**: No duplicate middleware code
- ✅ **Easy to Maintain**: All permission logic in one place
- ✅ **Type Safe**: IDE autocomplete for permission names
- ✅ **Consistent**: Same pattern across all controllers
- ✅ **Laravel Best Practices**: Uses HasMiddleware interface properly

## Example Usage

Once implemented, your routes will automatically be protected:

```php
// This will automatically apply permission:products.view middleware to index and show
// This will automatically apply permission:products.create middleware to store
// This will automatically apply permission:products.update middleware to update
// This will automatically apply permission:products.delete middleware to destroy
// This will automatically apply permission:products.toggle_active middleware to toggleActive

Route::apiResource('products', ProductController::class);
Route::post('products/{product}/toggle', [ProductController::class, 'toggleActive']);
```
