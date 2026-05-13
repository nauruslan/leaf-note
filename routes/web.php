<?php

use App\Http\Controllers\NoteImageController;
use App\Http\Controllers\PrivacyPolicyController;
use App\Http\Controllers\SafePasswordResetController;
use App\Http\Controllers\TermsOfServiceController;
use Illuminate\Support\Facades\Route;

// Очистка сессии на время разработки
Route::get('/clear-session', function() {
    session()->flush();
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');

    return redirect()->route('login')->with('clear_session_storage', true);
});

// Главная страница (требует аутентификации и верификации)
Route::view('/', 'layouts.app')->middleware(['auth', 'verified'])->name('app');

// Загрузка изображения
Route::post('/notes/upload-image', [NoteImageController::class, 'upload'])
    ->name('notes.upload-image');

// Мягкое удаление изображения (для undo/redo)
Route::post('/notes/soft-delete-image', [NoteImageController::class, 'softDelete'])
    ->name('notes.soft-delete-image');

// Восстановление изображения (при undo)
Route::post('/notes/restore-image', [NoteImageController::class, 'restore'])
    ->name('notes.restore-image');

// Подключение маршрутов из auth.php
require __DIR__.'/auth.php';

// Сброс пароля сейфа (защищённые маршруты требующие аутентификации)
Route::middleware(['auth'])->group(function () {
    Route::post('/safe-password/send-reset-link', [SafePasswordResetController::class, 'sendResetLink'])
        ->name('safe-password.send-reset-link');
});

// Подписанный маршрут для сброса пароля сейфа (не требует аутентификации)
Route::get('/safe-password/reset', [SafePasswordResetController::class, 'reset'])
    ->name('safe-password.reset');

// Политика конфиденциальности (открывается в новом окне)
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'index'])
    ->name('privacy-policy');

// Условия использования (открываются в новом окне)
Route::get('/terms-of-service', [TermsOfServiceController::class, 'index'])
    ->name('terms-of-service');
