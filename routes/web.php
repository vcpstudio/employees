<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Dashboard\EmployeesController;
use App\Http\Controllers\Dashboard\PositionsController;

/* Роутинг для неавторизованных пользователей */
Route::middleware(['guest'])->group(function () {
    Route::get('/', function () {
        return view('dashboard/index');
    });
});

/* Отключаем регистрацию, подтверждение регистрации и сброс пароля */
Auth::routes([
    'register' => false,
    'reset' => false,
    'verify' => false,
    'confirm' => false,
]);

/* Роутинг панели управления */
Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    // Employees
    Route::get('/', [EmployeesController::class, 'index']);
    Route::get('/employees/list-ajax', [EmployeesController::class, 'listAjax'])->name('employees.list-ajax');
    Route::get('/employees/subordinates-num', [EmployeesController::class, 'getNumSubordinatesByHeadId'])
        ->name('employees.subordinates-num');
    Route::post('/employees/rotate-photo', [EmployeesController::class, 'rotatePhoto'])
        ->name('employees.rotate-photo');
    Route::resource('employees', EmployeesController::class);

    // Positions
    Route::get('/positions/list-ajax', [PositionsController::class, 'listAjax'])->name('positions.list-ajax');
    Route::get('/positions/employees-num', [PositionsController::class, 'employeesNumByPositionId'])
        ->name('positions.employees-num');
    Route::resource('positions', PositionsController::class);
});