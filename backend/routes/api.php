<?php

use App\Http\Controllers\Api\Admin\AnnouncementController;
use App\Http\Controllers\Api\Admin\CareerController as AdminCareerController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\FeedbackController as AdminFeedbackController;
use App\Http\Controllers\Api\Admin\MultimediaController as AdminMultimediaController;
use App\Http\Controllers\Api\Admin\QuizQuestionController as AdminQuizQuestionController;
use App\Http\Controllers\Api\Admin\ResourceController as AdminResourceController;
use App\Http\Controllers\Api\Admin\SuccessStoryModerationController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookmarkController;
use App\Http\Controllers\Api\CareerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\MultimediaController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\RecentViewController;
use App\Http\Controllers\Api\ResourceController;
use App\Http\Controllers\Api\SuccessStoryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'success' => true,
    'message' => 'PathSeeker API is available.',
    'data' => [
        'service' => 'pathseeker-api',
        'environment' => app()->environment(),
    ],
]));
Route::get('/career-domains', [CareerController::class, 'domains']);
Route::get('/career-options', [CareerController::class, 'options']);
Route::get('/careers', [CareerController::class, 'index'])->name('careers.index');
Route::get('/careers/{slug}', [CareerController::class, 'show'])->name('careers.show');
Route::get('/resources', [ResourceController::class, 'index']);
Route::get('/resources/{slug}', [ResourceController::class, 'show']);
Route::post('/resources/{slug}/download', [ResourceController::class, 'download']);
Route::get('/multimedia', [MultimediaController::class, 'index']);
Route::get('/multimedia/{slug}', [MultimediaController::class, 'show']);
Route::get('/success-stories', [SuccessStoryController::class, 'index']);
Route::get('/success-stories/{slug}', [SuccessStoryController::class, 'show']);

Route::prefix('auth')->middleware('throttle:auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
});

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function (): void {
    Route::get('/health', fn () => response()->json(['success' => true, 'message' => 'Administrator access verified.', 'data' => null]));
    Route::get('/dashboard', [AdminDashboardController::class, 'show']);
    Route::get('/catalog-options', [AdminDashboardController::class, 'options']);
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{user}', [AdminUserController::class, 'show']);
    Route::patch('/users/{user}', [AdminUserController::class, 'update']);
    Route::apiResource('careers', AdminCareerController::class)->names('admin.careers');
    Route::apiResource('quiz-questions', AdminQuizQuestionController::class)->names('admin.quiz-questions');
    Route::apiResource('resources', AdminResourceController::class)->names('admin.resources');
    Route::apiResource('multimedia', AdminMultimediaController::class)->names('admin.multimedia');
    Route::get('/success-stories', [SuccessStoryModerationController::class, 'index']);
    Route::patch('/success-stories/{story}', [SuccessStoryModerationController::class, 'update']);
    Route::get('/feedback', [AdminFeedbackController::class, 'index']);
    Route::get('/feedback/analytics', [AdminFeedbackController::class, 'analytics']);
    Route::patch('/feedback/{feedback}', [AdminFeedbackController::class, 'update']);
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements', [AnnouncementController::class, 'store']);
});

Route::middleware('auth:sanctum')->prefix('auth')->group(function (): void {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/resume', [ProfileController::class, 'uploadResume']);
    Route::get('/profile-options', [ProfileController::class, 'options']);
    Route::get('/quiz/questions', [QuizController::class, 'questions']);
    Route::post('/quiz/attempts', [QuizController::class, 'start']);
    Route::put('/quiz/attempts/{attempt}/answer', [QuizController::class, 'answer']);
    Route::post('/quiz/attempts/{attempt}/complete', [QuizController::class, 'complete']);
    Route::get('/quiz/attempts/{attempt}/result', [QuizController::class, 'result']);
    Route::get('/quiz/history', [QuizController::class, 'history']);
    Route::get('/dashboard', [DashboardController::class, 'show']);
    Route::get('/bookmarks', [BookmarkController::class, 'index']);
    Route::post('/bookmarks', [BookmarkController::class, 'store']);
    Route::patch('/bookmarks/{bookmark}', [BookmarkController::class, 'update']);
    Route::delete('/bookmarks/{bookmark}', [BookmarkController::class, 'destroy']);
    Route::get('/recent-views', [RecentViewController::class, 'index']);
    Route::post('/recent-views', [RecentViewController::class, 'store']);
    Route::post('/multimedia/{multimedia}/rating', [MultimediaController::class, 'rate']);
    Route::get('/success-story-submissions', [SuccessStoryController::class, 'mine']);
    Route::post('/success-story-submissions', [SuccessStoryController::class, 'store']);
    Route::get('/feedback', [FeedbackController::class, 'index']);
    Route::post('/feedback', [FeedbackController::class, 'store']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read']);
});
