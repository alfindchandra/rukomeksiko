<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RukoController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Master Data Routes (Admin Pusat only)
    Route::middleware('role:admin_pusat')->group(function () {
        
        // Products
        Route::apiResource('products', ProductController::class);
        Route::patch('/products/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
        
        // Categories
        Route::apiResource('categories', CategoryController::class);
        Route::patch('/categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus']);
        
        // Suppliers
        Route::apiResource('suppliers', SupplierController::class);
        Route::patch('/suppliers/{id}/toggle-status', [SupplierController::class, 'toggleStatus']);
        
        // Rukos
        Route::apiResource('rukos', RukoController::class);
        Route::patch('/rukos/{id}/toggle-status', [RukoController::class, 'toggleStatus']);
        
        // Purchase Orders
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        Route::patch('/purchase-orders/{id}/approve', [PurchaseOrderController::class, 'approve']);
        Route::patch('/purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
        Route::patch('/purchase-orders/{id}/cancel', [PurchaseOrderController::class, 'cancel']);
        
        // Shipments (Create and Cancel)
        Route::post('/shipments', [ShipmentController::class, 'store']);
        Route::patch('/shipments/{id}/cancel', [ShipmentController::class, 'cancel']);
    });
    
    // Inventory Management
    Route::prefix('inventory')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::get('/movements', [InventoryController::class, 'stockMovements']);
        Route::get('/low-stock', [InventoryController::class, 'lowStock']);
        Route::get('/stock-value', [InventoryController::class, 'stockValue']);
        Route::get('/product/{productId}', [InventoryController::class, 'productStock']);
    });
    
    // Shipments (View and Receive)
    Route::get('/shipments', [ShipmentController::class, 'index']);
    Route::get('/shipments/{id}', [ShipmentController::class, 'show']);
    Route::patch('/shipments/{id}/receive', [ShipmentController::class, 'receive']);
    
    // Sales Management
    Route::prefix('sales')->group(function () {
        Route::get('/', [SalesController::class, 'index']);
        Route::post('/', [SalesController::class, 'store']);
        Route::get('/{id}', [SalesController::class, 'show']);
        Route::patch('/{id}/cancel', [SalesController::class, 'cancel']);
        Route::get('/reports/summary', [SalesController::class, 'report']);
    });
    
    // Read-only access to master data for all authenticated users
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/suppliers', [SupplierController::class, 'index']);
    Route::get('/rukos', [RukoController::class, 'index']);
});

