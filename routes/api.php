<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ZohoController;

// ......................................


Route::post('/zoho/create', [ZohoController::class, 'createDealAndAccount']);
Route::get('/zoho/stageslist', [ZohoController::class, 'getDealStagesList']);
