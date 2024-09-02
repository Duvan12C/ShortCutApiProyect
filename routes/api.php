<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

// Ruta para crear una nueva URL acortada
Route::post('/create', [UrlController::class, 'create']);

// Ruta para eliminar una URL acortada por ID
Route::delete('/delete/{id}', [UrlController::class, 'delete']);

// Ruta para obtener el historial de URLs acortadas
Route::get('/history', [UrlController::class, 'history']);

// Ruta para redirigir desde la URL corta a la URL original
Route::get('/{shortened_url}', [UrlController::class, 'redirect']);
