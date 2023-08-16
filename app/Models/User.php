<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'name',
        'surname',
        'password',
        'job',
        'profile_picture',
        'organization_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password'
    ];

    protected $casts = [
        'disabled' => 'boolean',
    ];

    /**
     * Default values of the User model attributes.
     *
     * @var array
     */
    protected $attributes = [
        'disabled' => false,
        'role_id' => 1
    ];

    public function organization() {
        return $this->belongsTo(Organization::class);
    }

    public function role() {
        return $this->belongsTo(Role::class);
    }

    public function posts() {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function comments() {
        return $this->hasMany(Comment::class, 'author_id');
    }
}
