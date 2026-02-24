<?php

use Illuminate\Support\Facades\Route;


Route::view('/', 'layouts.app')->middleware(['auth', 'verified']);


require __DIR__.'/auth.php';
