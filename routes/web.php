<?php

use App\Http\Controllers\AdminCertificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\CommandeAdminController;
use App\Http\Controllers\CertificationController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\DistributorSpaceController;
use App\Http\Controllers\FermeController;
use App\Http\Controllers\FermePublicController;
use App\Http\Controllers\FoodPhotoController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\NutritionalAnalysisController;
use App\Http\Controllers\NutritionLabelController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProducteurController;
use App\Http\Controllers\ProducteurPublicController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransformateurController;
use App\Http\Controllers\TransformateurPublicController;
use App\Http\Controllers\TransformationController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureDistributor;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.dashboard')->name('home');
Route::view('/dashboard', 'auth.dashboard')->middleware(['auth:web', 'verified'])->name('dashboard');
Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::view('/about', 'pages.about')->name('about');
Route::view('/service', 'pages.service')->name('service');
Route::view('/testimonials', 'pages.testimonials')->name('testimonials');
Route::view('/blog', 'pages.blog')->name('blog');
Route::view('/contact', 'pages.contact')->name('contact');

require __DIR__.'/auth.php';

Route::middleware('guest:admin')->group(function () {
    Route::view('/admin/login', 'admin.auth.login')->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'login'])->name('admin.login.store');
});
Route::post('/admin/logout', [AuthController::class, 'logout'])->middleware('auth:admin')->name('admin.logout');
Route::prefix('admin')->name('admin.')->middleware(['auth:admin', EnsureAdmin::class])->group(function () {
    Route::redirect('/', '/admin/dashboard');
    Route::view('/dashboard', 'admin.pages.dashboard')->name('dashboard');
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::post('analyses-nutritionnelles/identifier-aliment', [FoodPhotoController::class, 'identify'])->middleware('throttle:5,1')->name('nutrition.identify');
    Route::post('analyses-nutritionnelles/references', [FoodPhotoController::class, 'search'])->middleware('throttle:10,1')->name('nutrition.references');
    Route::post('analyses-nutritionnelles/lecture-etiquette', NutritionLabelController::class)->middleware('throttle:5,1')->name('nutrition.read-label');
    Route::resource('analyses-nutritionnelles', NutritionalAnalysisController::class)->parameters(['analyses-nutritionnelles' => 'analysis'])->names('nutrition');
    Route::resource('users', UserController::class);
    Route::resource('distributors', DistributorController::class);
    Route::post('deliveries/suggerer', [DeliveryController::class, 'suggest'])->middleware('throttle:10,1')->name('deliveries.suggest');
    Route::resource('deliveries', DeliveryController::class)->parameters(['deliveries' => 'delivery']);
    Route::patch('commandes/{commande}/confirmer', [CommandeAdminController::class, 'confirm'])->name('commandes.confirm');
    Route::patch('commandes/{commande}/refuser', [CommandeAdminController::class, 'refuse'])->name('commandes.refuse');
    Route::resource('commandes', CommandeAdminController::class);
    Route::resource('producteurs', ProducteurController::class);
    Route::resource('fermes', FermeController::class);
    Route::resource('labels', LabelController::class);
    Route::resource('certifications', AdminCertificationController::class);
    // Module Transformation (Zied)
    Route::resource('transformateurs', TransformateurController::class);
    Route::post('transformations/estimer', [TransformationController::class, 'estimate'])->middleware('throttle:10,1')->name('transformations.estimate');
    Route::resource('transformations', TransformationController::class);
});
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/catalogue/{legacyProduct}', [CatalogController::class, 'legacy'])->whereNumber('legacyProduct');
Route::get('/catalogue/{product:slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/catalogue/{product}/image', [CatalogController::class, 'image'])->name('catalog.image');

// Espace client : commandes (chaque client ne voit que les siennes).
Route::middleware('auth:web')->group(function () {
    Route::post('/livraisons/{delivery}/avis', [\App\Http\Controllers\AvisLivraisonController::class, 'store'])->middleware('throttle:10,1')->name('deliveries.avis.store');
    Route::get('/commandes', [CommandeController::class, 'index'])->name('commandes.index');
    Route::get('/commandes/creer', [CommandeController::class, 'create'])->name('commandes.create');
    Route::post('/commandes', [CommandeController::class, 'store'])->name('commandes.store');
    Route::post('/commandes/assistant', [CommandeController::class, 'assist'])->middleware('throttle:10,1')->name('commandes.assist');
    Route::get('/commandes/{commande}', [CommandeController::class, 'show'])->whereNumber('commande')->name('commandes.show');
    Route::patch('/commandes/{commande}/annuler', [CommandeController::class, 'cancel'])->whereNumber('commande')->name('commandes.cancel');
});

// Espace distributeur : compte utilisateur lié à un distributeur actif.
Route::middleware(['auth:web', EnsureDistributor::class])->prefix('espace-distributeur')->name('distributor-space.')->group(function () {
    Route::get('/livraisons', [DistributorSpaceController::class, 'index'])->name('index');
    Route::get('/livraisons/{delivery}', [DistributorSpaceController::class, 'show'])->name('show');
    Route::patch('/livraisons/{delivery}/statut', [DistributorSpaceController::class, 'updateStatus'])->name('status');
});

// Front Office — Producteurs & Fermes (authentifié)
Route::middleware('auth:web')->group(function () {
    Route::get('/producteurs', [ProducteurPublicController::class, 'index'])->name('producteurs.index');
    Route::get('/producteurs/{producteur}', [ProducteurPublicController::class, 'show'])->name('producteurs.show');
    Route::get('/fermes', [FermePublicController::class, 'index'])->name('fermes.index');
    Route::get('/fermes/{ferme}', [FermePublicController::class, 'show'])->name('fermes.show');
});

Route::get('/certifications', [CertificationController::class, 'index'])->name('certifications.index');
Route::get('/certifications/{certification}', [CertificationController::class, 'show'])->name('certifications.show');
Route::get('/certifications/{certification}/qrcode', [CertificationController::class, 'qrcode'])->name('certifications.qrcode');
Route::get('/certifications/{certification}/document', [CertificationController::class, 'document'])->name('certifications.document');

// Front Office — Transformateurs et empreinte des transformations (public, comme les certifications)
Route::get('/transformateurs', [TransformateurPublicController::class, 'index'])->name('transformateurs.index');
Route::get('/transformateurs/{transformateur}', [TransformateurPublicController::class, 'show'])->name('transformateurs.show');
