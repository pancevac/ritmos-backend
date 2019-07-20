<?php

namespace App\Http\Controllers;

use App\Playlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PlaylistsController extends Controller
{
    /**
     * Show list of playlists.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return response()->json([
            'playlists' => Playlist::with('owner')
                ->withCount('tracks')
                ->public()
                ->includeByUser($request)
                ->latest()
                ->take(5)
                ->get()
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
        $playlist = Playlist::with([
            'owner',        // Load owner of playlist
            'tracks.media', // Load playlist tracks and its songs paths
            'media'         // Load playlist image
        ])->public()->where('id', $id)->first();

        if (!$playlist) {
            return response()->json(['error' => 'No data found.'], 404);
        }

        return $playlist;
    }

    /**
     * Store image for playlist.
     *
     * @param Request $request
     * @param $playlistId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function uploadImage(Request $request, $playlistId)
    {
        $this->validate($request, [
            'image' => 'required|image',
        ]);

        $playlist = Playlist::owned()->where('id', $playlistId)->first();

        if (!$playlist) {
            return response()->json(['error' => 'No playlist found.'], 404);
        }

        // Save image after validation
        $playlist->addMediaFromRequest('image')
            ->usingName($playlist->name)
            ->toMediaCollection('cover');

        return response()->json([
            'success' => 'Successful uploaded image.'
        ]);
    }

    /**
     * Update the given playlist resource.
     *
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $playlist = Playlist::owned()->find($id);

        if (!$playlist) {
            return response()->json(['error' => 'Playlist not found.']);
        }

        $this->validate($request, [
            'private' => 'nullable|boolean',
            'name' => ['required', 'string', 'max:255',
                Rule::unique('playlists')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($id)
            ],
        ]);

        $result = $playlist->update($request->only(['name', 'private']));

        return $result ?
            response()->json($playlist->refresh()) :
            response()->json(['error' => 'Error while updating playlist.']);
    }

    /**
     * Delete the specific playlist resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $playlist = Playlist::owned()->find($id);

        if (!$playlist) {
            return response()->json(['error' => 'Unknown playlist.']);
        }

        $playlist->delete();

        return response()->json(['success' => 'Playlist successfully deleted.']);
    }
}
