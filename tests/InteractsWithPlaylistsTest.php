<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

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
    function it_can_return_public_and_private_playlists_for_authenticated_user()
    {
        $this->signIn();

        $public = factory(\App\Playlist::class, 3)->create(['private' => false])->load('owner');
        $otherPrivate = factory(\App\Playlist::class, 2)->create(['private' => true])->load('owner');

        $owned = factory(\App\Playlist::class, 2)->create([
            'private' => true,
            'user_id' => Auth::id()
        ])->load('owner');

        $playlists = $public->merge($owned);

        $this->get('api/playlists')->seeJson([
            'playlists' => $playlists->sortByDesc('created_at')->toArray()
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
            ->seeJson(['error' => 'No data found.'])
            ->assertResponseStatus(404);
    }

    /** @test */
    function it_will_also_return_private_playlist_if_belongs_to_owner()
    {
        $this->signIn();

        $playlist = factory(\App\Playlist::class)->create([
            'user_id' => Auth::id(),
            'private' => true
        ]);

        $tracks = factory(\App\Track::class, 3)->create(['user_id' => Auth::id()]);

        $playlist->tracks()->attach($tracks->pluck('id')->toArray());

        $playlist->load(['owner', 'tracks']);

        $this->get('api/playlists/' . $playlist->getKey())
            ->seeJson($playlist->toArray());
    }

    /** @test */
    function a_user_can_create_new_playlist()
    {
        $this->signIn();

        $playlist = factory(\App\Playlist::class)->make();

        $post = Arr::except($playlist->toArray(), ['user_id', 'path']);

        $this->post('api/playlists', $post);

        $this->seeInDatabase('playlists', ['name' => $playlist->name]);
    }

    /** @test */
    function a_non_auth_user_can_not_create_playlist()
    {
        $playlist = factory(\App\Playlist::class)->make();

        $response = $this->post('api/playlists', Arr::except($playlist->toArray(), ['user_id', 'path']));

        $response->assertResponseStatus(401);
    }
}