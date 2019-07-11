<?php

namespace App\Http\Controllers;

use App\Playlist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
     * Store the new playlist.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'private' => 'nullable|boolean',
            'name' => ['required', 'string', 'max:255',
                Rule::unique('playlists')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })
            ],
        ]);

        return Playlist::create(array_merge(
            $request->only(['name', 'private']),
            ['user_id' => Auth::id()]
        ));
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
