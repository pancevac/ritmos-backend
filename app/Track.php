<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;

class Track extends Model implements HasMedia, Searchable
{
    use HasMediaTrait;

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
        'media',
        //'pivot', TODO hide if needed
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['media_path'];

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
     * Get media path for track.
     *
     * @return string
     */
    public function getMediaPathAttribute()
    {
        if ($this->hasMedia('track')) {
            return url($this->getFirstMediaUrl('track'));
        }

        return '';
    }

    /**
     * Transform track duration from seconds into minutes.
     */
    public function getDurationAttribute($value)
    {
        return round($value / 60, 2);
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
     * Scope a query to only return tracks that belongs to public playlists.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeVisible(Builder $builder)
    {
        return $builder->whereHas('playlists', function (Builder $builder) {
            $builder->public();
        });
    }

    /**
     * Register playlist image collections.
     */
    public function registerMediaCollections()
    {
        $this->addMediaCollection('track')
            ->singleFile();
    }

    /**
     * Return model name and path as searching result.
     *
     * @return SearchResult
     */
    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->name,
            route('tracks.show', ['track' => $this])
        );
    }
}
