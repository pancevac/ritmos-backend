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
     * Type of validation (attach or detach)
     *
     * @var string
     */
    protected $type;

    /**
     * ValidateTrackPlaylist constructor.
     * @param Track $track
     * @param string $type
     */
    public function __construct(Track $track, string $type = 'attach')
    {
        $this->track = $track;
        $this->type = $type;
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

        // if attaching track to playlist, check if already existing
        // otherwise check if missing from db
        switch ($this->type) {
            case 'attach': return !$playlist->tracks()->where('id', $this->track->id)->exists();
            case 'detach': return $playlist->tracks()->where('id', $this->track->id)->exists();
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        if ($this->noPlaylist) {
            $message = 'Unknown playlist';
        }
        if ($this->type == 'attach') {
            $message = 'Track is already added in this playlist.';
        }
        if ($this->type == 'detach') {
            $message = 'Track is already removed from this playlist.';
        }

        return $message;
    }
}