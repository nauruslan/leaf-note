<?php

namespace App\Models;

use App\Models\Trash;
use App\Models\Archive;
use App\Models\Safe;
use App\Models\Folder;
use App\Models\Note;
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
        'avatar_path',
        'gender',
        'birth_date',
        'country',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_demo' => 'boolean',
        'birth_date' => 'date',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Вычисляемые атрибуты
    public function getAgeAttribute(): ?int
    {
        return $this->birth_date ? now()->diffInYears($this->birth_date) : null;
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar_path
            ? asset('storage/' . $this->avatar_path)
            : asset('images/default-avatar.png');
    }

    // Boot-логика с явными значениями (рекомендуется)
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
                'color' => 'default',
                'icon' => 'briefcase',
            ],
            [
                'title' => 'Личное',
                'color' => 'default',
                'icon' => 'heart',
            ],
            [
                'title' => 'Идеи',
                'color' => 'default',
                'icon' => 'lightbulb',
            ],
        ]);
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
}
