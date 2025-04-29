<?php

use App\Http\Controllers\Admin\ApprovalMembershipController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\InvoiceMembershipController;
use App\Http\Controllers\Admin\MembershipUserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PostController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\API\Auth\GoogleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SupportBankController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\WithDrawalsRequestController;
use App\Http\Controllers\Admin\ApprovalCourseController;
use App\Http\Controllers\Admin\CommissionController;
use App\Http\Controllers\Admin\AnalyticController;
use App\Http\Controllers\Admin\ApprovalPostController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InstructorCommissionController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\QaSystemController;
use App\Http\Controllers\Admin\RevenueStatisticController;
use App\Http\Controllers\Admin\SpinController;
use App\Http\Controllers\Admin\SpinTypeController;
use App\Http\Controllers\Admin\TopCourseController;
use App\Http\Controllers\Admin\TopInstructorController;
use App\Http\Controllers\Admin\TopStudentController;
use App\Http\Controllers\Admin\WalletController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    Route::get('signup', [AuthController::class, 'signup'])->name('signup');
    Route::get('forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('login', [AuthController::class, 'handleLogin'])->name('handleLogin');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('email', function () {
    return view('emails.auth.verify');
});
Route::get('buyCourse', function () {
    return view('emails.userBuyCourse');
});
Route::get('forgot-password', function () {
    return view('emails.auth.forgot-password');
});


Route::prefix('admin')->as('admin.')
    ->middleware(['roleHasAdmins', 'check_permission:view.dashboard'])
    ->group(function () {
        #============================== ROUTE AUTH =============================
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('verified');
        Route::post('dashboard-export', [DashboardController::class, 'export'])->name('dashboard.export');

        Route::get('administrator-profile', [UserController::class, 'profile'])->name('administrator.profile');

        Route::put('administrator-profile-update/{user}', [UserController::class, 'profileUpdate'])->name('administrator.profileUpdate');

        #============================== ROUTE USER =============================
        Route::prefix('users')->group(function () {
            Route::get('user-clients', [UserController::class, 'index'])->name('clients.index');
            Route::get('user-instructors', [UserController::class, 'index'])->name('instructors.index');
            Route::get('user-employees', [UserController::class, 'index'])->name('employees.index');
            Route::get('user-deleted', [UserController::class, 'index'])->name('users.deleted.index');

            Route::as('users.')->group(function () {
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
                Route::put('/updateEmailVerified/{user}', [UserController::class, 'updateEmailVerified'])->name('updateEmailVerified')
                    ->can('user.update');
                Route::delete('/{user}/force-delete', [UserController::class, 'forceDelete'])
                    ->name('forceDelete')->can('user.update');
                Route::put('/{user}/restore-delete', [UserController::class, 'restoreDelete'])
                    ->name('restoreDelete')->can('user.update');
                Route::post('/import/{role?}', [UserController::class, 'import'])->name('import')
                    ->can('user.create');
                Route::get('export/{role?}', [UserController::class, 'export'])->name('export');
            });
        });

        #============================== ROUTE ROLE =============================
        Route::prefix('roles')->as('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index')
                ->can('role.read');
            Route::get('/create', [RoleController::class, 'create'])->name('create')
                ->can('role.create');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show')
                ->can('role.show');
            Route::post('/', [RoleController::class, 'store'])->name('store')
                ->can('role.create');
            Route::get('/edit/{role}', [RoleController::class, 'edit'])->name('edit')
                ->can('role.edit');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update')
                ->can('role.edit');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy')
                ->can('role.delete');
            Route::post('/import', [RoleController::class, 'import'])->name('import');
        });

        #============================== ROUTE PERMISSION =============================
        Route::prefix('permissions')->as('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index')
                ->can('permission.read');
            Route::get('/create', [PermissionController::class, 'create'])->name('create')
                ->can('permission.create');
            Route::get('/export', [PermissionController::class, 'export'])->name('export');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/edit/{permission}', [PermissionController::class, 'edit'])->name('edit');
            Route::put('/{permission}', [PermissionController::class, 'update'])->name('update')->can('permission.update');
            Route::delete('/{permission}', [PermissionController::class, 'destroy'])->name('destroy')
                ->can('permission.delete');
        });

        #============================== ROUTE CATEGORY =============================
        Route::prefix('categories')->as('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index')->can('category.read');
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
        
        #============================== ROUTE Spin-tpye =============================
        Route::resource('spin-types', SpinTypeController::class);

        #============================== ROUTE COMMENTS =============================
        Route::prefix('comments')->as('comments.')->group(function () {
            Route::get('/', [CommentController::class, 'index'])->name('index')->can('comment.read');
            // Route::get('/{id}', [CommentController::class, 'show'])->name('show');
            Route::get('{comment}/replies', [CommentController::class, 'getReplies'])->name('getReplies');
            Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy')
                ->can('comment.delete');
        });

        #============================== ROUTE BANNER =============================
        Route::prefix('banners')->as('banners.')->group(function () {
            Route::get('/', [BannerController::class, 'index'])->name('index')->can('banners.read');
            Route::get('/deleted', [BannerController::class, 'listDeleted'])->name('deleted');
            Route::get('/create', [BannerController::class, 'create'])->name('create')
                ->can('banners.create');
            Route::post('/', [BannerController::class, 'store'])->name('store')
                ->can('banners.create');
            Route::post('/update-order', [BannerController::class, 'updateOrder'])->name('updateOrder')
                ->can('banners.update');
            Route::get('/{id}', [BannerController::class, 'show'])->name('show');
            Route::get('/edit/{banner}', [BannerController::class, 'edit'])->name('edit');
            Route::put('/{banner}', [BannerController::class, 'update'])->name('update')
                ->can('banners.update');
            Route::delete('/{banner}', [BannerController::class, 'destroy'])->name('destroy')
                ->can('banners.delete');
            Route::put('/{banner}/restore-delete', [BannerController::class, 'restoreDelete'])
                ->name('restoreDelete')->can('banners.update');
            Route::delete('/{banner}/force-delete', [BannerController::class, 'forceDelete'])
                ->name('forceDelete')->can('banners.update');
        });

        #============================== ROUTE POST =============================
        Route::prefix('posts')->as('posts.')->group(function () {
            Route::get('/', [PostController::class, 'index'])->name('index')->can('post.read');
            Route::get('/post-deleted', [PostController::class, 'listPostDelete'])
                ->name('list-post-delete');
            Route::get('/create', [PostController::class, 'create'])->name('create')
                ->can('post.create');
            Route::post('/', [PostController::class, 'store'])->name('store')
                ->can('post.create');
            Route::get('export', [PostController::class, 'export'])->name('export');
            Route::get('/{id}', [PostController::class, 'show'])->name('show');
            Route::get('/edit/{post}', [PostController::class, 'edit'])->name('edit')
                ->can('post.update');
            Route::put('/{post}', [PostController::class, 'update'])->name('update')
                ->can('post.update');
            Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy')
                ->can('post.delete');
            Route::delete('/{post}/force-delete', [PostController::class, 'forceDelete'])
                ->name('forceDelete')->can('post.update');
            Route::put('/{post}/restore-delete', [PostController::class, 'restoreDelete'])
                ->name('restoreDelete')->can('post.update');
        });

        #============================== ROUTE COUPON =============================
        Route::prefix('coupons')->as('coupons.')->group(function () {
            Route::get('/', [CouponController::class, 'index'])->name('index')->can('coupon.read');
            Route::get('/exportCoupon', [CouponController::class, 'exportCoupon'])->name('exportCoupon');
            Route::get('/user-search', [CouponController::class, 'couponUserSearch'])->name('search');
            Route::get('/create', [CouponController::class, 'create'])->name('create')
                ->can('coupon.create');
            Route::get('suggest-coupon-code', [CouponController::class, 'suggestionCounpoun'])->name('suggestCode');
            Route::post('/', [CouponController::class, 'store'])->name('store')
                ->can('coupon.create');
            Route::post('/import', [CouponController::class, 'importFile'])->name('import');
            Route::get('/deleted', [CouponController::class, 'listDeleted'])->name('deleted');
            Route::get('/{id}', [CouponController::class, 'show'])->name('show');
            Route::get('/edit/{coupon}', [CouponController::class, 'edit'])->name('edit');
            Route::put('/{coupon}', [CouponController::class, 'update'])->name('update')
                ->can('coupon.update');
            Route::delete('/{coupon}', [CouponController::class, 'destroy'])->name('destroy')
                ->can('coupon.delete');
            Route::put('/{coupon}/restore-delete', [CouponController::class, 'restoreDelete'])
                ->name('restoreDelete')->can('coupon.update');
            Route::delete('/{coupon}/force-delete', [CouponController::class, 'forceDelete'])
                ->name('forceDelete')->can('coupon.update');
            
        });
        
        #============================== ROUTE SETTINGS =============================
        Route::prefix('settings')->as('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index')->can('setting.read');
            Route::get('/create', [SettingController::class, 'create'])->name('create')
                ->can('setting.create');
            Route::post('/', [SettingController::class, 'store'])->name('store')
                ->can('setting.create');
            Route::get('/edit/{setting}', [SettingController::class, 'edit'])->name('edit')
                ->can('setting.update');
            Route::put('/{setting}', [SettingController::class, 'update'])->name('update')
                ->can('setting.update');
            Route::put('/certificates/{certificateId}', [SettingController::class, 'updateStatusCertificates'])->name('updateStatusCertificates')
                ->can('setting.update');
            Route::delete('/{setting}', [SettingController::class, 'destroy'])->name('destroy')
                ->can('setting.delete');
        });

        #============================== ROUTE instructorCommissions =============================
        Route::prefix('instructor-commissions')->as('instructor-commissions.')->group(function () {
            Route::get('/', [InstructorCommissionController::class, 'index'])->name('index');
            Route::get('/export', [InstructorCommissionController::class, 'export'])->name('export');
            Route::post('/bulk-update', [InstructorCommissionController::class, 'bulkUpdate'])->name('bulk-update');
            Route::put('/update-rate-instructor', [InstructorCommissionController::class, 'updateInstructorCommission'])->name('update');
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

        #============================== ROUTE COMMISSION =============================
        Route::prefix('commissions')->as('commissions.')->group(function () {
            Route::get('/', [CommissionController::class, 'index'])->name('index')->can('commission.read');
            Route::get('/create', [CommissionController::class, 'create'])->name('create')
                ->can('commission.create');
            Route::post('/', [CommissionController::class, 'store'])->name('store');
            Route::get('/{id}', [CommissionController::class, 'show'])->name('show');
            Route::get('/edit/{commission}', [CommissionController::class, 'edit'])->name('edit')
                ->can('commission.update');
            Route::put('/{commission}', [CommissionController::class, 'update'])->name('update')
                ->can('commission.update');
            Route::delete('/{commission}', [CommissionController::class, 'destroy'])->name('destroy')
                ->can('commission.delete');
        });

        #============================== ROUTE COURSES =============================
        Route::prefix('courses')->as('courses.')->group(function () {
            Route::get('/', [CourseController::class, 'index'])->name('index')->can('course.read');
            Route::get('/exportFile', [CourseController::class, 'export'])->name('exportFile');
            Route::get('/{id}', [CourseController::class, 'show'])->name('show');
            Route::get('/{id}/renueveCourse', [CourseController::class, 'renueveCourse'])->name('renueveCourse');
            Route::put('{id}/approve', [CourseController::class, 'approve'])->name('approve');
            Route::put('{id}/reject', [CourseController::class, 'reject'])->name('reject');
            Route::put('{id}/update-popular', [CourseController::class, 'updatePopular'])->name('updatePopular');
        });

        #============================== ROUTE APPROVAL =============================
        Route::prefix('approvals')
            ->as('approvals.')
            ->group(function () {
                Route::prefix('courses')
                    ->as('courses.')
                    ->group(function () {
                        Route::get('/', [ApprovalCourseController::class, 'index'])->name('index')->can('approval.course.read');
                        Route::get('/{course}', [ApprovalCourseController::class, 'show'])->name('show');
                        Route::put('/{course}', [ApprovalCourseController::class, 'approve'])->name('approve')->can('approval.course.approve');
                        Route::put('/{course}/reject', [ApprovalCourseController::class, 'reject'])->name('reject')->can('approval.course.reject');
                        Route::put('/{course}/approve-modify-request', [ApprovalCourseController::class, 'approveModifyRequest'])->name('approve-modify-request')->can('approval.course.approve-modify-request');
                        Route::put('/{course}/reject-modify-request', [ApprovalCourseController::class, 'rejectModifyRequest'])->name('reject-modify-request')->can('approval.course.reject-modify-request');
                    });

                Route::prefix('instructors')
                    ->as('instructors.')
                    ->group(function () {
                        Route::get('/', [\App\Http\Controllers\Admin\ApprovalInstructorController::class, 'index'])->name('index')->can('approval.instructor.read');
                        Route::get('export', [\App\Http\Controllers\Admin\ApprovalInstructorController::class, 'export'])->name('export');
                        Route::get('/{instructor}', [\App\Http\Controllers\Admin\ApprovalInstructorController::class, 'show'])->name('show');
                        Route::put('/{instructor}', [\App\Http\Controllers\Admin\ApprovalInstructorController::class, 'approve'])->name('approve')->can('approval.instructor.approve');
                        Route::put('/{instructor}/reject', [\App\Http\Controllers\Admin\ApprovalInstructorController::class, 'reject'])->name('reject')->can('approval.instructor.reject');
                    });

                Route::prefix('posts')
                    ->as('posts.')
                    ->group(function () {
                        Route::get('/', [ApprovalPostController::class, 'index'])->name('index')->can('approval.post.read');
                        Route::get('/{post}', [ApprovalPostController::class, 'show'])->name('show');
                        Route::put('/{post}', [ApprovalPostController::class, 'approve'])->name('approve')->can('approval.post.approve');
                        Route::put('/{post}/reject', [ApprovalPostController::class, 'reject'])->name('reject')->can('approval.post.reject');
                    });

                Route::prefix('memberships')
                    ->as('memberships.')
                    ->group(function () {
                        Route::get('/', [ApprovalMembershipController::class, 'index'])->name('index')->can('approval.membership.read');
                        Route::get('/{membership}', [ApprovalMembershipController::class, 'show'])->name('show');
                        Route::get('/{id}/courses', [ApprovalMembershipController::class, 'getCourses'])->name('courses');
                        Route::put('/{membership}', [ApprovalMembershipController::class, 'approve'])->name('approve')->can('approval.membership.approve');
                        Route::put('/{membership}/reject', [ApprovalMembershipController::class, 'reject'])->name('reject')->can('approval.membership.reject');
                    });
            });

        #============================== ROUTE INVOICE =============================
        Route::prefix('invoices')->as('invoices.')->group(function () {
            Route::prefix('memberships')->group(function () {
                Route::get('/', [InvoiceMembershipController::class, 'index'])->name('memberships.index')->can('invoice.membership.read');
                Route::get('/{code}', [InvoiceMembershipController::class, 'show'])->name('memberships.show');
            });

            Route::get('/', [InvoiceController::class, 'index'])->name('index')->can('invoice.read');
            Route::get('export', [InvoiceController::class, 'export'])->name('export');
            Route::get('/{code}', [InvoiceController::class, 'show'])->name('show');
        });

        #============================== ROUTE memberships =============================
        Route::prefix('memberships')->as('memberships.')->group(function () {
            Route::get('/', [MembershipUserController::class, 'index'])->name('index');
        });

        Route::prefix('spins')->as('spins.')->group(function () {
            Route::get('/', [SpinController::class, 'index'])->name('index')->can('spin.read');
            Route::post('/spin-config/store', [SpinController::class, 'storeSpinConfig'])->name('spin-config.store');
            Route::put('/spin-configs/{id}', [SpinController::class, 'updateSpinConfig'])->name('spin-config.update');
            Route::post('/gifts', [SpinController::class, 'addGift'])->name('gift.store');
            Route::put('/gifts/{id}', [SpinController::class, 'updateGift'])->name('gift.update');
            Route::delete('/gifts/{id}', [SpinController::class, 'deleteGift'])->name('gift.delete');
            Route::delete('/spin-config/delete/{id}', [SpinController::class, 'deleteSpinConfig'])->name('deleteSpinConfig');
            Route::post('/spin/toggle-selection/{type}/{id}', [SpinController::class, 'toggleSelection'])->name('toggle-selection');
            Route::post('/spin/toggle-status', [SpinController::class, 'toggleSpinStatus'])->name('toggle-status');
        });

        #============================== ROUTE WITH DRAWALS =============================
        Route::prefix('withdrawals')
            ->as('withdrawals.')
            ->group(function () {

                Route::get('/', [WithDrawalsRequestController::class, 'index'])->name('index')->can('withdrawal.read');
                Route::get('export', [WithDrawalsRequestController::class, 'export'])->name('export');
                Route::get('/{withdrawal}', [WithDrawalsRequestController::class, 'show'])->name('show');
                Route::post('/confirm-payment', [WithDrawalsRequestController::class, 'confirmPayment'])->name('confirmPayment')->can('withdrawal.update');
                Route::post('/check-status', [WithDrawalsRequestController::class, 'checkStatus'])->name('check-status')->can('withdrawal.update');
            });

        #============================== ROUTE TRANSACTIONS =============================
        Route::prefix('transactions')
            ->as('transactions.')
            ->group(function () {
                Route::get('/', [TransactionController::class, 'index'])->name('index')->can('transaction.read');
                Route::get('export', [TransactionController::class, 'export'])->name('export');
                Route::get('/{transaction}', [TransactionController::class, 'show'])->name('show');
                Route::get('/check-transaction', [TransactionController::class, 'checkTransaction'])
                    ->name('check-transaction');
            });

        #============================== ROUTE ANALYTICS =============================
        Route::get('/analytics', [AnalyticController::class, 'index'])
            ->name('analytics.index')->can('analytic.read');

        #============================== ROUTE REVENUE STATISTICS =============================
        Route::get('/revenue-statistics', [RevenueStatisticController::class, 'index'])
            ->name('revenue-statistics.index');

        Route::post('/revenue-statistics/export', [RevenueStatisticController::class, 'export'])
            ->name('revenue-statistics.export');
        #============================== ROUTE TOP INSTRUCTOR =============================
        Route::get('/top-instructors', [TopInstructorController::class, 'index'])
            ->name('top-instructors.index');
        #============================== ROUTE TOP STUDENT =============================
        Route::get('/top-students', [TopStudentController::class, 'index'])
            ->name('top-students.index');
        #============================== ROUTE TOP COURSE =============================
        Route::get('/top-courses', [TopCourseController::class, 'index'])
            ->name('top-courses.index')->can('top-course.read');

        #============================== ROUTE NOTIFICATIONS =============================
        Route::prefix('notifications')
            ->as('notifications.')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])
                    ->name('index');
                Route::get('/all-notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'allNotification'])
                    ->name('all-notifications');
                Route::get('/unread-count', [\App\Http\Controllers\Admin\NotificationController::class, 'getUnreadNotificationsCount'])
                    ->name('unread-count');
                Route::put('/{notificationId}', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])
                    ->name('markAsRead');
                Route::delete('/{notification}/force-delete', [\App\Http\Controllers\Admin\NotificationController::class, 'forceDelete'])
                    ->name('forceDelete');
            });

        #============================== ROUTE QA SYSTEM =============================
        Route::prefix('qa-systems')
            ->as('qa-systems.')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\QaSystemController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\QaSystemController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\QaSystemController::class, 'store'])->name('store');
                Route::post('/import', [QaSystemController::class, 'importFile'])->name('import');
                Route::get('/exportFile', [QaSystemController::class, 'export'])->name('exportFile');
                Route::get('/edit/{qaSystem}', [\App\Http\Controllers\Admin\QaSystemController::class, 'edit'])->name('edit');
                Route::put('/{qaSystem}', [\App\Http\Controllers\Admin\QaSystemController::class, 'update'])->name('update');
                Route::delete('/{qaSystem}', [\App\Http\Controllers\Admin\QaSystemController::class, 'destroy'])->name('destroy');
            });

        #============================== ROUTE QA SYSTEM =============================
        Route::prefix('wallets')
            ->as('wallets.')
            ->group(function () {
                Route::get('/', [WalletController::class, 'index'])->name('index');
                Route::get('/{wallet}', [WalletController::class, 'show'])->name('show');
            });
        #============================== ROUTE CHAT-REALTIME =============================
        Route::prefix('chats')
            ->as('chats.')
            ->group(function () {
                Route::get('/chat-room', [ChatController::class, 'index'])->name('index');
                Route::post('/private', [ChatController::class, 'createPrivateChat'])->name('createOnetoOne');
                Route::post('/chat-room', [ChatController::class, 'createGroupChat'])->name('create');
                Route::get('/get-group-info', [ChatController::class, 'getGroupInfo'])->name('getGroupInfo');
                Route::get('/get-user-info', [ChatController::class, 'getUserInfo'])->name('getUserInfo');
                Route::post('/send-message', [ChatController::class, 'sendGroupMessage'])->name('sendGroupMessage');
                Route::get('/get-messages/{conversationId}', [ChatController::class, 'getGroupMessages'])->name('getGroupMessages');
                Route::get('/get-sent-files/{conversationId}', [ChatController::class, 'getSentFiles']);
                Route::post('/add-members-to-group', [ChatController::class, 'addMembersToGroup']);
                Route::post('/conversation/{conversationId}/leave', [ChatController::class, 'leaveConversation'])->name('leaveConversation');
                Route::delete('/conversation/{conversationId}/delete', [ChatController::class, 'deleteConversation'])->name('deleteConversation');
                Route::post('/kick-member', [ChatController::class, 'kickUserFromGroup'])->name('kickUserFromGroup');
                Route::post('/dissolve-group', [ChatController::class, 'dissolveGroup'])->name('dissolveGroup');
            });

        Route::post('chat/notify-inactive-users', [ChatController::class, 'getUserJoinRoom'])->name('getUserJoinRoom');
        Route::post('user-status', [ChatController::class, 'getUserOnline'])->name('getUserOnline');
        Route::post('clear-currency-conversation', [ChatController::class, 'clearCurrentChat'])->name('clear-currency-conversation');
    });
