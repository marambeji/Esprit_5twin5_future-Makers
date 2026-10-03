<?php

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
