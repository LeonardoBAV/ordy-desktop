<?php

use App\Http\Controllers\Api\MovementController;
use Illuminate\Support\Facades\Route;

Route::post('movements', MovementController::class)
    ->name('movements.store');
