<?php

namespace App\Http\Controllers;

use App\Playlist;
use App\Rules\ValidateTrackPlaylist;
use App\Track;
use App\Utils\YouTubeAPI;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use wapmorgan\Mp3Info\Mp3Info;

class TracksController extends Controller
{
    /**
     * Get latest tracks from all public playlists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'tracks' => Track::with(['media'])
                ->visible()
                ->latest()
                ->take(5)
                ->get()
        ]);
    }

    /**
     * Store new track record.
     *
     * @param Request $request
     * @param YouTubeAPI $API
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request, YouTubeAPI $API)
    {
        $this->validate($request, [
            'track' => 'required|mimetypes:audio/mpeg',
        ]);

        try {
            $API->sendSearchRequest();
            $audio = new Mp3Info($request->file('track')->getRealPath());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }

        // Create track record in database
        $track = Track::create([
            'user_id' => Auth::id(),
            'name' => $API->getTItle(),
            'image_url' => $API->getImage(),
            'duration' => $audio->duration,
            'artist' => $audio->tags1['artist'] ?? null,
            'album' => $audio->tags1['album'] ?? null,
        ]);

        // Store track in storage
        $track->addMediaFromRequest('track')
            ->usingName($API->getTItle())
            ->toMediaCollection('track');

        return $track;
    }

    /**
     * Show the specific track resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $track = Track::visible()->where('id', $id)->first();

        if (!$track) {
            return response()->json(['error' => 'Unknown track.']);
        }

        return $track;
    }

    /**
     * Update track resource (only name, artist, album).
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $id)
    {
        $track = Track::owned()->where('id', $id)->first();

        if (!$track) {
            return response()->json(['error' => 'Unknown track.']);
        }

        $this->validate($request, [
            'name' => 'string|max:255',
            'artist' => 'string|max:255',
            'album' => 'string|max:255',
            'playlist_id' => ['integer', new ValidateTrackPlaylist($track)],
        ]);

        $result = $track->update($request->only([
            'name',
            'artist',
            'album'
        ]));

        $playlist = Playlist::with('tracks')->find($request->playlist_id);

        $track->attachToPlaylist($playlist);

        return $result ?
            response()->json([$track->fresh()]) :
            response()->json(['error' => 'Track can not be updated!']);
    }

    /**
     * Delete specific track resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $track = Track::owned()->where('id', $id)->first();

        if (!$track) {
            return response()->json(['erorr' => 'Unknown track.']);
        }

        $track->delete();

        return response()->json(['success' => 'Track successfully deleted.']);
    }
}
