<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class InteractsWithSearchTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function a_visitor_can_search_for_playlist_or_track()
    {
        $playlist = factory(\App\Playlist::class)->create([
            'private' => false,
            'name' => 'Moja playlista'
        ]);
        $tracks = factory(\App\Track::class, 3)->create([
            'name' => 'test 123'
        ]);

        $tracks->each(function (\App\Track $track) use ($playlist) {
            $playlist->attach($track);
        });

        $this->get('api/search?q=moja%20playlista')
            ->seeJsonStructure([
                'playlists' => []
            ]);
    }
}