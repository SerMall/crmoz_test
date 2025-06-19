<?php

use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});

Route::get('{all}', function () {
    return view('app');
})->where('all', '.*');
