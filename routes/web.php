<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.dashboard')->name('home');
Route::view('/dashboard', 'pages.dashboard')->name('dashboard');
Route::view('/about', 'pages.about')->name('about');
Route::view('/service', 'pages.service')->name('service');
Route::view('/testimonials', 'pages.testimonials')->name('testimonials');
Route::view('/blog', 'pages.blog')->name('blog');
Route::view('/contact', 'pages.contact')->name('contact');

Route::redirect('/admin', '/admin/dashboard');
Route::view('/admin/dashboard', 'admin.pages.dashboard')->name('admin.dashboard');
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
});
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogue/{product}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/catalogue/{product}/image', [CatalogController::class, 'image'])->name('catalog.image');
