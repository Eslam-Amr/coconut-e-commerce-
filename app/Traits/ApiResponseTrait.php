<?php

namespace App\Traits;

trait ApiResponseTrait
{
    protected function successResponse($data, $message = null, $status = 200)
    {
        $message = $message ?: __('messages.success');
        
        return response()->json([
            "success" => true,
            "message" => $message,
            "data" => $data
        ], $status);
    }
    protected function successResponsePaginated($paginator, $message = null, $status = 200, $resourceClass = null)
{
    $message = $message ?: __('messages.success');

    // If a resource is provided, transform the paginator items
    $data = $resourceClass 
        ? $resourceClass::collection($paginator->items())
        : $paginator->items();

    $response = [
        'success' => true,
        'message' => $message,
        'data'    => $data,
    ];

    if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator ||
        $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
        
        $response['meta'] = [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
        ];

        if ($paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) {
            $response['meta']['total']     = $paginator->total();
            $response['meta']['last_page'] = $paginator->lastPage();
        }
    }

    return response()->json($response, $status);
}


    protected function successWithTokenResponse($token, $message = null, $status = 200)
    {
        $message = $message ?: __('messages.success');
        
        return response()->json([
            "success" => true,
            "message" => $message,
            "token" => $token
        ], $status);
    }

    protected function successNotDataResponse($message = null, $status = 200)
    {
        $message = $message ?: __('messages.success');
        
        return response()->json([
            "success" => true,
            "message" => $message,
        ], $status);
    }

    protected function notContentResponse($message = null)
    {
        $message = $message ?: __('messages.not_found');
        
        return response()->json([
            'success' => false,
            'message' => $message
        ], 204);
    }

    protected function notFoundResponse($message = null, $details = [])
    {
        $message = $message ?: __('messages.not_found');
        
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        return response()->json($response, 404);
    }

    protected function serverErrorResponse($message = null, $details = [])
    {
        $message = $message ?: __('messages.error');
        
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        return response()->json($response, 500);
    }

    protected function badRequestResponse($message = null, $errors = [], $details = [])
    {
        $message = $message ?: __('messages.validation_failed');
        
        $response = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ];

        if (!empty($details)) {
            $response['details'] = $details;
        }

        return response()->json($response, 422); // Changed to 422 for validation errors
    }

    protected function errorResponse($message = null, $errors = [], $code = 400, $details = [])
    {
        $message = $message ?: __('messages.error');
        
        $response = [
            "success" => false,
            "message" => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        if (!empty($details)) {
            $response['details'] = $details;
        }

        return response()->json($response, $code);
    }
}