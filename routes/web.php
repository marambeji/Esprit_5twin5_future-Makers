<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.dashboard')->name('home');
Route::view('/dashboard', 'pages.dashboard')->name('dashboard');
Route::view('/about', 'pages.about')->name('about');
Route::view('/service', 'pages.service')->name('service');
Route::view('/testimonials', 'pages.testimonials')->name('testimonials');
Route::view('/blog', 'pages.blog')->name('blog');
Route::view('/contact', 'pages.contact')->name('contact');

Route::middleware('guest:web')->group(function () {
    Route::view('/login', 'auth.login')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::view('/register', 'auth.register')->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1')->name('register.store');
});
Route::middleware('guest:admin')->group(function () {
    Route::view('/admin/login', 'admin.auth.login')->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:web')->name('logout');
Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth:admin')->name('admin.logout');
Route::prefix('admin')->name('admin.')->middleware(['auth:admin', EnsureAdmin::class])->group(function () {
    Route::redirect('/', '/admin/dashboard');
    Route::view('/dashboard', 'admin.pages.dashboard')->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('users', UserController::class);
});
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogue/{product}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/catalogue/{product}/image', [CatalogController::class, 'image'])->name('catalog.image');

Route::resource('certifications', CertificationController::class);

