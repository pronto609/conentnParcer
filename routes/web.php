<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParserController;
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

Route::get('/', [ParserController::class, 'index'])->name('parser.index');
Route::post('/process', [ParserController::class, 'process'])->name('parser.process');
Route::post('/loaddb', [ParserController::class, 'loaddb'])->name('parser.loaddb');
Route::get('/download/file', [ParserController::class, 'downloadFile'])->name('download.file');
