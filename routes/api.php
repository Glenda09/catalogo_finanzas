<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstructorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/', [AuthController::class, 'login']);
    Route::post('/cerrar-sesion', [AuthController::class, 'logout']);
});


Route::prefix('cursos')->group(function () {
    Route::get('/', [CursoController::class, 'index']);
    Route::post('/', [CursoController::class, 'store']);
    Route::get('/{id}', [CursoController::class, 'show']);
    Route::put('/{id}', [CursoController::class, 'update']);
    Route::delete('/{id}', [CursoController::class, 'destroy']);
});

//instructor
Route::prefix('instructores')->group(function () {
    Route::get('/', [InstructorController::class, 'index']);
    Route::post('/', [InstructorController::class, 'store']);
    Route::get('/{id}', [InstructorController::class, 'show']);
    Route::put('/{id}', [InstructorController::class, 'update']);
    Route::delete('/{id}', [InstructorController::class, 'destroy']);
});

//usuarios
Route::prefix('usuarios')->group(function () {
    Route::get('/', [UsuarioController::class, 'index']);
    Route::post('/', [UsuarioController::class, 'store']);
    Route::get('/{id}', [UsuarioController::class, 'show']);
    Route::put('/{id}', [UsuarioController::class, 'update']);
    Route::delete('/{id}', [UsuarioController::class, 'destroy']);
});

