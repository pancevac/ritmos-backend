<?php

namespace App\Http\Controllers;

use App\Utils\PlaylistSearchAspect;
use App\Utils\TrackSearchAspect;
use Illuminate\Http\Request;
use Spatie\Searchable\Search;

class SearchController extends Controller
{
    /**
     * Search for playlists and tracks.
     * Return grouped matches by types (playlist and track).
     *
     * @param Request $request
     * @param Search $search
     * @return \Illuminate\Support\Collection
     */
    public function search(Request $request, Search $search)
    {
        return $search
            ->registerAspect(PlaylistSearchAspect::class)
            ->registerAspect(TrackSearchAspect::class)
            ->search($request->get('q'))
            ->groupByType();
    }

}
