<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Laravel\Passport\HasApiTokens;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use HasApiTokens, Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'password', 'activated', 'blocked', 'created_at', 'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'activated' => 'boolean',
        'blocked' => 'boolean'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['profile'];

    /**
     * Get playlists that belongs to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|mixed
     */
    public function playlists()
    {
        return $this->hasMany(Playlist::class)->scopes(['public']);
    }

    /**
     * Get the user path.
     *
     * @return string
     */
    public function getProfileAttribute()
    {
        return $this->profile();
    }

    /**
     * Scope a query to only include active users.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeActivated(Builder $builder)
    {
        return $builder->where('activated', true)
            ->where('blocked', false);
    }

    /**
     * Generate the user path.
     *
     * @return string
     */
    public function profile()
    {
        return route('profile.show', ['user' => $this]);
    }
}
