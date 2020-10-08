<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Book;
use App\BookReview;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(BookReview::class, function (Faker $faker) {
    return [
        'review' => $faker->randomElement(range(1, 10)),
        'comment' => $faker->text(),
    ];
});