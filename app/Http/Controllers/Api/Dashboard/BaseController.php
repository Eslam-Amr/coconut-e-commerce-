<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

abstract class BaseController extends Controller
{
    use ApiResponseTrait;

    protected $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * Display the specified resource.
     */
    public function show($model)
    {
        return $this->service->show($model);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($model)
    {
        return $this->service->destroy($model);
    }
}
