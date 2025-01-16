<?php

use App\Http\Controllers\API\Document\DocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\GoogleController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Transaction\TransactionController;
use App\Http\Controllers\API\Instructor\CourseController;
use App\Http\Controllers\API\Instructor\ChapterController;
use App\Http\Controllers\API\Instructor\RegisterController;
use App\Http\Controllers\API\Instructor\LessonController;
use App\Http\Controllers\API\Posts\PostController;
use App\Http\Controllers\API\Search\SearchController;
use App\Http\Controllers\API\Instructor\SupportBankController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/livestream', [\App\Http\Controllers\API\LivestreamController::class, 'createLiveStream']);

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
Route::get('search', [SearchController::class, 'search']);

Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->as('auth.')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
        });

        Route::prefix('instructor')->as('instructor.')->group(function () {
            Route::post('register', [RegisterController::class, 'register']);
        });

        Route::prefix('instructor')
            ->middleware('roleHasInstructor')
            ->as('instructor.')
            ->group(function () {
                #============================== ROUTE COURSE =============================
                Route::prefix('courses')
                    ->as('courses.')
                    ->group(function () {
                        Route::get('/', [CourseController::class, 'index']);
                        Route::get('/{slug}', [CourseController::class, 'getCourseOverView']);
                        Route::post('/', [CourseController::class, 'store']);
                        Route::put('/{slug}/contentCourse', [CourseController::class, 'updateContentCourse']);
                        Route::delete('/{slug}', [CourseController::class, 'deleteCourse']);
                    });

                #============================== ROUTE CHAPTER =============================
                Route::prefix('chapters')
                    ->as('chapters.')
                    ->group(function () {
                        Route::post('/', [ChapterController::class, 'storeChapter']);
                        Route::put('/{slug}/update-order', [ChapterController::class, 'updateOrderChapter']);
                        Route::put('/{slug}/{chapterId}', [ChapterController::class, 'updateContentChapter']);
                        Route::delete('/{slug}/{chapterId}', [ChapterController::class, 'deleteChapter']);
                    });

                #============================== ROUTE LESSON =============================
                Route::prefix('lessons')
                    ->as('lessons.')
                    ->group(function () {
                        Route::post('/', [LessonController::class, 'storeLesson']);
                        Route::put('/{slug}/update-order', [LessonController::class, 'updateOrderLesson']);
                        Route::put('/{slug}/{lessonId}', [LessonController::class, 'updateContentLesson']);
                        Route::delete('/{slug}/{lessonId}', [LessonController::class, 'deleteLesson']);
                    });
            });

        #============================== ROUTE POST =============================
        Route::prefix('posts')->as('posts.')->group(function () {
            Route::get('/', [PostController::class, '']);
        });

        #============================== ROUTE DOCUMENT =============================
        Route::prefix('documents')->as('documents.')->group(function () {
            Route::get('/', [DocumentController::class, 'index']);
            Route::get('/{documentID}', [DocumentController::class, 'show']);
            Route::post('/', [DocumentController::class, 'store']);
            Route::put('/{documentID}', [DocumentController::class, 'update']);
            Route::delete('/{documentID}', [DocumentController::class, 'destroy']);
        });

        #============================== ROUTE TRANSACTION =============================
        Route::prefix('transactions')->as('transactions.')->group(function () {
            Route::get('/', [TransactionController::class, 'index']);
            Route::get('/{transactionID}', [TransactionController::class, 'show']);
            Route::post('/deposit', [TransactionController::class, 'deposit']);
            Route::post('/buyCourse', [TransactionController::class, 'buyCourse']);
        });
    });