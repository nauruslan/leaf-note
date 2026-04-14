<?php

namespace App\Models;

use App\Models\Trash;
use App\Models\Archive;
use App\Models\Safe;
use App\Models\Folder;
use App\Models\Note;
use App\Services\DemoUserService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_demo',
        'email_verified_at',
        'google_id',
        'notifications_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_demo' => 'boolean',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'notifications_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->trash()->create([
                'capacity' => 100,
                'current_quantity' => 0,
            ]);

            $user->archive()->create();

            $user->safe()->create([
                'max_attempts' => 3,
                'failed_attempts' => 0,
                'locked_until' => null,
                'last_accessed_at' => null,
            ]);

            $user->folders()->createMany([
            [
                'title' => 'Рабочая',
                'color' => 'electric-blue',
                'icon' => 'briefcase',
            ],
            [
                'title' => 'Личное',
                'color' => 'neon-pink',
                'icon' => 'heart',
            ],
            [
                'title' => 'Идеи',
                'color' => 'neon-amber',
                'icon' => 'lightbulb',
            ],
        ]);
        });

        static::deleting(function (User $user) {
            // Вручную удаляем notes и folders перед удалением пользователя
            $user->notes()->delete();
            $user->folders()->delete();
        });
    }

    // Отношения
    public function trash()    { return $this->hasOne(Trash::class); }
    public function archive()  { return $this->hasOne(Archive::class); }
    public function safe()     { return $this->hasOne(Safe::class); }
    public function folders()  { return $this->hasMany(Folder::class); }
    public function notes()    { return $this->hasMany(Note::class); }
    public function textNotes() {
        return $this->hasMany(Note::class)
            ->where('type', Note::TYPE_NOTE)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id');
    }
    public function listNotes() {
        return $this->hasMany(Note::class)
            ->where('type', Note::TYPE_CHECKLIST)
            ->whereNull('trash_id')
            ->whereNull('archive_id')
            ->whereNull('safe_id');
    }
    public function favorites() {
        return $this->hasMany(Note::class)
            ->where('is_favorite', true)
            ->whereNull('trash_id');
    }

    // Вспомогательные методы
    public function hasGoogleAccount(): bool
    {
        return !empty($this->google_id);
    }

    public function isDemoUser(): bool
    {
        return (bool) $this->is_demo;
    }

    /**
     * Проверка: истёк ли срок действия демо-пользователя.
     * Считает по created_at + DEMO_LIFETIME_MINUTES.
     */
    public function isDemoExpired(): bool
    {
        if (!$this->is_demo) {
            return false;
        }

        return now()->gte(
            $this->created_at->addMinutes(DemoUserService::DEMO_LIFETIME_MINUTES)
        );
    }
}