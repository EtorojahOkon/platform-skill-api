<?php

use App\Http\Controllers\V1\AssessmentController;
use App\Http\Controllers\V1\Auth\LoginController;
use App\Http\Controllers\V1\Auth\SignupController;
use App\Http\Controllers\V1\CourseController;
use App\Http\Controllers\V1\GoalController;
use App\Http\Controllers\V1\LessonController;
use App\Http\Controllers\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('login', [LoginController::class, 'login']);

Route::controller(SignupController::class)->group(function () {
    Route::post('signup', 'signup');
    Route::post('verify-email', 'verifyEmail');
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::controller(AssessmentController::class)->prefix('assessments')->group(function () {
       Route::post('/', 'startAssessment');
       Route::get('{assessment}', 'getAssessment');
       Route::post('{assessment}/video-chunk', [AssessmentController::class, 'uploadChunk']);
       Route::post('{assessment}/evaluate', 'evaluateAssessment');
    });

    Route::controller(CourseController::class)->prefix('courses')->group(function () {
        Route::get('/', 'index');
        Route::get('{course}', 'show');
    });

    Route::controller(LessonController::class)->prefix('lessons')->group(function () {
        Route::get('{lesson}', 'show');
    });

    Route::controller(TaskController::class)->prefix('tasks')->group(function () {
        Route::get('{task}', 'show');
    });

    Route::get('goals', [GoalController::class, 'index']);

    Route::post('logout', [LoginController::class, 'logout']);
});