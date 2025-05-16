<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UsuarioRolController;

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
    Route::get('/{id}', [InstructorController::class, 'show']);
    Route::delete('/{id}', [InstructorController::class, 'destroy']);
    Route::get('/{id}/cursos', [InstructorController::class, 'getCursos']);
});

//usuarios
Route::prefix('usuarios')->group(function () {
    Route::get('/', [UsuarioController::class, 'index']);
    Route::post('/', [UsuarioController::class, 'store']);
    Route::get('/{id}', [UsuarioController::class, 'show']);
    Route::put('/{id}', [UsuarioController::class, 'update']);
    Route::delete('/{id}', [UsuarioController::class, 'destroy']);
});

//roles
Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    Route::get('/{id}', [RoleController::class, 'show']);
    Route::put('/{id}', [RoleController::class, 'update']);
    Route::delete('/{id}', [RoleController::class, 'destroy']);
});

//usuario-rol
Route::prefix('usuario_rol')->group(function () {
    Route::get('/', [UsuarioRolController::class, 'index']);
    Route::post('/', [UsuarioRolController::class, 'store']);
    Route::get('/{id}', [UsuarioRolController::class, 'show']);
    Route::put('/{id}', [UsuarioRolController::class, 'update']);
    Route::delete('/{id}', [UsuarioRolController::class, 'destroy']);
});
