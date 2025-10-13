<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Utilities\MediaService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class GenericCrudController extends Controller implements HasMiddleware
{
    use ApiResponseTrait;

    protected $service;
    protected $requestClass;
    protected $modelClass;
    private MediaService $mediaService;

    public function __construct($service, $requestClass = null, $modelClass = null)
    {
        $this->service = $service;
        $this->mediaService = new MediaService();
        
        // Handle both class names and instances
        if (is_object($requestClass)) {
            $this->requestClass = get_class($requestClass);
        } else {
            $this->requestClass = $requestClass;
        }

        if (is_object($modelClass)) {
            $this->modelClass = get_class($modelClass);
        } else {
            $this->modelClass = $modelClass;
        }
    }
    
    public static function middleware(): array
    {
        $permissions = static::$permissionsList ?? [];
        $middleware = static::$middleware ?? [];
        $middlewareList = [];

        // Handle global middleware (roles)
        // if (!empty($permissions['global'])) {
        //     if (is_array($permissions['global'])) {
        //         // Multiple roles: role:admin,editor
        //         $roles = implode(',', $permissions['global']);
        //         $middlewareList[] = new Middleware("$roles");
        //     } else {
        //         // Single role
        //         $middlewareList[] = new Middleware("{$permissions['global']}");
        //     }
        //     unset($permissions['global']);
        // }
        if (!empty($middleware)) {
            if (is_array($middleware)) {
                // Multiple roles: role:admin,editor
                $roles = implode(',', $middleware);
                $middlewareList[] = new Middleware("$roles");
            } else {
                // Single role
                $middlewareList[] = new Middleware("{$middleware}");
            }
            // unset($permissions['global']);
        }

        // Handle method-specific permissions
        foreach ($permissions as $method => $permission) {
            if (is_array($permission)) {
                // Multiple permissions for one method
                $perms = implode(',', $permission);
                $middlewareList[] = new Middleware("permission:$perms", only: [$method]);
            } else {
                // Single permission for one method
                $middlewareList[] = new Middleware("permission:$permission", only: [$method]);
            }
        }
// dd($middlewareList);
        return $middlewareList;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request->all());
        return $this->service->index($request);
    }

    public function toggleActive($model)
    {
        // If we received a string (ID), resolve the model
        if (is_string($model) && $this->modelClass) {
            $model = app($this->modelClass)->findOrFail($model);
        }
        $model->update([
            'active' => !$model->active,
        ]);
        // dd($model->active);
        $lastPart = Str::singular(ucfirst($model->getTable()));

        return $this->successResponse($model, $lastPart . ' toggled successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show($model)
    {
        // If we received a string (ID), resolve the model
        if (is_string($model) && $this->modelClass) {
            $model = app($this->modelClass)->findOrFail($model);
        }

        return $this->service->show($model);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($model)
    {
        // If we received a string (ID), resolve the model
        if (is_string($model) && $this->modelClass) {
            $model = app($this->modelClass)->findOrFail($model);
        }

        return $this->service->destroy($model);
    }

    // public function store(Request $request)
    public function store()
    {
        // $request =  app($this->requestClass);
        // $validatedData = $this->getValidatedData($request);
        $request =  app($this->requestClass);
        if(method_exists($request, 'validated')){
            $validatedData = $request->validated();
        }else{
            $validatedData = $this->getValidatedData($request);
        }
        $result = $this->service->store($validatedData);

        // Handle image upload if present (after model creation)
        if ($result instanceof \Illuminate\Http\JsonResponse) {
            $original = $result->getOriginalContent();

            if (
                isset($original['data']) &&
                $original['data'] instanceof \Illuminate\Database\Eloquent\Model
            ) {
                $model = $original['data'];

                if ($request->hasFile('image')) {
                    $this->handleImageUpload($request, $model);

                    $model->load('media');
                }

                return $this->successResponse($model, $original['message'] ?? 'Created successfully', 201);
            }
        }
        return $result;
    }

    public function update( $model)
    {
        $request =  app($this->requestClass);
        if(method_exists($request, 'validated')){
            $validatedData = $request->validated();
        }else{
            $validatedData = $this->getValidatedData($request);
        }
        // $validatedData = $this->getValidatedData($request);
        // dd(is_string($model) , $this->modelClass);
        if (is_string($model) && $this->modelClass) {
            // dd(app($this->modelClass)->findOrFail($model));
            $model = app($this->modelClass)->findOrFail($model);
        }
        if ($request->hasFile('image')) {
            // dd($model);
            if ($model->media) {
                if ($model->media->count() > 0) {
                    foreach ($model->media as $media) {
                        $this->mediaService->deleteMedia($media);
                    }
                }
            }
            $this->handleImageUpload($request, $model);
        }
        // dd($validatedData);
        return $this->service->update($model, $validatedData);
    }



    /**
     * Get validated data from request
     */

    protected function getValidatedData($request)
    {
        Log::info('getValidatedData() called');

        if (!$request) {
            return [];
        }

        // 1️⃣ Use FormRequest::validated() if available
        if (method_exists($request, 'validated')) {
            Log::info('Using validated() from request');
            return $request->validated();
        }

        // 2️⃣ Use custom request class if defined
        if ($this->requestClass) {
            Log::info('Using custom request class: ' . $this->requestClass);

            $formRequest = new $this->requestClass();

            // Inject dependencies
            $formRequest->setContainer(app())
                ->setRedirector(app('redirect'))
                ->setUserResolver($request->getUserResolver())
                ->setRouteResolver(function () use ($request) {
                    return $request->route();
                });

            // Set the HTTP method properly
            $formRequest->setMethod($request->method());

            // Merge request data
            $formRequest->merge($request->all());

            // 3️⃣ Create validator from form request rules
            $validator = Validator::make(
                $formRequest->all(),
                $formRequest->rules(),
                method_exists($formRequest, 'messages') ? $formRequest->messages() : [],
                method_exists($formRequest, 'attributes') ? $formRequest->attributes() : []
            );

            // 4️⃣ Check validation result
            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()->toArray()]);
                throw new ValidationException($validator);
            }

            Log::info('Validation passed');
            return $validator->validated();
        }

        // 5️⃣ Fallback: return raw request data
        Log::info('No request class — returning all data');
        return $request->all();
    }

    protected function handleImageUpload(Request $request, $model, string $collection = 'images')
    {
        if (!$request->hasFile('image')) {
            return;
        }
        // $this->mediaService->storeMultipleMedia($request->file('image'), $model, $collection);
        // $this->mediaService->storeImage($request->file('image'), $model, $collection);
        $files = $request->file('image');

        // Check if it's a single file or array of files
        if (is_array($files)) {
            $this->mediaService->storeMultipleMedia($files, $model, $collection);
        } else {
            $this->mediaService->storeImage($files, $model, $collection);
        }
    }


    /**
     * Get the middleware that should be assigned to the controller.
     */
    // public static function middleware(): array
    // {
    //     // Get permissions from the child class static property
    //     $permissions = static::$middlewarePermissions ?? [];
        
    //     if (empty($permissions)) {
    //         return [];
    //     }
    //     dd($permissions['global'],static::$middlewarePermissions);
    //     // Just return global middleware for now - method-specific middleware will be handled in routes
    //     return $permissions['global'] ?? [];
    // }
    // public static function middleware(): array
    // {
    //     $permissions = static::$middlewarePermissions ?? [];
    //     $middlewareList = [];
    
    //     // ✅ Add global middleware (if defined)
    //     if (!empty($permissions['global'])) {
    //         foreach ($permissions['global'] as $global) {
    //             // If these are role names, use role middleware:
    //             $middlewareList[] = ['middleware' => "$global"];
    //         }
    //         unset($permissions['global']);
    //     }
    
    //     // ✅ Add method-specific permissions
    //     foreach ($permissions as $method => $permission) {
    //         $middlewareList[] = [
    //             'middleware' => "permission:$permission",
    //             'only' => [$method],
    //         ];
    //     }
    // // dd($middlewareList);
    //     return $middlewareList;
    // }
    

}
