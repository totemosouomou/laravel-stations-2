<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserMovieController;
use App\Http\Controllers\UserReservationController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ScreenController;
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
Route::get('/movies/{id}/schedules/{scheduleId}/sheets', [UserReservationController::class, 'sheets'])->name('user.movies.schedules.sheets');
Route::get('/movies/{id}/schedules/{scheduleId}/reservations/create', [UserReservationController::class, 'create'])->name('user.reservations.create');
Route::post('/reservations/store', [UserReservationController::class, 'store'])->name('user.reservations.store');

// 管理者向け映画関連のルート
Route::get('/admin/movies', [MovieController::class, 'admin'])->name('admin.movies.index');
Route::get('/admin/movies/create', [MovieController::class, 'create'])->name('admin.movies.create');
Route::post('/admin/movies/store', [MovieController::class, 'store'])->name('admin.movies.store');
Route::get('/admin/movies/{id}', [MovieController::class, 'detail'])->name('admin.movies.detail');
Route::get('/admin/movies/{id}/edit', [MovieController::class, 'edit'])->name('admin.movies.edit');
Route::patch('/admin/movies/{id}/update', [MovieController::class, 'update'])->name('admin.movies.update');
Route::delete('/admin/movies/{id}/destroy', [MovieController::class, 'destroy'])->name('admin.movies.destroy');

// 管理者向け座席予約関連のルート
Route::get('/admin/reservations', [ReservationController::class, 'movies'])->name('admin.reservations.index');
Route::get('/admin/reservations/create', [ReservationController::class, 'create'])->name('admin.reservations.create');
Route::post('/admin/reservations', [ReservationController::class, 'store'])->name('admin.reservations.store');
Route::get('/admin/reservations/{reservationId}/edit', [ReservationController::class, 'edit'])->name('admin.reservations.edit');
Route::patch('/admin/reservations/{reservationId}', [ReservationController::class, 'update'])->name('admin.reservations.update');
Route::delete('/admin/reservations/{reservationId}', [ReservationController::class, 'destroy'])->name('admin.reservations.destroy');

// 管理者向けスケジュール関連のルート
Route::get('/admin/schedules', [ScreenController::class, 'movies'])->name('admin.movies.schedules.index');
Route::get('/admin/schedules/create/{date}', [ScreenController::class, 'auto'])->name('admin.schedules.create.auto');
Route::get('/admin/movies/{id}/schedules/create', [ScreenController::class, 'create'])->name('admin.movies.schedules.create');
Route::post('/admin/movies/{id}/schedules/store', [ScreenController::class, 'store'])->name('admin.movies.schedules.store');
Route::get('/admin/schedules/{scheduleId}', [ScreenController::class, 'detail'])->name('admin.schedules.detail');
Route::get('/admin/schedules/{scheduleId}/edit', [ScreenController::class, 'edit'])->name('admin.schedules.edit');
Route::patch('/admin/schedules/{scheduleId}/update', [ScreenController::class, 'update'])->name('admin.schedules.update');
Route::delete('/admin/schedules/{scheduleId}/destroy', [ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

Route::get('/', function () {
    return view('welcome');
});
