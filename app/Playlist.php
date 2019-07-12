<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Playlist extends Model implements HasMedia
{
    use HasMediaTrait;

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
     * Scope a query to only include specific user's playlists if there is url query in request.
     *
     * @param Builder $builder
     * @param Request $request
     * @return Builder
     */
    public function scopeIncludeByUser(Builder $builder, Request $request)
    {
        if ($request->has('user')) {
            return $builder->whereHas('owner', function (Builder $builder) use ($request) {
                $builder->where('email', $request->get('user'));
            });
        }

        return $builder;
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

    /**
     * Register playlist image collections.
     */
    public function registerMediaCollections()
    {
        $this->addMediaCollection('cover')
            ->singleFile();
    }
}
