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

        // If request has playlist_id, attach newly created track to
        // playlist with that id
        if ($request->has('playlist_id')) {

            $this->validate($request, [
                'playlist_id' => ['required', 'integer', new ValidateTrackPlaylist($track)],
            ]);

            $playlist = Playlist::with('tracks')->find($request->playlist_id);
            $playlist->attach($track);
        }

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
        $track = Track::with('playlists')->visible()->where('id', $id)->first();

        if (!$track) {
            return response()->json(['error' => 'Unknown track.'], 404);
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
            'artist' => 'nullable|string|max:255',
            'album' => 'nullable|string|max:255',
            'playlist_id' => ['integer', new ValidateTrackPlaylist($track)],
        ]);

        $result = $track->update($request->only([
            'name',
            'artist',
            'album'
        ]));

        if ($request->has('playlist_id')) {
            $playlist = Playlist::with('tracks')->find($request->playlist_id);
            $playlist->attach($track);
        }

        return $result ?
            response()->json([$track->fresh()]) :
            response()->json(['error' => 'Track can not be updated!']);
    }

    /**
     * Handle adding track into playlist.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function attachToPlaylist(Request $request, $id)
    {
        $track = Track::owned()->where('id', $id)->first();

        if (!$track) {
            return response()->json(['error' => 'Unknown track.']);
        }

        $this->validate($request, [
            'playlist_id' => ['required', 'integer', new ValidateTrackPlaylist($track)],
        ]);

        $playlist = Playlist::with('tracks')->find($request->playlist_id);

        $playlist->attach($track);

        return response()->json(['success' => 'Track added to playlist.']);
    }

    /**
     * Handle removing track from playlist.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function detachFromPlaylist(Request $request, $id)
    {
        $track = Track::owned()->where('id', $id)->first();

        if (!$track) {
            return response()->json(['error' => 'Unknown track.']);
        }

        $this->validate($request, [
            'playlist_id' => ['required', 'integer', new ValidateTrackPlaylist($track, 'detach')],
        ]);

        $playlist = Playlist::with('tracks')->find($request->playlist_id);

        $playlist->tracks()->detach($track);

        return response()->json(['success' => 'Track removed from playlist.']);
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
