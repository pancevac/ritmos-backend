<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class InteractsWithUsersTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    function test_can_open_specific_user_profile_by_its_id()
    {
        $user = factory(\App\User::class)->create();

        $usersPlalists = factory(\App\Playlist::class, 5)->create(['user_id' => $user->id]);

        $user = $user->load(['playlists', 'tracks.media', 'media']);

        $this->get('/api/profile/' . $user->id)
            ->seeJson($user->toArray());
    }

    /** @test */
    function only_auth_user_can_edit_its_own_profile()
    {
        $user = factory(\App\User::class)->create();

        $response = $this->put('/api/profile/' . $user->id . '/update', [
            'name' => 'sile',
            'email' => 'sinisa@gmail.com'
        ]);

        $response->assertResponseStatus(401);

        $this->signIn($user);

        $response = $this->put('/api/profile/' . $user->id . '/update', [
            'name' => 'sile',
            'email' => 'sinisa@gmail.com'
        ]);

        $this->seeInDatabase('users', [
            'name' => 'sile',
            'email' => 'sinisa@gmail.com'
        ]);
    }
}