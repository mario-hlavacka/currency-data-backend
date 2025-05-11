<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CurrencyController;

Route::get('/currencies', [CurrencyController::class, 'fetchApiData']);