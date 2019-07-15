<?php

namespace App\Utils;


use App\Track;
use Illuminate\Support\Collection;
use Spatie\Searchable\SearchAspect;

class TrackSearchAspect extends SearchAspect
{

    public function getResults(string $term): Collection
    {
        return Track::visible()
            ->where('name', 'like', "%$term%")
            ->latest()
            ->limit(15)
            ->get();
    }
}