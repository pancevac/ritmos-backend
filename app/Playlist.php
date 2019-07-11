<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Playlist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'private'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'user_id', 'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'private' => 'boolean'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['path'];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ActivatedOwnerScope('owner'));
    }

    /**
     * Get user that owns the playlist.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include public or owner playlist.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopePublic(Builder $builder)
    {
        $builder->where('private', '!=', true);

        return Auth::check() ? $builder->orWhere('user_id', Auth::id()) : $builder;
    }

    /**
     * Get the playlist path.
     *
     * @return string
     */
    public function getPathAttribute()
    {
        return $this->path();
    }

    /**
     * Set the user id for playlist.
     *
     * @param $value
     */
    public function setUserIdAttribute($value)
    {
        $this->attributes['user_id'] = $value ?? Auth::id();
    }

    /**
     * Generate playlist path.
     *
     * @return string
     */
    public function path()
    {
        return route('playlists.show', ['playlist' => $this]);
    }
}
