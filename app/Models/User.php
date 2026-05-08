<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'google_id',
        'password',
        'role',
        'role_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'role',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function roleRelation(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleAttribute(): ?string
    {
        if (!$this->role_id) {
            return null;
        }

        if ($this->relationLoaded('roleRelation')) {
            return $this->roleRelation?->slug;
        }

        return $this->roleRelation()->value('slug');
    }

    public function setRoleAttribute(?string $value): void
    {
        if (empty($value)) {
            $this->attributes['role_id'] = null;
            return;
        }

        $role = Role::firstOrCreate(
            ['slug' => $value],
            ['name' => ucfirst($value)]
        );

        $this->attributes['role_id'] = $role->id;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }
}
