<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

/* Public Frontend Showcase Catalog */
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('shop', [FrontendController::class, 'shop'])->name('shop');

/* Guest / Authentication Routes */
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    
    Route::get('forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    
    Route::get('reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

/* Authenticated Routes */
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile Management
    Route::prefix('profile')->as('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('index');
        Route::put('/', [ProfileController::class, 'update'])->name('update');
        Route::get('password', [ProfileController::class, 'showPasswordForm'])->name('password');
        Route::put('password', [ProfileController::class, 'updatePassword'])->name('password.update');
    });

    // User Administration CRUD
    Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
    Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export');
    Route::get('users/print', [UserController::class, 'printList'])->name('users.print');
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class);

    // Catalog Prefix Group (Active Highlight Support)
    Route::prefix('catalog')->group(function () {
        // Brands CRUD
        Route::post('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('brands.toggle-status');
        Route::resource('brands', BrandController::class);

        // Categories & Subcategories CRUD
        Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::get('categories/{category}/subcategories', [CategoryController::class, 'getSubCategories'])->name('categories.subcategories');
        Route::get('subcategories', [CategoryController::class, 'listSubCategories'])->name('subcategories.list');
        Route::post('subcategories', [CategoryController::class, 'storeSubCategory'])->name('subcategories.store');
        Route::get('subcategories/{subCategory}', [CategoryController::class, 'showSubCategory'])->name('subcategories.show');
        Route::put('subcategories/{subCategory}', [CategoryController::class, 'updateSubCategory'])->name('subcategories.update');
        Route::delete('subcategories/{subCategory}', [CategoryController::class, 'destroySubCategory'])->name('subcategories.destroy');
        Route::post('subcategories/{subCategory}/toggle-status', [CategoryController::class, 'toggleSubCategoryStatus'])->name('subcategories.toggle-status');
        Route::resource('categories', CategoryController::class);

        // Variants CRUD (Colors, Storage, RAM)
        Route::get('variants', [VariantController::class, 'index'])->name('variants.index');
        Route::post('variants/colors', [VariantController::class, 'storeColor'])->name('variants.colors.store');
        Route::get('variants/colors/{color}', [VariantController::class, 'showColor'])->name('variants.colors.show');
        Route::put('variants/colors/{color}', [VariantController::class, 'updateColor'])->name('variants.colors.update');
        Route::delete('variants/colors/{color}', [VariantController::class, 'destroyColor'])->name('variants.colors.destroy');

        Route::post('variants/storage', [VariantController::class, 'storeStorage'])->name('variants.storage.store');
        Route::get('variants/storage/{storage}', [VariantController::class, 'showStorage'])->name('variants.storage.show');
        Route::put('variants/storage/{storage}', [VariantController::class, 'updateStorage'])->name('variants.storage.update');
        Route::delete('variants/storage/{storage}', [VariantController::class, 'destroyStorage'])->name('variants.storage.destroy');

        Route::post('variants/ram', [VariantController::class, 'storeRam'])->name('variants.ram.store');
        Route::get('variants/ram/{ram}', [VariantController::class, 'showRam'])->name('variants.ram.show');
        Route::put('variants/ram/{ram}', [VariantController::class, 'updateRam'])->name('variants.ram.update');
        Route::delete('variants/ram/{ram}', [VariantController::class, 'destroyRam'])->name('variants.ram.destroy');

        // Products CRUD
        Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::get('products/{product}/variants', [ProductController::class, 'getVariants'])->name('products.variants');
        Route::resource('products', ProductController::class);
    });

    // Contacts Prefix Group (Active Highlight Support)
    Route::prefix('contacts')->group(function () {
        // Customers CRUD
        Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
        Route::get('customers/print', [CustomerController::class, 'printList'])->name('customers.print');
        Route::resource('customers', CustomerController::class);

        // Suppliers CRUD
        Route::post('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
        Route::get('suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export');
        Route::get('suppliers/print', [SupplierController::class, 'printList'])->name('suppliers.print');
        Route::resource('suppliers', SupplierController::class);
    });

    // AJAX Fallbacks for direct legacy URL compatibility
    Route::post('brands/{brand}/toggle-status', [BrandController::class, 'toggleStatus']);
    Route::resource('brands', BrandController::class)->only(['store', 'show', 'update', 'destroy']);

    Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus']);
    Route::get('categories/{category}/subcategories', [CategoryController::class, 'getSubCategories']);
    Route::resource('categories', CategoryController::class)->only(['store', 'show', 'update', 'destroy']);

    Route::get('subcategories', [CategoryController::class, 'listSubCategories']);
    Route::post('subcategories', [CategoryController::class, 'storeSubCategory']);
    Route::get('subcategories/{subCategory}', [CategoryController::class, 'showSubCategory']);
    Route::put('subcategories/{subCategory}', [CategoryController::class, 'updateSubCategory']);
    Route::delete('subcategories/{subCategory}', [CategoryController::class, 'destroySubCategory']);
    Route::post('subcategories/{subCategory}/toggle-status', [CategoryController::class, 'toggleSubCategoryStatus']);

    Route::post('variants/colors', [VariantController::class, 'storeColor']);
    Route::get('variants/colors/{color}', [VariantController::class, 'showColor']);
    Route::put('variants/colors/{color}', [VariantController::class, 'updateColor']);
    Route::delete('variants/colors/{color}', [VariantController::class, 'destroyColor']);

    Route::post('variants/storage', [VariantController::class, 'storeStorage']);
    Route::get('variants/storage/{storage}', [VariantController::class, 'showStorage']);
    Route::put('variants/storage/{storage}', [VariantController::class, 'updateStorage']);
    Route::delete('variants/storage/{storage}', [VariantController::class, 'destroyStorage']);

    Route::post('variants/ram', [VariantController::class, 'storeRam']);
    Route::get('variants/ram/{ram}', [VariantController::class, 'showRam']);
    Route::put('variants/ram/{ram}', [VariantController::class, 'updateRam']);
    Route::delete('variants/ram/{ram}', [VariantController::class, 'destroyRam']);

    Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus']);
    Route::get('products/{product}/variants', [ProductController::class, 'getVariants']);
    Route::resource('products', ProductController::class)->only(['store', 'show', 'update', 'destroy']);

    Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus']);
    Route::get('customers/export', [CustomerController::class, 'export']);
    Route::get('customers/print', [CustomerController::class, 'printList']);
    Route::resource('customers', CustomerController::class)->only(['store', 'show', 'update', 'destroy']);

    Route::post('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus']);
    Route::get('suppliers/export', [SupplierController::class, 'export']);
    Route::get('suppliers/print', [SupplierController::class, 'printList']);
    Route::resource('suppliers', SupplierController::class)->only(['store', 'show', 'update', 'destroy']);

    // Company Settings
    Route::get('settings', [CompanyController::class, 'edit'])->name('settings.index');
    Route::put('settings', [CompanyController::class, 'update'])->name('settings.update');

    // Warehouses CRUD
    Route::post('warehouses/{warehouse}/toggle-status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status');
    Route::get('warehouses/{warehouse}/stocks', [WarehouseController::class, 'stocks'])->name('warehouses.stocks');
    Route::resource('warehouses', WarehouseController::class);

    // Inventory & Adjustments & Transfers
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/adjustments', [InventoryController::class, 'adjustments'])->name('inventory.adjustments');
    Route::post('inventory/adjustments', [InventoryController::class, 'storeAdjustment'])->name('inventory.adjustments.store');
    Route::get('inventory/transfers', [InventoryController::class, 'transfers'])->name('inventory.transfers');
    Route::post('inventory/transfers', [InventoryController::class, 'storeTransfer'])->name('inventory.transfers.store');
    Route::get('inventory/available-imeis', [InventoryController::class, 'getAvailableImeis'])->name('inventory.available-imeis');
    Route::get('purchases', function () { return view('placeholder', ['title' => 'Purchases Module']); })->name('purchases.index');
    Route::get('sales/pos', function () { return view('placeholder', ['title' => 'POS Billing Module']); })->name('sales.pos');
    Route::get('accounts', function () { return view('placeholder', ['title' => 'Accounts Module']); })->name('accounts.index');
    Route::get('reports', function () { return view('placeholder', ['title' => 'Reports Module']); })->name('reports.index');
});
