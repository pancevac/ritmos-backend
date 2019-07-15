<?php

use \Laravel\Lumen\Testing\DatabaseMigrations;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        $this->artisan('passport:install');

        $result = \DB::table('oauth_clients')->find(2);

        config()->set('passport.client_id', $result->id);
        config()->set('passport.client_secret', $result->secret);
    }

    /** @test */
    function a_user_can_make_request_for_login_and_get_access_token()
    {
        $user = factory(\App\User::class)->create([
            'activated' => true,
            'blocked' => false,
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $response = $this->post('api/login', [
            'username' => $user->email,
            'password' => 'password',
        ]);

        $response->seeJsonStructure(['access_token']);
    }
}