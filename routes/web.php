<?php

use \Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});



$router->post('login', 'Auth\LoginController@login');
$router->post('register', 'Auth\RegisterController@register');

$router->get('user', ['middleware' => 'auth', function (Request $request) {
    return $request->user();
}]);

/**
 * Playlist
 */
$router->get('playlists', [
    'as' => 'playlists.index',
    'uses' => 'PlaylistsController@index'
]);
$router->get('playlists/{playlist}', [
    'as' => 'playlists.show',
    'uses' => 'PlaylistsController@show'
]);

/**
 * User
 */
$router->get('profile/{user}', [
    'as' => 'profile.show',
    'uses' => 'ProfileController@show'
]);
