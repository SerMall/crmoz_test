<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//  ................................

Route::get('/zoho', function () {
    return Inertia::render('ZohoForm');
})->name('zoho');
Route::get('/zohosimple', function () {
    return Inertia::render('ZohoFormSimple');
})->name('zohosimple');
