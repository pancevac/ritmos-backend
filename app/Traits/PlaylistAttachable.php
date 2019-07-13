<?php

namespace App\Traits;

use App\Playlist;

trait PlaylistAttachable
{
    /**
     * Attach track to playlist.
     *
     * @param Playlist $playlist
     */
    public function attachToPlaylist(Playlist $playlist)
    {
        $order = 1;

        if ($playlist->tracks->isNotEmpty()) {
            $highestOrderTrack = $playlist->tracks->last();
            $order = $highestOrderTrack->pivot->order + 1;
        }

        $this->playlists()->attach($playlist->id, ['order' => $order]);
    }
}