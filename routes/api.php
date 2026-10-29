<?php

use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\IngredientController as AdminIngredientController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\SizeController as AdminSizeController;
use App\Http\Controllers\Api\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\Api\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Api\Admin\PageController as AdminPageController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Guest\BannerController as GuestBannerController;
use App\Http\Controllers\Api\Guest\CategoryController as GuestCategoryController;
use App\Http\Controllers\Api\Guest\IngredientController as GuestIngredientController;
use App\Http\Controllers\Api\Guest\PageController as GuestPageController;
use App\Http\Controllers\Api\Guest\ProductController as GuestProductController;
use App\Http\Controllers\Api\Guest\SearchController as GuestSearchController;
use App\Http\Controllers\Api\Guest\SizeController as GuestSizeController;
use App\Http\Controllers\Api\Guest\StoreController as GuestStoreController;
use App\Http\Controllers\Api\Stock\StockController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest - công khai, URL thân thiện SEO (danh mục / sản phẩm theo slug)
|--------------------------------------------------------------------------
*/

Route::get('/categories', [GuestCategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}', [GuestCategoryController::class, 'show'])->name('categories.show');

Route::get('/products', [GuestProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [GuestProductController::class, 'show'])->name('products.show');

Route::get('/sizes', [GuestSizeController::class, 'index'])->name('sizes.index');
Route::get('/sizes/{id}', [GuestSizeController::class, 'show'])->whereNumber('id')->name('sizes.show');

Route::get('/ingredients', [GuestIngredientController::class, 'index'])->name('ingredients.index');
Route::get('/ingredients/{id}', [GuestIngredientController::class, 'show'])->whereNumber('id')->name('ingredients.show');

Route::get('/stores', [GuestStoreController::class, 'index'])->name('stores.index');
Route::get('/stores/{id}', [GuestStoreController::class, 'show'])->whereNumber('id')->name('stores.show');
Route::get('/stores/{id}/products', [GuestStoreController::class, 'products'])->whereNumber('id')->name('stores.products');
Route::get('/banners', [GuestBannerController::class, 'index'])->name('banners.index');
Route::get('/pages/{slug}', [GuestPageController::class, 'show'])->name('pages.show');

Route::get('/search', [GuestSearchController::class, 'index'])->name('search.index');

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

Route::middleware('auth:sanctum,api')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum,api', 'role:warehouse,sales'])->group(function () {
    Route::get('/staff/profile', [AuthController::class, 'profile']);
});

/*
|--------------------------------------------------------------------------
| Kho (Stock) - nhan vien kho chi thao tac trong pham vi cua hang cua ho
|   - warehouse: ghim vao store_id cua nhan vien, store khac => 403
|   - admin     : pham vi toan he thong, duoc loc theo store_id
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum,api', 'store.scope:sales,warehouse,admin,manager'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::post('/clock-in', [\App\Http\Controllers\Api\ClockInController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out', [\App\Http\Controllers\Api\ClockInController::class, 'clockOut'])->name('clock-out');
    Route::get('/salary', [\App\Http\Controllers\Api\SalaryController::class, 'calculate'])->name('salary');
    Route::get('/staff', [\App\Http\Controllers\Api\StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/{staff}', [\App\Http\Controllers\Api\StaffController::class, 'show'])->name('staff.show');
});

/*
|--------------------------------------------------------------------------
| Quản lý chấm công / ca làm / lương (manager trong store, admin toàn hệ thống)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum,api', 'role:manager,admin', 'store.scope:manager,admin'])->prefix('attendance')->name('attendance.')->group(function () {
    Route::get('/clock-ins', [\App\Http\Controllers\Api\ClockInController::class, 'index'])->name('clock-ins.index');
    Route::post('/clock-ins', [\App\Http\Controllers\Api\ClockInController::class, 'store'])->name('clock-ins.store');
    Route::put('/clock-ins/{clock_in}', [\App\Http\Controllers\Api\ClockInController::class, 'update'])->name('clock-ins.update');
    Route::delete('/clock-ins/{clock_in}', [\App\Http\Controllers\Api\ClockInController::class, 'destroy'])->name('clock-ins.destroy');

    Route::get('/shifts', [\App\Http\Controllers\Api\StaffShiftController::class, 'index'])->name('shifts.index');
    Route::post('/shifts', [\App\Http\Controllers\Api\StaffShiftController::class, 'store'])->name('shifts.store');
    Route::put('/shifts/{shift}', [\App\Http\Controllers\Api\StaffShiftController::class, 'update'])->name('shifts.update');
    Route::delete('/shifts/{shift}', [\App\Http\Controllers\Api\StaffShiftController::class, 'destroy'])->name('shifts.destroy');

    Route::put('/staff/{staff}/hourly-rate', [\App\Http\Controllers\Api\SalaryController::class, 'updateRate'])->name('staff.hourly-rate');
});

Route::middleware(['auth:sanctum,api', 'store.scope:sales,warehouse,admin'])->prefix('stock')->name('stock.')->group(function () {
    Route::get('/', [StockController::class, 'index'])->name('index');
    Route::get('/{stock}', [StockController::class, 'show'])->name('show');
});

Route::middleware(['auth:sanctum,api', 'store.scope:warehouse,admin'])->prefix('stock')->name('stock.')->group(function () {
    Route::post('/', [StockController::class, 'store'])->name('store');
    Route::put('/{stock}', [StockController::class, 'update'])->name('update');
    Route::delete('/{stock}', [StockController::class, 'destroy'])->name('destroy');
});

/*
|--------------------------------------------------------------------------
| Admin - CRUD + search, yêu cầu role:admin
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->middleware(['auth:sanctum,api', 'role:admin'])->group(function () {
    // search phải khai trước apiResource để không bị {id}/{slug} nuốt
    Route::get('products/search', [AdminProductController::class, 'search'])->name('products.search');
    Route::post('products/{product}/restore', [AdminProductController::class, 'restore'])->name('products.restore');
    Route::delete('products/{product}/force', [AdminProductController::class, 'forceDelete'])->name('products.force');
    Route::get('categories/search', [AdminCategoryController::class, 'search'])->name('categories.search');
    Route::get('sizes/search', [AdminSizeController::class, 'search'])->name('sizes.search');
    Route::get('ingredients/search', [AdminIngredientController::class, 'search'])->name('ingredients.search');
    Route::get('stores/search', [AdminStoreController::class, 'search'])->name('stores.search');
    Route::get('banners/search', [AdminBannerController::class, 'search'])->name('banners.search');

    Route::apiResource('products', AdminProductController::class)->except(['create', 'edit']);
    Route::apiResource('categories', AdminCategoryController::class)->except(['create', 'edit']);
    Route::apiResource('sizes', AdminSizeController::class)->except(['create', 'edit']);
    Route::apiResource('ingredients', AdminIngredientController::class)->except(['create', 'edit']);
    Route::apiResource('stores', AdminStoreController::class)->except(['create', 'edit']);
    Route::apiResource('banners', AdminBannerController::class)->except(['create', 'edit']);
    Route::apiResource('pages', AdminPageController::class)->except(['create', 'edit']);

    Route::get('stores/{store}/products', [AdminStoreController::class, 'products'])->name('stores.products');
});
