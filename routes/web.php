<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Volt::route('categories', 'categories.manage')->name('categories');
    Route::group([
        'prefix' => 'posts',
        'as' => 'posts.',
    ], function () {
        Volt::route('/', 'posts.lists')->name('index');
        Volt::route('/create', 'posts.create')->name('create');
        Volt::route('/{record}/edit', 'posts.edit')->name('edit');
    });
    Route::group([
        'prefix' => 'pages',
        'as' => 'pages.',
    ], function () {
        Volt::route('/', 'pages.lists')->name('index');
        Volt::route('/create', 'pages.create')->name('create');
        Volt::route('/{record}/edit', 'pages.edit')->name('edit');
    });
});

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

require __DIR__.'/auth.php';
