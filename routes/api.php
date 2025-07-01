<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'auth',
    'as' => 'api.auth.',
    'controller' => AuthController::class,
], function () {
    Route::post('/', 'store')->name('store')->middleware('guest');
    Route::delete('/', 'destroy')->name('destroy')->middleware('auth:api');
});

Route::group([
    'middleware' => 'auth:api',
    'as' => 'api.',
], function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'message' => __('api.general.show.success'),
            'data' => $request->user(),
        ]);
    })->name('user');

    Route::apiResource('/categories', CategoryController::class);

    Route::put('/posts/{post}/mark-published', [PostController::class, 'markAsPublished'])->name('posts.mark-published');
    Route::put('/posts/{post}/mark-draft', [PostController::class, 'markAsDraft'])->name('posts.mark-draft');
    Route::apiResource('/posts', PostController::class);

    Route::put('/pages/{page}/mark-published', [PageController::class, 'markAsPublished'])->name('pages.mark-published');
    Route::put('/pages/{page}/mark-draft', [PageController::class, 'markAsDraft'])->name('pages.mark-draft');
    Route::apiResource('/pages', PageController::class);
});
