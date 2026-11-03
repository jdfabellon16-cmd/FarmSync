<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::view('/{path}', 'app')
    ->where('path', '^(?!api(?:/|$)|up$).*$');
