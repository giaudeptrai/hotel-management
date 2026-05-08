<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'is_active',
        'permissions',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
