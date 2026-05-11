<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Сервис для валидации данных профиля
 */
class ProfileValidationService
{
    /**
     * Валидация данных профиля
     */
    public function validateProfile(array $data): array
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ])->validate();
    }

    /**
     * Валидация смены пароля
     */
    public function validatePasswordChange(array $data): array
    {
        return Validator::make($data, [
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:8',
            'confirmPassword' => 'required|string|same:newPassword',
        ])->validate();
    }

    /**
     * Валидация пароля сейфа
     */
    public function validateSafePassword(array $data, bool $hasCurrentPassword): array
    {
        $rules = [
            'password' => 'required|string|min:4',
            'confirmPassword' => 'required|string|same:password',
        ];

        if ($hasCurrentPassword) {
            $rules['currentPassword'] = 'required|string';
        }

        return Validator::make($data, $rules)->validate();
    }
}