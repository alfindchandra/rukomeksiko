<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // API middleware stack
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Add CORS middleware for all routes
        $middleware->web(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->api(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Custom middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Handle preflight requests
        $middleware->validateCsrfTokens(except: [
            'api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        
        // Handle API exceptions
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                
                // Handle validation exceptions
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Validation failed',
                        'errors' => $e->errors()
                    ], 422);
                }
                
                // Handle authentication exceptions
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthenticated',
                        'errors' => ['auth' => ['Authentication required']]
                    ], 401);
                }
                
                // Handle authorization exceptions
                if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Unauthorized',
                        'errors' => ['auth' => ['Insufficient permissions']]
                    ], 403);
                }
                
                // Handle model not found exceptions
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Resource not found',
                        'errors' => ['resource' => ['The requested resource was not found']]
                    ], 404);
                }
                
                // Handle method not allowed exceptions
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Method not allowed',
                        'errors' => ['method' => ['The specified method is not allowed for this route']]
                    ], 405);
                }
                
                // Handle general HTTP exceptions
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $e->getMessage() ?: 'HTTP error',
                        'errors' => ['http' => [$e->getMessage() ?: 'An HTTP error occurred']]
                    ], $e->getStatusCode());
                }
                
                // Handle database exceptions
                if ($e instanceof \Illuminate\Database\QueryException) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Database error',
                        'errors' => ['database' => ['A database error occurred']]
                    ], 500);
                }
                
                // Handle all other exceptions in production
                if (!config('app.debug')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Internal server error',
                        'errors' => ['server' => ['An unexpected error occurred']]
                    ], 500);
                }
            }
            
            // Return null to use default exception handling for non-API routes
            return null;
        });
        
    })->create();