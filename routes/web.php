<?php

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
    return view('welcome');
});

Route::get('/search', [SolariumController::class, 'index']);
Route::get('/extract', [SolariumController::class, 'extract']);
Route::get('/crawl', [SolariumController::class, 'crawl']);
Route::post('/search', [SolariumController::class, 'search']);
