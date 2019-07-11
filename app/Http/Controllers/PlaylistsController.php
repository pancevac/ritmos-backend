<?php

namespace App\Http\Controllers;

use App\Playlist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlaylistsController extends Controller
{
    /**
     * Show list of playlists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'playlists' => Playlist::with('owner')->public()->latest()->take(5)->get()
        ]);
    }

    /**
     * Show the specific playlist.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $playlist = Playlist::with('owner')->public()->where('id', $id)->first();

        if (!$playlist) {
            return response()->json(['error' => 'No data found.'], 404);
        }

        return $playlist;
    }

    public function update($id, Request $request)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
