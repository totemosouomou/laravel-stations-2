<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserMovieController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ScheduleController;
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

// ユーザー向け映画関連のルート
Route::get('/movies', [UserMovieController::class, 'index'])->name('user.movies.index');
Route::get('/movies/{id}', [UserMovieController::class, 'schedules'])->name('user.movies.schedules');
Route::get('/sheets', [UserMovieController::class, 'sheets'])->name('user.movies.sheets');

// ユーザー向け座席予約関連のルート
Route::get('/movies/{id}/schedules/{scheduleId}/sheets', [ReservationController::class, 'sheets'])->name('user.movies.schedules.sheets');
Route::get('/movies/{id}/schedules/{scheduleId}/reservations/create', [ReservationController::class, 'create'])->name('user.reservations.create');
Route::post('/reservations/store', [ReservationController::class, 'store'])->name('user.reservations.store');

// 管理者向け映画関連のルート
Route::get('/admin/movies', [MovieController::class, 'admin'])->name('admin.movies.index');
Route::get('/admin/movies/create', [MovieController::class, 'create'])->name('admin.movies.create');
Route::post('/admin/movies/store', [MovieController::class, 'store'])->name('admin.movies.store');
Route::get('/admin/movies/{id}', [MovieController::class, 'detail'])->name('admin.movies.detail');
Route::get('/admin/movies/{id}/edit', [MovieController::class, 'edit'])->name('admin.movies.edit');
Route::patch('/admin/movies/{id}/update', [MovieController::class, 'update'])->name('admin.movies.update');
Route::delete('/admin/movies/{id}/destroy', [MovieController::class, 'destroy'])->name('admin.movies.destroy');

// 管理者向けスケジュール関連のルート
Route::get('/admin/schedules', [ScheduleController::class, 'admin'])->name('admin.schedules.index');
Route::get('/admin/movies/{id}/schedules/create', [ScheduleController::class, 'create'])->name('admin.movies.schedules.create');
Route::post('/admin/movies/{id}/schedules/store', [ScheduleController::class, 'store'])->name('admin.movies.schedules.store');
Route::get('/admin/schedules/{scheduleId}', [ScheduleController::class, 'detail'])->name('admin.schedules.detail');
Route::get('/admin/schedules/{scheduleId}/edit', [ScheduleController::class, 'edit'])->name('admin.schedules.edit');
Route::patch('/admin/schedules/{scheduleId}/update', [ScheduleController::class, 'update'])->name('admin.schedules.update');
Route::delete('/admin/schedules/{scheduleId}/destroy', [ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

Route::get('/', function () {
    return view('welcome');
});
