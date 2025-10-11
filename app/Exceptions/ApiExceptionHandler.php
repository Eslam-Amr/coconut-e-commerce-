<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpFoundation\Response;
use PDOException;

class ApiExceptionHandler
{
    use ApiResponseTrait;

    public static function handle(Throwable $e, Request $request)
    {
        $handler = new self();
        
        // Log the exception for debugging (exclude sensitive data)
        $handler->logException($e, $request);

        // Handle specific exception types
        return match (true) {
            // Authentication errors
            $e instanceof AuthenticationException => $handler->handleAuthenticationException($e),
            
            // Authorization errors
            $e instanceof AuthorizationException => $handler->handleAuthorizationException($e),
            
            // Validation errors
            $e instanceof ValidationException => $handler->handleValidationException($e),
            
            // Model not found errors
            $e instanceof ModelNotFoundException => $handler->handleModelNotFoundException($e),
            
            // HTTP errors
            $e instanceof NotFoundHttpException => $handler->handleNotFoundHttpException($e),
            $e instanceof MethodNotAllowedHttpException => $handler->handleMethodNotAllowedHttpException($e),
            $e instanceof TooManyRequestsHttpException => $handler->handleTooManyRequestsException($e),
            $e instanceof HttpException => $handler->handleHttpException($e),
            
            // Database errors
            $e instanceof QueryException => $handler->handleQueryException($e),
            $e instanceof PDOException => $handler->handlePDOException($e),
            
            // File/Storage errors
            $e instanceof \Illuminate\Contracts\Filesystem\FileNotFoundException => $handler->handleFileNotFoundException($e),
            
            // Token errors (for API authentication)
            
            // Custom application exceptions
            
            // Default server error
            default => $handler->handleServerException($e),
        };
    }

    /**
     * Handle authentication exceptions (401)
     */
    private function handleAuthenticationException(AuthenticationException $e)
    {
        return $this->errorResponse(
            message: __('messages.unauthenticated'),
            errors: [],
            code: Response::HTTP_UNAUTHORIZED,
            details: [
                'error_type' => 'authentication_error',
                'guards' => $e->guards(),
            ]
        );
    }

    /**
     * Handle authorization exceptions (403)
     */
    private function handleAuthorizationException(AuthorizationException $e)
    {
        return $this->errorResponse(
            message: __('messages.forbidden'),
            errors: [],
            code: Response::HTTP_FORBIDDEN,
            details: [
                'error_type' => 'authorization_error',
                'message' => $e->getMessage() ?: 'Access denied to this resource',
            ]
        );
    }

    /**
     * Handle validation exceptions (422)
     */
    private function handleValidationException(ValidationException $e)
    {
        return $this->badRequestResponse(
            message: __('messages.validation_failed'),
            errors: $e->errors(),
            details: [
                'error_type' => 'validation_error',
                'failed_rules' => $this->getFailedRules($e),
            ]
        );
    }

    /**
     * Handle model not found exceptions (404)
     */
    private function handleModelNotFoundException(ModelNotFoundException $e)
    {
        $model = class_basename($e->getModel());
        $ids = $e->getIds();
        
        return $this->notFoundResponse(
            message: __('messages.resource_not_found', ['resource' => $model]),
            details: [
                'error_type' => 'resource_not_found',
                'model' => $model,
                'ids' => $ids,
            ]
        );
    }

    /**
     * Handle not found HTTP exceptions (404)
     */
    private function handleNotFoundHttpException(NotFoundHttpException $e)
    {
        return $this->notFoundResponse(
            message: __('messages.route_not_found'),
            details: [
                'error_type' => 'route_not_found',
                'message' => $e->getMessage() ?: 'The requested resource was not found',
            ]
        );
    }

    /**
     * Handle method not allowed exceptions (405)
     */
    private function handleMethodNotAllowedHttpException(MethodNotAllowedHttpException $e)
    {
        return $this->errorResponse(
            message: __('messages.method_not_allowed'),
            errors: [],
            code: Response::HTTP_METHOD_NOT_ALLOWED,
            details: [
                'error_type' => 'method_not_allowed',
                'allowed_methods' => $e->getHeaders()['Allow'] ?? [],
            ]
        );
    }

    /**
     * Handle rate limiting exceptions (429)
     */
    private function handleTooManyRequestsException(TooManyRequestsHttpException $e)
    {
        $retryAfter = $e->getHeaders()['Retry-After'] ?? null;
        
        return $this->errorResponse(
            message: __('messages.too_many_requests'),
            errors: [],
            code: Response::HTTP_TOO_MANY_REQUESTS,
            details: [
                'error_type' => 'rate_limit_exceeded',
                'retry_after' => $retryAfter,
                'message' => 'Too many requests. Please try again later.',
            ]
        );
    }

    /**
     * Handle general HTTP exceptions
     */
    private function handleHttpException(HttpException $e)
    {
        return $this->errorResponse(
            message: $e->getMessage() ?: __('messages.http_error'),
            errors: [],
            code: $e->getStatusCode(),
            details: [
                'error_type' => 'http_error',
                'status_code' => $e->getStatusCode(),
            ]
        );
    }

    /**
     * Handle database query exceptions (500)
     */
    private function handleQueryException(QueryException $e)
    {
        // Don't expose sensitive database information in production
        $message = app()->environment('production') 
            ? __('messages.database_error') 
            : $e->getMessage();

        return $this->serverErrorResponse(
            message: $message,
            details: [
                'error_type' => 'database_error',
                'sql_state' => $e->errorInfo[0] ?? null,
                'error_code' => $e->errorInfo[1] ?? null,
            ]
        );
    }

    /**
     * Handle PDO exceptions (500)
     */
    private function handlePDOException(PDOException $e)
    {
        $message = app()->environment('production') 
            ? __('messages.database_connection_error') 
            : $e->getMessage();

        return $this->serverErrorResponse(
            message: $message,
            details: [
                'error_type' => 'database_connection_error',
                'error_code' => $e->getCode(),
            ]
        );
    }

    /**
     * Handle file not found exceptions (404)
     */
    private function handleFileNotFoundException(\Illuminate\Contracts\Filesystem\FileNotFoundException $e)
    {
        return $this->notFoundResponse(
            message: __('messages.file_not_found'),
            details: [
                'error_type' => 'file_not_found',
                'message' => 'The requested file was not found',
            ]
        );
    }



  

    /**
     * Handle all other server exceptions (500)
     */
    private function handleServerException(Throwable $e)
    {
        $message = app()->environment('production') 
            ? __('messages.server_error') 
            : $e->getMessage();

        return $this->serverErrorResponse(
            message: $message,
            details: [
                'error_type' => 'server_error',
                'exception_class' => get_class($e),
                'file' => app()->environment('production') ? null : $e->getFile(),
                'line' => app()->environment('production') ? null : $e->getLine(),
            ]
        );
    }

    /**
     * Log the exception with context
     */
    private function logException(Throwable $e, Request $request): void
    {
        $context = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => Auth::check() ? Auth::id() : null,
        ];

        // Log different levels based on exception type
        match (true) {
            $e instanceof AuthenticationException,
            $e instanceof AuthorizationException,
            $e instanceof ValidationException,
            $e instanceof NotFoundHttpException,
            $e instanceof ModelNotFoundException => Log::info('API Exception', $context),
            
            $e instanceof TooManyRequestsHttpException => Log::warning('Rate Limit Exceeded', $context),
            
            default => Log::error('API Exception', array_merge($context, [
                'trace' => $e->getTraceAsString(),
            ])),
        };
    }

    /**
     * Extract failed rules from validation exception
     */
    private function getFailedRules(ValidationException $e): array
    {
        $failedRules = [];
        
        if (method_exists($e->validator, 'failed')) {
            $failed = $e->validator->failed();
            foreach ($failed as $field => $rules) {
                $failedRules[$field] = array_keys($rules);
            }
        }
        
        return $failedRules;
    }
}
