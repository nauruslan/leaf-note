<?php

use App\Http\Controllers\NoteImageController;
use Illuminate\Support\Facades\Route;

// Очистка сессии на время разработки
Route::get('/clear-session', function() {
    session()->flush();
    return redirect()->route('login');
});

Route::view('/', 'layouts.app')->middleware(['auth', 'verified'])->name('app');

Route::post('/notes/upload-image', [NoteImageController::class, 'upload'])->name('notes.upload-image');

Route::delete('/notes/delete-image', [NoteImageController::class, 'delete'])->name('notes.delete-image');


require __DIR__.'/auth.php';