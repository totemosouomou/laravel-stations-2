<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserMovieController;
use App\Http\Controllers\UserReservationController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminMovieController;

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

Route::get('/', function () {
    return redirect()->route('user.movies.index');
});

// ユーザー向け映画関連のルート
Route::get('/movies', [UserMovieController::class, 'index'])->name('user.movies.index');
Route::get('/movies/{id}', [UserMovieController::class, 'schedules'])->name('user.movies.schedules');
Route::get('/sheets', [UserMovieController::class, 'sheets'])->name('user.movies.sheets');

// ユーザー向け座席予約関連のルート
Route::group(['middleware' => 'auth'], function () {
    Route::get('/movies/{id}/schedules/{scheduleId}/sheets', [UserReservationController::class, 'sheets'])->name('user.movies.schedules.sheets');
    Route::get('/movies/{id}/schedules/{scheduleId}/reservations/create', [UserReservationController::class, 'create'])->name('user.reservations.create');
    Route::post('/reservations/store', [UserReservationController::class, 'store'])->name('user.reservations.store');
});

// ユーザー向け認証関連のルート
Route::get('/users/create', function () {
    return view('auth.register');
})->name('users.create');
Route::post('/register', [UserController::class, 'register'])->name('register');

Route::get('/login', function () {
    return view('auth.login');
})->name('login');
Route::post('/login', [UserController::class, 'login'])->name('login');

Route::post('/logout', [UserController::class, 'logout'])->name('logout');


Route::group(['middleware' => ['auth', 'admin']], function () {
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
    Route::get('/admin/schedules', [ScheduleController::class, 'movies'])->name('admin.movies.schedules.index');
    Route::get('/admin/schedules/create/{date}', [ScheduleController::class, 'auto'])->name('admin.schedules.create.auto');
    Route::get('/admin/movies/{id}/schedules/create', [ScheduleController::class, 'create'])->name('admin.movies.schedules.create');
    Route::post('/admin/movies/{id}/schedules/store', [ScheduleController::class, 'store'])->name('admin.movies.schedules.store');
    Route::get('/admin/schedules/{scheduleId}', [ScheduleController::class, 'detail'])->name('admin.schedules.detail');
    Route::get('/admin/schedules/{scheduleId}/edit', [ScheduleController::class, 'edit'])->name('admin.schedules.edit');
    Route::patch('/admin/schedules/{scheduleId}/update', [ScheduleController::class, 'update'])->name('admin.schedules.update');
    Route::delete('/admin/schedules/{scheduleId}/destroy', [ScheduleController::class, 'destroy'])->name('admin.schedules.destroy');

    // 管理者向け認証関連のルート
    Route::get('/search-users', [UserController::class, 'search']);
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__.'/auth.php';
