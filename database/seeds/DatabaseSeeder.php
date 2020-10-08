<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $users = factory(App\User::class, 5)->create();
        $admin = factory(App\User::class, 1)->state('admin')->create();

        factory(App\Author::class, 15)->create()->each(function (App\Author $author) {
            factory(App\Book::class, 3)->create()->each(function (App\Book $book) use ($author) {
                $book->authors()->saveMany([
                    $author,
                ]);
            });
        });

        \App\Book::all()->each(function (App\Book $book) use ($users) {
            $reviews = factory(App\BookReview::class, 4)->make();
            $reviews->each(function (\App\BookReview $review) use ($users) {
                $review->user()->associate($users->random());
            });
            $book->reviews()->saveMany($reviews);
        });
    }
}
