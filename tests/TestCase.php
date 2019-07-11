<?php

abstract class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Sign in a user
     *
     * @param null $user
     * @param null $guard
     * @return $this
     */
    protected function signIn($user = null, $guard = null)
    {
        $user = $user ?: factory('App\User')->create();

        $this->actingAs($user, $guard);

        return $this;
    }
}
