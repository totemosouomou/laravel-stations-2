<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PracticeController;
use App\Http\Controllers\UserMovieController;
use App\Http\Controllers\MovieController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/practice', [PracticeController::class, 'sample']);
Route::get('/practice2', [PracticeController::class, 'sample2']);
Route::get('/practice3', [PracticeController::class, 'sample3']);
Route::get('/getPractice', [PracticeController::class, 'getPractice']);
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/admin/movies', [MovieController::class, 'admin']);

Route::get('/movies', [UserMovieController::class, 'index']);
Route::get('/movies/{id}', [UserMovieController::class, 'Schedule']);
Route::get('/sheets', [UserMovieController::class, 'sheets']);

Route::get('/admin/movies', [MovieController::class, 'admin']);
Route::get('/admin/movies/create', [MovieController::class, 'create']);
Route::post('/admin/movies/store', [MovieController::class, 'store']);
Route::get('/admin/movies/{id}/edit', [MovieController::class, 'edit'])->name('admin.movies.edit');
Route::patch('/admin/movies/{id}/update', [MovieController::class, 'update'])->name('admin.movies.update');
Route::delete('/admin/movies/{id}/destroy', [MovieController::class, 'destroy']);

Route::get('/', function () {
    return view('welcome');
});
