<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\API\Auth\GoogleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SupportBankController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

#============================== ROUTE GOOGLE AUTH =============================
Route::prefix('admin')->as('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'handleLogin'])->name('handleLogin');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('email', function () {
    //    \Illuminate\Support\Facades\Mail::to('quaixe121811@gmail.com')
    //        ->send(new \App\Mail\Auth\VerifyEmail());

    return view('emails.auth.verify');
});

Route::prefix('admin')->as('admin.')
    ->middleware(['roleHasAdmins', 'check_permission:view.dashboard'])
    ->group(function () {
        #============================== ROUTE AUTH =============================
        Route::get('dashboard', function () {
            return view('dashboard');
        })->name('dashboard');

        #============================== ROUTE USER =============================
        Route::prefix('users')->group(function () {
            Route::get('user-clients', [UserController::class, 'index'])->name('clients.index');
            Route::get('user-instructors', [UserController::class, 'index'])->name('instructors.index');
            Route::get('user-admins', [UserController::class, 'index'])->name('admins.index');
            Route::get('user-deleted', [UserController::class, 'index'])->name('users.deleted.index');

            Route::as('users.')->group(function (){
                Route::get('/create', [UserController::class, 'create'])->name('create')
                ->can('user.create');
                Route::post('/', [UserController::class, 'store'])->name('store')
                    ->can('user.create');
                Route::get('/{user}', [UserController::class, 'show'])->name('show');
                Route::get('/edit/{user}', [UserController::class, 'edit'])->name('edit');
                Route::put('/{user}', [UserController::class, 'update'])->name('update')
                    ->can('user.update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy')
                    ->can('user.delete');
                Route::put('/updateEmailVerified/{user}', [UserController::class, 'updateEmailVerified'])
                ->name('updateEmailVerified')->can('user.update');
                Route::put('/updateEmailVerified/{user}', [UserController::class, 'updateEmailVerified'])
                ->name('updateEmailVerified')->can('user.update');
                Route::delete('/{user}/force-delete', [UserController::class, 'forceDelete'])
                ->name('forceDelete')->can('user.update');
                Route::put('/{user}/restore-delete', [UserController::class, 'restoreDelete'])
                ->name('restoreDelete')->can('user.update');
            });
        });

        #============================== ROUTE ROLE =============================
        Route::prefix('roles')->as('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index')
                ->can('role.index');
            Route::get('/create', [RoleController::class, 'create'])->name('create')
                ->can('role.create');
            Route::post('/', [RoleController::class, 'store'])->name('store')
                ->can('role.create');
            Route::get('/{id}', [RoleController::class, 'show'])->name('show')
                ->can('role.show');
            Route::get('/edit/{role}', [RoleController::class, 'edit'])->name('edit')
                ->can('role.edit');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update')
                ->can('role.edit');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')
                ->can('role.delete');
        });

        #============================== ROUTE PERMISSION =============================
        Route::prefix('permissions')->as('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index')
                ->can('permission.index');
            Route::get('/create', [PermissionController::class, 'create'])->name('create')
                ->can('permission.create');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/edit/{permission}', [PermissionController::class, 'edit'])->name('edit');
            Route::put('/{permission}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy')
                ->can('permission.delete');
        });

        #============================== ROUTE CATEGORY =============================
        Route::prefix('categories')->as('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/create', [CategoryController::class, 'create'])->name('create')
                ->can('category.create');
            Route::post('/', [CategoryController::class, 'store'])->name('store')
                ->can('category.create');
            Route::get('/{id}', [CategoryController::class, 'show'])->name('show');
            Route::get('/edit/{category}', [CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update')
                ->can('category.update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy')
                ->can('category.delete');
        });

        #============================== ROUTE BANNER =============================
        Route::prefix('banners')->as('banners.')->group(function () {
            Route::get('/', [BannerController::class, 'index'])->name('index');
            Route::get('/create', [BannerController::class, 'create'])->name('create')
                ->can('banner.create');
            Route::post('/', [BannerController::class, 'store'])->name('store')
                ->can('banner.create');
            Route::get('/{id}', [BannerController::class, 'show'])->name('show');
            Route::get('/edit/{banner}', [BannerController::class, 'edit'])->name('edit');
            Route::put('/{banner}', [BannerController::class, 'update'])->name('update')
                ->can('banner.update');
            Route::delete('/{banner}', [BannerController::class, 'destroy'])->name('destroy')
                ->can('banner.delete');
        });

        #============================== ROUTE POST =============================
        Route::prefix('posts')->as('posts.')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('index');
            Route::get('/create', [PostController::class, 'create'])->name('create')
                ->can('post.create');
            Route::post('/', [PostController::class, 'store'])->name('store')
                ->can('post.create');
            Route::get('/{id}', [PostController::class, 'show'])->name('show');
            Route::get('/edit/{post}', [PostController::class, 'edit'])->name('edit')
                ->can('post.update');
            Route::put('/{post}', [PostController::class, 'update'])->name('update')
                ->can('post.update');
            Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy')
                ->can('post.delete');
        });

        #============================== ROUTE COUPON =============================
        Route::prefix('coupons')->as('coupons.')->group(function () {
            Route::get('/', [CouponController::class, 'index'])->name('index');
            Route::get('/create', [CouponController::class, 'create'])->name('create')
                ->can('coupon.create');
            Route::post('/', [CouponController::class, 'store'])->name('store')
                ->can('coupon.create');
            Route::get('/{id}', [CouponController::class, 'show'])->name('show');
            Route::get('/edit/{coupon}', [CouponController::class, 'edit'])->name('edit');
            Route::put('/{coupon}', [CouponController::class, 'update'])->name('update')
                ->can('coupon.update');
            Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy')
                ->can('coupon.delete');
        });

        #============================== ROUTE SETTINGS =============================
        Route::prefix('settings')->as('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::get('/create', [SettingController::class, 'create'])->name('create')
                ->can('setting.create');
            Route::post('/', [SettingController::class, 'store'])->name('store')
                ->can('setting.create');
            Route::get('/edit/{setting}', [SettingController::class, 'edit'])->name('edit')
                ->can('setting.update');
            Route::put('/{setting}', [SettingController::class, 'update'])->name('update')
                ->can('setting.update');
            Route::delete('/{setting}', [SettingController::class, 'destroy'])->name('destroy')
                ->can('setting.delete');
        });

        #============================== ROUTE SUPPORT BANK =============================
        Route::prefix('support-banks')->as('support-banks.')->group(function () {
            Route::get('/', [SupportBankController::class, 'index'])->name('index');
            Route::get('/create', [SupportBankController::class, 'create'])->name('create')
                ->can('support-bank.create');
            Route::post('/', [SupportBankController::class, 'store'])->name('store')
                ->can('support-bank.create');
            Route::get('/{id}', [SupportBankController::class, 'show'])->name('show');
            Route::get('/edit/{supportBank}', [SupportBankController::class, 'edit'])->name('edit')
                ->can('support-bank.update');
            Route::put('/{supportBank}', [SupportBankController::class, 'update'])->name('update')
                ->can('support-bank.update');
            Route::delete('/{supportBank}', [SupportBankController::class, 'destroy'])->name('destroy')
                ->can('support-bank.delete');
        });
        #============================== ROUTE APPROVAL =============================

        #============================== ROUTE INVOICE =============================


    });
