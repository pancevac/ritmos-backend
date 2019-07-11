<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class InteractsWithPlaylistsTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function test_playlist_listing()
    {
        $playlists = factory(\App\Playlist::class, 3)->create(['private' => false])->load('owner');

        $this->get('api/playlists')
            ->seeJson(['playlists' => $playlists->sortByDesc('created_at')->toArray()]);
    }

    /** @test */
    function it_can_only_return_public_playlists_for_anonymous_visitor()
    {
        $anonymous = factory(\App\Playlist::class, 2)->create(['private' => true]);
        $public = factory(\App\Playlist::class, 3)->create(['private' => false])->load('owner');

        $this->get('api/playlists')->seeJson([
            'playlists' => $public->sortByDesc('created_at')->toArray(),
        ]);
    }

    /** @test */
    function it_will_return_specific_only_public_playlist()
    {
        $public =  factory(\App\Playlist::class)->create(['private' => false]);
        $anonymous = factory(\App\Playlist::class)->create(['private' => true]);

        $this->get('api/playlists/' . $public->getKey())
            ->seeJson($public->toArray());

        $this->get('api/playlists/' . $anonymous->getKey())
            ->seeJson(['error' => 'No data found.']);
    }
}