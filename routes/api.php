<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\GoogleController;
use App\Http\Controllers\API\Common\BannerController;
use App\Http\Controllers\API\Common\PostController;
use App\Http\Controllers\API\Common\SearchController;
use App\Http\Controllers\API\Common\TransactionController;
use App\Http\Controllers\API\Common\UserController;
use App\Http\Controllers\API\Instructor\ChapterController;
use App\Http\Controllers\API\Instructor\CourseController;
use App\Http\Controllers\API\Instructor\DocumentController;
use App\Http\Controllers\API\Instructor\LessonController;
use App\Http\Controllers\API\Instructor\LivestreamController;
use App\Http\Controllers\API\Instructor\RegisterController;
use App\Http\Controllers\API\Instructor\SupportBankController;
use App\Http\Controllers\API\Note\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

#============================== ROUTE AUTH =============================
Route::prefix('auth')->as('auth.')->group(function () {
    Route::post('sign-up', [AuthController::class, 'signUp']);
    Route::post('sign-in', [AuthController::class, 'signIn']);
    Route::post('verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('register-instructor', [AuthController::class, 'registerInstructor']);

    Route::get('google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

#============================== ROUTE SEARCH =============================
Route::prefix('search')
    ->group(function () {
        Route::get('/', [SearchController::class, 'search']);
    });

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('auth')->as('auth.')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::get('user', function (Request $request) {
        return $request->user();
    });

    #============================== ROUTE USER =============================
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'showProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/change-password', [UserController::class, 'changePassword']);
        Route::get('/my-course-bought', [UserController::class, 'getMyCourseBought']);

        #============================== ROUTE NOTIFICATION =============================
        Route::prefix('notifications')
            ->group(function () {

            });

    });

    #============================== ROUTE LEARNING =============================
    Route::prefix('learning-path')
        ->group(function () {

        });

    #============================== ROUTE INSTRUCTOR MANAGE =============================
    Route::prefix('instructor')->as('instructor.')->group(function () {
        Route::post('register', [RegisterController::class, 'register']);
    });

    Route::prefix('instructor')
        ->middleware('roleHasInstructor')
        ->as('instructor.')
        ->group(function () {
            Route::prefix('statistics')
                ->group(function () {

                });

            Route::prefix('manage')
                ->group(function () {
                    #============================== ROUTE COURSE =============================
                    Route::prefix('courses')
                        ->group(function () {
                            Route::get('/', [CourseController::class, 'index']);
                            Route::get('/{course}', [CourseController::class, 'getCourseOverView']);
                            Route::post('/', [CourseController::class, 'store']);
                            Route::put('/{course}/contentCourse', [CourseController::class, 'updateContentCourse']);
                            Route::delete('/{course}', [CourseController::class, 'deleteCourse']);
                            Route::get('/{slug}/chapters', [CourseController::class, 'getChapters']);
                        });

                    #============================== ROUTE CHAPTER =============================
                    Route::prefix('chapters')
                        ->as('chapters.')
                        ->group(function () {
                            Route::post('/', [ChapterController::class, 'storeChapter']);
                            Route::put('/{chapter}/update-order', [ChapterController::class, 'updateOrderChapter']);
                            Route::put('/{chapter}', [ChapterController::class, 'updateContentChapter']);
                            Route::delete('/{chapter}', [ChapterController::class, 'deleteChapter']);
                            Route::get('/{chapter}/lessons', [ChapterController::class, 'getLessons']);
                        });

                    #============================== ROUTE LESSON =============================
                    Route::prefix('lessons')
                        ->as('lessons.')
                        ->group(function () {
                            Route::post('/', [LessonController::class, 'storeLesson']);
                            Route::put('/{lesson}/update-order', [LessonController::class, 'updateOrderLesson']);
                            Route::put('/{lesson}', [LessonController::class, 'updateContentLesson']);
                            Route::delete('/{lesson}', [LessonController::class, 'deleteLesson']);
                        });
                });
        });

    #============================== ROUTE NOTE =============================
    Route::prefix('notes')->as('notes.')->group(function () {
        Route::get('/', [NoteController::class, 'index']);
        Route::get('/{NoteID}', [NoteController::class, 'show']);
        Route::post('/', [NoteController::class, 'store']);
        Route::put('/{NoteID}', [NoteController::class, 'update']);
        Route::delete('/{NoteID}', [NoteController::class, 'destroy']);
    });

    #============================== ROUTE DOCUMENT =============================
    Route::prefix('documents')->as('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index']);
        Route::get('/{documentID}', [DocumentController::class, 'show']);
        Route::post('/', [DocumentController::class, 'store']);
        Route::put('/{documentID}', [DocumentController::class, 'update']);
        Route::delete('/{documentID}', [DocumentController::class, 'destroy']);
    });

    #============================== ROUTE COUPON =============================
    Route::prefix('coupons')->as('coupons.')->group(function () {

    });

    #============================== ROUTE TRANSACTION =============================
    Route::prefix('transactions')->as('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{transactionID}', [TransactionController::class, 'show']);
        Route::post('/deposit', [TransactionController::class, 'deposit']);
        Route::post('/buyCourse', [TransactionController::class, 'buyCourse']);
    });

    #============================== ROUTE CHAT =============================
    Route::prefix('chats')
        ->group(function () {

        });

    #============================== ROUTE COMMENT =============================
    Route::prefix('comments')
        ->group(function () {

        });

    #============================== ROUTE RATING =============================
    Route::prefix('ratings')
        ->group(function () {

        });

    #============================== ROUTE LIVESTREAM =============================
    Route::prefix('livestreams')
        ->group(function () {
            Route::post('/', [LivestreamController::class, 'createLiveStream']);
        });
});

#============================== ROUTE COURSE =============================
Route::prefix('courses')
    ->group(function () {

    });

#============================== ROUTE POST =============================
Route::prefix('posts')->as('posts.')->group(function () {
    Route::get('/', [PostController::class, '']);
});

#============================== ROUTE BANNER =============================
Route::get('/banners', [BannerController::class, 'index']);

#============================== ROUTE CATEGORY =============================
Route::get('/categories', [CategoryController::class, 'index']);

#============================== ROUTE SUPPORT BANK =================================
Route::prefix('support-banks')->group(function () {
    Route::post('/', [SupportBankController::class, 'index']);
    Route::post('/generate-qr', [SupportBankController::class, 'generateQR']);
});