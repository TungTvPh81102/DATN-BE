<?php

use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\Auth\GoogleController;
use App\Http\Controllers\API\Common\BannerController;
use App\Http\Controllers\API\Common\BlogController;
use App\Http\Controllers\API\Common\CommonController;
use App\Http\Controllers\API\Common\CouponController;
use App\Http\Controllers\API\Common\CourseController as CommonCourseController;
use App\Http\Controllers\API\Common\FilterController;
use App\Http\Controllers\API\Common\FollowController;
use App\Http\Controllers\API\Common\RatingController;
use App\Http\Controllers\API\Common\ReactionController;
use App\Http\Controllers\API\Common\SearchController;
use App\Http\Controllers\API\Common\SpinController;
use App\Http\Controllers\API\Common\TagController;
use App\Http\Controllers\API\Common\TransactionController;
use App\Http\Controllers\API\Common\UserController;
use App\Http\Controllers\API\Common\WishListController;
use App\Http\Controllers\API\Instructor\ChapterController;
use App\Http\Controllers\API\Instructor\CouponController as InstructorCouponController;
use App\Http\Controllers\API\Instructor\CourseController;
use App\Http\Controllers\API\Instructor\DocumentController;
use App\Http\Controllers\API\Instructor\LessonController;
use App\Http\Controllers\API\Instructor\LivestreamController;
use App\Http\Controllers\API\Instructor\PostController;
use App\Http\Controllers\API\Instructor\RegisterController;
use App\Http\Controllers\API\Instructor\SendRequestController;
use App\Http\Controllers\API\Instructor\StatisticController;
use App\Http\Controllers\API\Instructor\SupportBankController;
use App\Http\Controllers\API\Instructor\TopInstructorController;
use App\Http\Controllers\API\Student\CertificateController;
use App\Http\Controllers\API\Student\NoteController;
use App\Http\Controllers\API\Verify\VerificationController;
use App\Models\Reaction;
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
    Route::get('verify/{token}', [AuthController::class, 'verify']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    Route::get('google', [GoogleController::class, 'redirectToGoogle']);
    Route::get('google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

Route::post('/chat-box', [CommonController::class, 'chatBox']);

Route::get('/vnpay-callback', [TransactionController::class, 'vnpayCallback']);
Route::get('/momo-callback', [TransactionController::class, 'momoCallback']);

Route::prefix('livestreams')->group(function () {
    Route::get('/', [LivestreamController::class, 'index']);
    Route::get('/{livestream}', [LivestreamController::class, 'show']);
    Route::post('/{livestream}/join', [LivestreamController::class, 'joinLiveSession'])
        ->middleware('optionalAuth');
    Route::post('/{livestream}/leave', [LivestreamController::class, 'leave']);
});

Route::get('/reset-password/{token}', function ($token) {
    return view('emails.auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

#============================== ROUTE SEARCH =============================
Route::prefix('search')
    ->group(function () {
        Route::get('/', [SearchController::class, 'search']);
    });

Route::prefix('filters')
    ->group(function () {
        Route::get('/', [FilterController::class, 'filter']);
    });

Route::get('/top-instructors', [TopInstructorController::class, 'index']);

Route::get('get-followers-count/{intructorCode}', [FollowController::class, 'getFollowersCount']);

Route::get('/{{ Auth::user()->code }}/get-validate-instructor');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/broadcasting/auth', [\App\Http\Controllers\API\Common\BroadcastController::class, 'authenticate']);

    Route::post('/send-notification', [\App\Http\Controllers\NotificationController::class, 'sendNotification']);

    Route::post('/create-payment', [TransactionController::class, 'createPayment']);

    Route::prefix('auth')->as('auth.')->group(function () {
        Route::get('get-user-with-token', [AuthController::class, 'getUserWithToken']);
        Route::get('online-users', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiGetOnlineUsers']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    Route::prefix('instructor')->as('instructor.')->group(function () {
        Route::post('register', [RegisterController::class, 'register']);
        Route::post('check-handle-role', [RegisterController::class, 'checkHandleRole']);
    });

    Route::get('user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('livestreams')->group(function () {
        Route::post('/{livestream}/send-message', [LivestreamController::class, 'sendMessage']);
    });

    Route::get('/instructors/{code}/follow-status', [FollowController::class, 'checkUserFollower']);

    #============================== ROUTE USER =============================
    Route::prefix('users')->group(function () {
        Route::get('check-profile', [\App\Http\Controllers\API\Common\CommonController::class, 'getCheckProfileUser']);
        Route::get('/profile', [UserController::class, 'showProfile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/change-password', [UserController::class, 'changePassword']);
        Route::get('/my-course-bought', [UserController::class, 'getMyCourseBought']);
        Route::get('/my-courses', [UserController::class, 'getUserCourses']);
        Route::get('/courses/{slug}/progress', [UserController::class, 'getCourseProgress']);
        Route::get('/recentCourse', [UserController::class, 'getRecentCourses']);
        Route::get('/orders', [UserController::class, 'getOrdersBought']);
        Route::get('/orders/{id}', [UserController::class, 'showOrdersBought']);
        Route::get('/coupons', [UserController::class, 'getCouponUser']);
        Route::get('/courses/{slug}/certificate', [UserController::class, 'downloadCertificate']);
        Route::get('/certificates', [UserController::class, 'getCertificate']);

        Route::put('follow/{intructorCode}', [FollowController::class, 'follow']);

        Route::get('/get-banking-info', [UserController::class, 'getBankingInfos']);
        Route::post('/add-banking-info', [UserController::class, 'addBankingInfo']);
        Route::put('/update-banking-info', [UserController::class, 'updateBankingInfo']);
        Route::put('/set-default', [UserController::class, 'setDefaultBankingInfo']);
        Route::delete('/remove-banking-info', [UserController::class, 'removeBankingInfo']);

        #============================== ROUTE CAREERS =============================
        Route::prefix('careers')->group(function () {
            Route::post('/', [UserController::class, 'storeCareers']);
            Route::put('/{careerID}', [UserController::class, 'updateCareers']);
            Route::delete('/{careerID}', [UserController::class, 'deleteCareers']);
        });

        #============================== ROUTE CERTIFICATES =============================
        Route::get('/certificate/{slug}', [CertificateController::class, 'generateCertificate']);
        Route::delete('/remove-certificates', [UserController::class, 'removeCertificate']);

        #============================== ROUTE NOTIFICATION =============================
        Route::prefix('notifications')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\API\Common\NotificationController::class, 'getNotifications']);
                Route::put('/{id}/read', [\App\Http\Controllers\API\Common\NotificationController::class, 'markAsRead']);
            });

        #============================== ROUTE MEMBERSHIP =============================
        Route::prefix('memberships')->group(function () {
            Route::get('/', [UserController::class, 'getMembershipPlanList']);
        });
    });

    #============================== ROUTE LEARNING =============================
    Route::prefix('learning-paths')->as('learning-paths.')->group(function () {
        Route::get('/{slug}/lesson', [\App\Http\Controllers\API\Common\LearningPathController::class, 'getLessons']);
        Route::get('/{slug}/lesson/{lesson}', [\App\Http\Controllers\API\Common\LearningPathController::class, 'show']);
        Route::put('/lesson/{lessonId}/update-last-time-video', [\App\Http\Controllers\API\Common\LearningPathController::class, 'updateLastTimeVideo']);
        Route::patch('/lesson/{lessonId}/complete-lesson', [\App\Http\Controllers\API\Common\LearningPathController::class, 'completeLesson']);
        Route::patch('/lesson/{lessonId}/complete-practice-exercise', [\App\Http\Controllers\API\Common\LearningPathController::class, 'completePracticeExercise']);
        Route::get('/{lesson}/get-chapter-from-lesson', [LessonController::class, 'getChapterFromLesson']);
        Route::get('/lesson/{lessonId}/get-quiz-submission/{submissionQuizId}/', [\App\Http\Controllers\API\Common\LearningPathController::class, 'getQuizSubmission']);
        Route::get('/lesson/{lessonId}/get-coding-submission/{submissionCodingId}/', [\App\Http\Controllers\API\Common\LearningPathController::class, 'getCodingSubmission']);
        Route::get('/draft/{slug}', [\App\Http\Controllers\API\Common\LearningPathController::class, 'getLearningPathDraft']);
    });

    Route::prefix('lessons')
        ->group(function () {
            Route::prefix('comments')
                ->group(function () {
                    Route::get('/{lesson}/lesson-comment', [\App\Http\Controllers\API\Common\CommentLessonController::class, 'getCommentLesson']);
                    Route::post('/store-lesson-comment', [\App\Http\Controllers\API\Common\CommentLessonController::class, 'storeCommentLesson']);
                    Route::get('{comment}/replies', [\App\Http\Controllers\API\Common\CommentLessonController::class, 'getReplies']);
                    Route::post('/{comment}/reply', [\App\Http\Controllers\API\Common\CommentLessonController::class, 'reply']);
                    Route::delete('/{comment}', [\App\Http\Controllers\API\Common\CommentLessonController::class, 'deleteComment']);
                    Route::get('{comment}/replies', [\App\Http\Controllers\API\Common\CommentLessonController::class, 'getReplies']);
                    Route::get('/comment-block-time', [\App\Http\Controllers\API\Common\CommentLessonController::class, 'getCommentBlockTime']);
                    Route::post('/{comment}/reply', [\App\Http\Controllers\API\Common\CommentLessonController::class, 'reply']);
                });
        });

    #============================== ROUTE WISH LIST =============================
    Route::prefix('wish-lists')->as('wish-lists.')->group(function () {
        Route::get('/', [WishListController::class, 'index']);
        Route::post('/', [WishListController::class, 'store']);
        Route::delete('/{wishListID}', [WishListController::class, 'destroy']);
    });

    #============================== ROUTE SPIN LUCKY =============================
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('spins')->as('spins.')->group(function () {
            // Quay vòng quay may mắn
            Route::post('/spin', [SpinController::class, 'spin'])->name('spin');

            // Lấy số lượt quay còn lại của người dùng
            Route::get('/user/turn', [SpinController::class, 'getSpins'])->name('user.spins');

            // Cập nhật membership và tặng lượt quay
            Route::post('/user/update-membership', [SpinController::class, 'updateMembership'])->name('user.update-membership');

            // Hoàn thành hồ sơ và tặng lượt quay
            Route::post('/user/complete-profile', [SpinController::class, 'completeProfile'])->name('user.complete-profile');

            // Hoàn thành khóa học và tặng lượt quay
            Route::post('/user/complete-course', [SpinController::class, 'completeCourse'])->name('user.complete-course');

            // Lấy lịch sử quay của người dùng
            Route::get('/user/spin-history', [SpinController::class, 'getSpinHistory'])->name('user.spin-history');

            // Lấy danh sách phần thưởng có thể trúng
            Route::get('/rewards', [SpinController::class, 'getAvailableRewards'])->name('rewards');

            //Kiểm tra trạng thái vòng quay
            Route::get('/status', [SpinController::class, 'getSpinStatus']);
        });
    });
    #============================== ROUTE TRANSACTION =============================
    Route::prefix('transactions')->as('transactions.')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/{transactionID}', [TransactionController::class, 'show']);
        Route::post('/deposit', [TransactionController::class, 'deposit']);
        Route::post('/apply-coupon', [TransactionController::class, 'applyCoupon']);
        Route::post('/buyCourse', [TransactionController::class, 'buyCourse']);
        Route::post('/enroll-free-course', [TransactionController::class, 'enrollFreeCourse']);
        Route::delete('/delete-apply-coupon', [TransactionController::class, 'deleteApplyCoupon']);
    });

    #============================== ROUTE LEARNING =============================
    Route::prefix('learning-path')
        ->group(function () {});

    Route::prefix('support-banks')->group(function () {
        Route::get('/', [SupportBankController::class, 'index']);
    });

    #============================== ROUTE INSTRUCTOR MANAGE =============================
    Route::prefix('instructor')
        ->middleware('roleHasInstructor')
        ->as('instructor.')
        ->group(function () {
            Route::prefix('statistics')
                ->group(function () {
                    Route::get('/get-course-overview', [StatisticController::class, 'getCourseOverview']);
                    Route::get('/get-course-revenue', [StatisticController::class, 'getCourseRevenue']);
                    Route::get('/get-month-revenue', [StatisticController::class, 'getMonthlyRevenue']);
                    Route::get('/get-monthly-course-statistics', [StatisticController::class, 'getMonthlyCourseStatistics']);
                    Route::get('/get-rating-stats', [StatisticController::class, 'getRatingStats']);
                    Route::get('/get-total-sales-by-month', [StatisticController::class, 'getTotalSalesByMonth']);
                    Route::get('/get-monthly-memberships-revenue', [StatisticController::class, 'getRevenueMembershipsByMonth']);
                    Route::get('/ratings', [StatisticController::class, 'getRatingsStatistics']);
                    Route::get('/follows', [StatisticController::class, 'getFollowsStatistics']);
                });

            #============================== ROUTE MEMBERSHIP PLAN =================================
            Route::prefix('member-ship-plans')->group(function () {
                Route::get('/', [\App\Http\Controllers\API\Instructor\MemberShipPlanController::class, 'getMemberShipPlans']);
                Route::get('/{code}', [\App\Http\Controllers\API\Instructor\MemberShipPlanController::class, 'getMemberShipPlan']);
                Route::post('/', [\App\Http\Controllers\API\Instructor\MemberShipPlanController::class, 'storeMemberShipPlan']);
                Route::post('/send-request-membership-plan/{code}', [\App\Http\Controllers\API\Instructor\MemberShipPlanController::class, 'sendRequestMembershipPlan']);
                Route::put('/{code}', [\App\Http\Controllers\API\Instructor\MemberShipPlanController::class, 'updateMemberShipPlan']);
                Route::put('/{code}/{action}', [\App\Http\Controllers\API\Instructor\MemberShipPlanController::class, 'toggleStatus'])
                    ->where('action', 'enable|disable');
            });

            #============================== ROUTE SUPPORT BANK =================================
            Route::prefix('support-banks')->group(function () {
                Route::get('/', [SupportBankController::class, 'index']);
                Route::post('/generate-qr', [SupportBankController::class, 'generateQR']);
            });

            #============================== ROUTE WALLET =============================
            Route::prefix('wallet')
                ->group(function () {
                    Route::get('/', [\App\Http\Controllers\API\Instructor\WalletController::class, 'getWallet']);
                    Route::get('/withdraw-requests', [\App\Http\Controllers\API\Instructor\WalletController::class, 'getWithdrawalRequests']);
                    Route::get('/withdraw-request/{withdrawalRequest}', [\App\Http\Controllers\API\Instructor\WalletController::class, 'getWithDrawRequest']);
                    Route::put('/withdraw-request/{withdrawalRequest}/handleConfirm', [\App\Http\Controllers\API\Instructor\WalletController::class, 'handleConfirmWithdrawal']);
                    Route::post('/withdraw-request', [\App\Http\Controllers\API\Instructor\WalletController::class, 'withDrawRequest']);
                });

            #============================== ROUTE LIVESTREAM =============================
            Route::prefix('livestreams')
                ->group(function () {
                    Route::get('/', [LivestreamController::class, 'getLivestreams']);
                    Route::post('/', [LivestreamController::class, 'startLivestream']);
                });

            #============================== ROUTE FEEDBACK =============================
            Route::prefix('feedbacks')
                ->group(function () {
                    Route::get('/', [\App\Http\Controllers\API\Instructor\FeedBackController::class, 'getFeedbacks']);
                });

            Route::prefix('manage')
                ->group(function () {
                    #============================== ROUTE MEDIA =============================
                    Route::get('/get-upload-url', [CommonController::class, 'getUploadUrl']);
                    Route::get('/get-video-info/{uploadId}', [CommonController::class, 'getInfoVideo']);
                    Route::get('/media', [CommonController::class, 'getMedia']);

                    #============================== ROUTE LEARNER  =============================
                    Route::prefix('learners')
                        ->group(function () {
                            Route::get('/', [\App\Http\Controllers\API\Instructor\LearnerController::class, 'index']);
                            Route::get('/{learner}', [\App\Http\Controllers\API\Instructor\LearnerController::class, 'getLearnerProgress']);
                        });

                    #============================== ROUTE COURSE =============================
                    Route::prefix('courses')
                        ->group(function () {
                            Route::get('/', [CourseController::class, 'index']);
                            Route::get('/course-approved', [CourseController::class, 'courseApproved']);
                            Route::get('/trash', [CourseController::class, 'trash']);
                            Route::get('/{course}', [CourseController::class, 'getCourseOverView']);
                            Route::get('/{course}/course-list-of-user', [CourseController::class, 'courseListOfUser']);
                            Route::post('/', [CourseController::class, 'store']);
                            Route::put('/{course}/courseOverView', [CourseController::class, 'updateCourseOverView']);
                            Route::put('/{course}/courseObjective', [CourseController::class, 'updateCourseObjectives']);
                            Route::get('/{slug}/chapters', [CourseController::class, 'getChapters']);
                            Route::get('/{slug}/validate-course', [CourseController::class, 'validateCourse']);
                            Route::get('/{slug}/check-course-complete', [CourseController::class, 'checkCourseComplete']);
                            Route::post('{slug}/submit-course', [SendRequestController::class, 'submitCourse']);
                            Route::post('request-modify-content', [SendRequestController::class, 'requestToModifyContent']);
                            Route::delete('/move-to-trash', [CourseController::class, 'moveToTrash']);
                            Route::post('/restore', [CourseController::class, 'restore']);
                        });

                    #============================== ROUTE CHAPTER =============================
                    Route::prefix('chapters')
                        ->as('chapters.')
                        ->group(function () {
                            Route::post('/', [ChapterController::class, 'storeChapter']);
                            Route::put('/{chapter}/update-order', [ChapterController::class, 'updateOrderChapter']);
                            Route::put('/{slug}/{chapter}', [ChapterController::class, 'updateContentChapter']);
                            Route::delete('/{slug}/{chapter}', [ChapterController::class, 'deleteChapter']);
                            Route::get('/{chapter}/lessons', [ChapterController::class, 'getLessons']);
                        });

                    #============================== ROUTE LESSON =============================
                    Route::prefix('lessons')
                        ->as('lessons.')
                        ->group(function () {
                            Route::post('/', [LessonController::class, 'storeLesson']);
                            Route::put('/{lesson}/update-order', [LessonController::class, 'updateOrderLesson']);
                            Route::put('/{chapterId}/{lesson}', [LessonController::class, 'updateTitleLesson']);
                            Route::put('/{chapterId}/{lesson}/content', [LessonController::class, 'updateContentLesson']);
                            Route::delete('/{chapterId}/{lesson}', [LessonController::class, 'deleteLesson']);

                            Route::post('/{chapterId}/store-lesson-video', [\App\Http\Controllers\API\Instructor\LessonVideoController::class, 'storeLessonVideo']);
                            Route::get('/{chapterId}/{lesson}/show-lesson', [\App\Http\Controllers\API\Instructor\LessonVideoController::class, 'getLessonVideo']);
                            Route::put('/{chapterId}/{lesson}/update-lesson-video', [\App\Http\Controllers\API\Instructor\LessonVideoController::class, 'updateLessonVideo']);

                            Route::post('/{chapterId}/store-lesson-quiz', [\App\Http\Controllers\API\Instructor\QuizController::class, 'storeLessonQuiz']);

                            Route::prefix('quiz')
                                ->group(function () {
                                    Route::get('download-quiz-form', [\App\Http\Controllers\API\Instructor\QuizController::class, 'downloadQuizForm']);
                                    Route::get('export-quiz/{quiz}', [\App\Http\Controllers\API\Instructor\QuizController::class, 'exportQuiz']);
                                    Route::get('{quiz}/show-quiz', [\App\Http\Controllers\API\Instructor\QuizController::class, 'showQuiz']);
                                    Route::get('{question}/show-quiz-question', [\App\Http\Controllers\API\Instructor\QuizController::class, 'showQuestion']);
                                    Route::put('{question}/update-quiz-content', [\App\Http\Controllers\API\Instructor\QuizController::class, 'updateContentQuiz']);
                                    Route::post('{quiz}/store-quiz-question-multiple', [\App\Http\Controllers\API\Instructor\QuizController::class, 'storeQuestionMultiple']);
                                    Route::post('{quiz}/store-quiz-question-single', [\App\Http\Controllers\API\Instructor\QuizController::class, 'storeQuestionSingle']);
                                    Route::post('{quiz}/import-quiz-question', [\App\Http\Controllers\API\Instructor\QuizController::class, 'importQuiz']);
                                    Route::put('{question}/update-quiz-question', [\App\Http\Controllers\API\Instructor\QuizController::class, 'updateQuestion']);
                                    Route::put('{quiz}/update-order', [\App\Http\Controllers\API\Instructor\QuizController::class, 'updateOrderQuestion']);
                                    Route::delete('{question}/delete-quiz-question', [\App\Http\Controllers\API\Instructor\QuizController::class, 'deleteQuestion']);
                                });

                            Route::get('/{chapterId}/{lesson}/lesson-document', [DocumentController::class, 'getLessonDocument']);
                            Route::post('/{chapterId}/store-lesson-document', [DocumentController::class, 'storeLessonDocument']);
                            Route::put('/{chapterId}/{lesson}/update-lesson-document', [DocumentController::class, 'updateLessonDocument']);

                            Route::post('/{chapterId}/store-lesson-coding', [\App\Http\Controllers\API\Instructor\LessonCodingController::class, 'storeLessonCoding']);
                            Route::get('/{lesson}/{coding}/coding-exercise', [\App\Http\Controllers\API\Instructor\LessonCodingController::class, 'getCodingExercise']);
                            Route::put('/{lesson}/{coding}/coding-exercise', [\App\Http\Controllers\API\Instructor\LessonCodingController::class, 'updateCodingExercise']);
                        });
                });

            #============================== ROUTE TRANSACTION =============================
            Route::prefix('transactions')
                ->group(function () {
                    Route::get('/participated-courses', [\App\Http\Controllers\API\Instructor\TransactionController::class, 'getParticipatedCourses']);
                    Route::get('/participated-membership', [\App\Http\Controllers\API\Instructor\TransactionController::class, 'getMembershipPlansSold']);
                    Route::get('/enrolled-free-courses', [\App\Http\Controllers\API\Instructor\TransactionController::class, 'getCourseEnrollFree']);
                });

            #============================== ROUTE POST =============================
            Route::prefix('posts')->as('posts.')->group(function () {
                Route::get('/', [PostController::class, 'index']);
                Route::get('/{slug}', [PostController::class, 'getPostBySlug']);
                Route::post('/', [PostController::class, 'store']);
                Route::put('/{post}', [PostController::class, 'update']);
                Route::post('/submit-for-approval/{slug}', [PostController::class, 'submitForApproval']);
            });

            #============================== ROUTE COUPON =============================
            Route::prefix('coupons')->as('coupons.')->group(function () {
                Route::get('/', [InstructorCouponController::class, 'index']);
                Route::get('/{couponId}', [InstructorCouponController::class, 'show']);
                Route::post('/', [InstructorCouponController::class, 'store']);
                Route::put('/{couponId}', [InstructorCouponController::class, 'update']);
                Route::put('/{couponId}/{action}', [InstructorCouponController::class, 'toggleStatus'])->where('action', 'enable|disable');
                Route::delete('/{couponId}', [InstructorCouponController::class, 'destroy']);
            });
        });

    #============================== ROUTE NOTE =============================
    Route::prefix('notes')->as('notes.')->group(function () {
        Route::get('/{courseSlug}/get-notes', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::put('/{note}', [NoteController::class, 'update']);
        Route::delete('/{note}', [NoteController::class, 'destroy']);
    });


    #============================== ROUTE COUPON =============================
    Route::prefix('coupons')->as('coupons.')->group(function () {
        Route::get('/accept/{coupon_id}', [CouponController::class, 'acceptCoupon'])->name('coupons.accept');
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
            Route::prefix('group')
                ->middleware('roleHasInstructor')
                ->group(function () {
                    Route::get('/get-group-chats', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiGetGroupChats']);
                    Route::get('/info-group-chat/{id}', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiInfoGroupChat']);
                    Route::post('/create-group-chat', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiCreateGroupChat']);
                    Route::post('/add-member-group-chat/{id}', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiAddMemberGroupChat']);
                    Route::put('/update-info-group-chat/{id}', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiUpdateInfoGroupChat']);
                    Route::put('/block-member-group-chat/{id}/{memberId}', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiBlockMemberGroupChat']);
                    Route::delete('/delete-group-chat/{id}', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiDeleteGroupChat']);
                    Route::delete('/kick-member-group-chat', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiKickMemberGroupChat']);
                    Route::get('/{id}/remaining-members', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiGetRemainingMembers']);
                });

            Route::get('/group/get-group-student', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiGetStudentGroups']);

            Route::prefix('direct')
                ->group(function () {
                    Route::get('/get-direct-chats', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiGetDirectChats']);
                    Route::post('/start-direct-chat', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiStartDirectChat']);
                });

            Route::get('/get-message/{conversationId}', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiGetMessage']);
            Route::post('/send-message', [\App\Http\Controllers\API\Chat\ChatController::class, 'apiSendMessage']);
        });

    #============================== ROUTE REACTION =============================
    Route::prefix('reactions')
        ->group(function () {
            Route::post('/', [ReactionController::class, 'toggleReaction']);
            Route::get('/{commentId}', [ReactionController::class, 'index']);
        });

    #============================== ROUTE RATING =============================
    Route::prefix('ratings')
        ->group(function () {
            Route::get('/{courseId}', [RatingController::class, 'index']);
            Route::get('/{slug}/checkCourseState', [RatingController::class, 'checkCourseState']);
            Route::post('/', [RatingController::class, 'store']);
        });

    #============================== ROUTE POST =============================
    Route::prefix('posts')->as('posts.')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::post('/', [PostController::class, 'store']);
    });
});

#============================== ROUTE COURSE =============================
Route::prefix('courses')
    ->group(function () {
        Route::get('/discounted', [CommonCourseController::class, 'getDiscountedCourses']);
        Route::get('/free', [CommonCourseController::class, 'getFreeCourses']);
        Route::get('/popular', [CommonCourseController::class, 'getPopularCourses']);
        Route::get('/practice-exercises', [CommonCourseController::class, 'getPracticeExercises']);
        Route::get('/top-categories-with-most-courses', [CommonCourseController::class, 'getTopCategoriesWithMostCourses']);
        Route::get('/{slug}', [CommonCourseController::class, 'getCourseDetail']);
        Route::get('/{slug}/get-other-courses', [CommonCourseController::class, 'getOtherCourses']);
        Route::get('/{slug}/related', [CommonCourseController::class, 'getRelatedCourses']);
    });

#============================== ROUTE BANNER =============================
Route::get('/banners', [BannerController::class, 'index']);

#============================== ROUTE TAG =============================
Route::get('/tags', [TagController::class, 'index']);

#============================== ROUTE CATEGORY =============================
Route::get('/categories', [\App\Http\Controllers\API\Common\CategoryController::class, 'index']);

Route::get('/instructor-order-by-count-course', [\App\Http\Controllers\API\Common\CommonController::class, 'instructorOrderByCountCourse']);

#============================== ROUTE UPLOAD IMAGE =============================
Route::post('/upload/image', [\App\Http\Controllers\API\Common\CommonController::class, 'uploadImage']);

#============================== ROUTE POST =============================
Route::prefix('blogs')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\API\Common\BlogController::class, 'index']);
        Route::get('/{blog}', [\App\Http\Controllers\API\Common\BlogController::class, 'getBlogBySlug']);
        Route::get('/tag/{slug}', [\App\Http\Controllers\API\Common\BlogController::class, 'getBlogsByTag']);
        Route::get('/category/{slug}', [\App\Http\Controllers\API\Common\BlogController::class, 'getBlogsByCategory']);
        Route::post('/recent-views', [BlogController::class, 'recentViews']);
    });
Route::prefix('blogs')
    ->group(function () {
        Route::prefix('comments')
            ->group(function () {
                Route::get('/{blog}/blog-comment', [\App\Http\Controllers\API\Common\CommentBlogController::class, 'getCommentBlog']);
                Route::post('/store-blog-comment', [\App\Http\Controllers\API\Common\CommentBlogController::class, 'storeCommentBlog'])->middleware('auth:sanctum');
                Route::get('{comment}/replies', [\App\Http\Controllers\API\Common\CommentBlogController::class, 'getReplies']);
                Route::get('/comment-block-time', [\App\Http\Controllers\API\Common\CommentBlogController::class, 'getCommentBlockTime']);
                Route::post('/{comment}/reply', [\App\Http\Controllers\API\Common\CommentBlogController::class, 'reply'])->middleware('auth:sanctum');
                Route::delete('/{comment}', [\App\Http\Controllers\API\Common\CommentBlogController::class, 'deleteComment'])->middleware('auth:sanctum');
            });
    });

#============================== ROUTE QA SYSTEM =================================
Route::prefix('qa-systems')->group(function () {
    Route::get('/', [\App\Http\Controllers\API\Common\QaSystemController::class, 'index']);
});

Route::prefix('mux-upload')->group(function () {
    Route::post('video', [\App\Http\Controllers\Api\Instructor\HandleVideoController::class, 'handleUpload']);
});

#============================== ROUTE VERIFY MAIL =================================
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/email/resend', [VerificationController::class, 'resend'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.resend');
Route::get('/{code}/{slug}/get-validate-course', [CourseController::class, 'getValidateCourse']);


Route::get('/instructor-info/{code}', [CommonController::class, 'instructorInfo']);
Route::get('/get-course-instructor/{code}', [CommonController::class, 'getCourseInstructor']);
Route::get('/get-member-ship-plans/{code}', [CommonController::class, 'getMemberShipPlans']);

Route::get('/get-ratings', [RatingController::class, 'getLastRatings']);

Route::get('/get-course-ratings/{slug}', [RatingController::class, 'getCourseRatings']);

Route::get('/get-posts', [\App\Http\Controllers\API\Common\PostController::class, 'getPost']);
