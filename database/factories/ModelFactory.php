<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret,
        'activated' => true,
        'blocked' => false
    ];
});

$factory->define(App\Playlist::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'private' => $faker->boolean,
        'user_id' => factory(\App\User::class)->create([
            'activated' => true,
            'blocked' => false
        ])->id,
    ];
});

$factory->define(App\Track::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'duration' => $faker->time('s', 200),
        'image_url' => $faker->imageUrl(),
        'artist' => $faker->name,
        'album' => $faker->name,
        'publishing_year' => $faker->date('Y'),
        'user_id' => factory(\App\User::class)->create([
            'activated' => true,
            'blocked' => false
        ])->id,
    ];
});
