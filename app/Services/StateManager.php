<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

/**
 * Центральный сервис управления состоянием приложения
 */
class StateManager
{
    private static ?array $globalState = null;

    /**
     * Инициализация глобального состояния
     */
    private static function initializeState(): void
    {
        if (self::$globalState === null) {
            self::$globalState = Session::get('app_state', []);
        }
    }

    /**
     * Установка значения в состоянии
     */
    public static function set(string $key, mixed $value): void
    {
        self::initializeState();
        self::$globalState[$key] = $value;
        Session::put('app_state', self::$globalState);
    }

    /**
     * Получение значения из состояния
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::initializeState();
        return self::$globalState[$key] ?? $default;
    }

    /**
     * Проверка наличия ключа в состоянии
     */
    public static function has(string $key): bool
    {
        self::initializeState();
        return isset(self::$globalState[$key]);
    }

    /**
     * Удаление значения из состояния
     */
    public static function remove(string $key): void
    {
        self::initializeState();
        unset(self::$globalState[$key]);
        Session::put('app_state', self::$globalState);
    }

    /**
     * Очистка всего состояния
     */
    public static function clear(): void
    {
        self::$globalState = [];
        Session::forget('app_state');
    }

    /**
     * Получение всего состояния
     */
    public static function getAll(): array
    {
        self::initializeState();
        return self::$globalState;
    }

    /**
     * Пакетное обновление состояния
     */
    public static function setMultiple(array $data): void
    {
        self::initializeState();
        foreach ($data as $key => $value) {
            self::$globalState[$key] = $value;
        }
        Session::put('app_state', self::$globalState);
    }
}