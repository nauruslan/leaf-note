<?php

use Illuminate\Support\Facades\Route;


Route::view('/', 'layouts.app')->middleware(['auth', 'verified'])->name('app');


require __DIR__.'/auth.php';