<?php

namespace App\Utils;


use App\Playlist;
use Illuminate\Support\Collection;
use Spatie\Searchable\SearchAspect;

class PlaylistSearchAspect extends SearchAspect
{

    public function getResults(string $term): Collection
    {
        return Playlist::public()
            ->where('name', 'like', "%$term%")
            ->latest()
            ->limit(15)
            ->get();
    }
}