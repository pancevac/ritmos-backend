<?php

namespace App\Rules;


use App\Playlist;
use App\Track;
use Illuminate\Contracts\Validation\Rule;

class ValidateTrackPlaylist implements Rule
{
    /**
     * @var Track
     */
    protected $track;

    /**
     * @var bool
     */
    protected $noPlaylist;

    /**
     * ValidateTrackPlaylist constructor.
     * @param Track $track
     */
    public function __construct(Track $track)
    {
        $this->track = $track;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // check if user have access to playlist from request
        // check if track is already in given playlist
        $playlist = Playlist::owned()->where('id', $value)->first();

        if (!$playlist) {
            $this->noPlaylist = true;
            return false;
        }

        return !$playlist->tracks()->where('id', $this->track->id)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return $this->noPlaylist ?
            'Unknown playlist.' :
            'Track is already added in this playlist.';
    }
}