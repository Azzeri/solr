<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SolariumController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('/search');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/search', [SolariumController::class, 'index']);
Route::get('/extract', [SolariumController::class, 'extract']);
Route::post('/crawl', [SolariumController::class, 'crawl']);
Route::post('/search', [SolariumController::class, 'search']);
Route::get('/cleanDocuments', [SolariumController::class, 'cleanDocuments']);
Route::get('/cleanDatabase', [SolariumController::class, 'cleanDatabase']);