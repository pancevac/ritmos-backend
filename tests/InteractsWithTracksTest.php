<?php

use Illuminate\Support\Facades\Auth;
use Laravel\Lumen\Testing\DatabaseMigrations;

class InteractsWithTracksTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function visitor_can_list_latest_tracks_that_belongs_to_public_playlists()
    {
        $user = factory(\App\User::class)->create();
        $playlist = factory(\App\Playlist::class)->create(['user_id' => $user->id, 'private' => false]);

        // Tracks that doesn't belongs to any list
        $notAssignedTracks = factory(\App\Track::class, 10)->create(['user_id' => $user->id]);

        // Tracks that are assigned to playlist
        $assignedTracks = factory(\App\Track::class, 5)->create([
            'user_id' => $user->id
        ]);

        // assign tracks to playlist.
        $assignedTracks->each(function (\App\Track $track) use ($playlist) {
            $track->attachToPlaylist($playlist);
        });

        $this->get('api/tracks')
            ->seeJson([
                'tracks' => $assignedTracks->sortByDesc('created_at')->toArray()
            ]);
    }

    /** @test */
    function visitor_can_see_only_specific_track_that_belongs_to_public_playlist()
    {
        $user = factory(\App\User::class)->create();
        $playlist = factory(\App\Playlist::class)->create(['user_id' => $user->id, 'private' => false]);

        $track = factory(\App\Track::class)->create([
            'user_id' => $user->id
        ]);

        $track->attachToPlaylist($playlist);

        $this->get('api/tracks/' . $track->id)
            ->seeJson($track->toArray());
    }

    /** @test */
    function visitor_can_not_see_specific_track_if_not_belongs_to_public_playlist()
    {
        $user = factory(\App\User::class)->create();
        $playlist = factory(\App\Playlist::class)->create(['user_id' => $user->id, 'private' => true]);

        $track = factory(\App\Track::class)->create([
            'user_id' => $user->id
        ]);

        $track->attachToPlaylist($playlist);

        $this->get('api/tracks/' . $track->id)->assertResponseStatus(404);
    }

    /** @test */
    function auth_user_can_update_its_track_info_only()
    {
        $user = factory(\App\User::class)->create();
        $playlist = factory(\App\Playlist::class)->create(['user_id' => $user->id]);
        $track = factory(\App\Track::class)->create([
            'user_id' => $user->id
        ]);

        $this->put('api/tracks/' . $track->id . '/update', $track->toArray())->assertResponseStatus(401);

        $this->signIn($user);

        $this->put('api/tracks/' . $track->id . '/update', array_merge(
            $track->toArray(),
            ['playlist_id' => $playlist->id]
        ))->seeJson($track->toArray());

        $this->seeInDatabase('playlist_track', [
            'playlist_id' => $playlist->id,
            'track_id' => $track->id,
        ]);
    }

    /** @test */
    function auth_user_can_delete_its_track_only()
    {
        $user = factory(\App\User::class)->create();
        $playlist = factory(\App\Playlist::class)->create(['user_id' => $user->id]);
        $track = factory(\App\Track::class)->create([
            'user_id' => $user->id
        ]);

        $this->signIn($user);

        $this->delete('api/tracks/' . $track->id)
            ->seeJson(['success' => 'Track successfully deleted.']);

        $this->notSeeInDatabase('playlist_track', [
            'playlist_id' => $playlist->id,
            'track_id' => $track->id,
        ]);
    }

    /** @test */
    function auth_user_can_add_track_to_playlist()
    {
        $this->signIn();

        $playlist = factory(\App\Playlist::class)->create(['user_id' => Auth::id()]);
        $track = factory(\App\Track::class)->create(['user_id' => Auth::id()]);

        $response = $this->put('api/tracks/' . $track->id . '/add_to_playlist', [
            'playlist_id' => $playlist->id
        ]);

        $response->seeJson(['success' => 'Track added to playlist.']);

        $this->seeInDatabase('playlist_track', [
            'playlist_id' => $playlist->id,
            'track_id' => $track->id,
        ]);
    }

    /** @test */
    function auth_user_can_remove_track_from_playlist()
    {
        $this->signIn();

        $playlist = factory(\App\Playlist::class)->create(['user_id' => Auth::id()]);
        $track = factory(\App\Track::class)->create(['user_id' => Auth::id()]);

        $track->attachToPlaylist($playlist);

        $response = $this->put('api/tracks/' . $track->id . '/remove_from_playlist', [
            'playlist_id' => $playlist->id
        ]);

        $response->seeJson(['success' => 'Track removed from playlist.']);

        $this->notSeeInDatabase('playlist_track', [
            'playlist_id' => $playlist->id,
            'track_id' => $track->id,
        ]);
    }
}