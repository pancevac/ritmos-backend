<?php

namespace App;

use App\Scopes\AccessibleScope;
use App\Traits\PlaylistAttachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Track extends Model implements HasMedia
{
    use HasMediaTrait, PlaylistAttachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'duration',
        'image_url',
        'artist',
        'album',
        'publishing_year'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'publishing_year',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
        //'pivot', TODO hide if needed
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new AccessibleScope('playlists'));
    }

    /**
     * Get playlists that track belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class)
            ->withPivot('order');
    }

    /**
     * Get owner of the track.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope a query to only include owned track by logged user.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeOwned(Builder $builder)
    {
        return $builder->where('user_id', Auth::id());
    }

    /**
     * Register playlist image collections.
     */
    public function registerMediaCollections()
    {
        $this->addMediaCollection('track')
            ->singleFile();
    }
}
