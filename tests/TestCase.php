<?php

namespace Tests;

use App\Author;
use App\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function bookToResourceArray(Book $book)
    {
        return [
            'id' => $book->id,
            'isbn' => $book->isbn,
            'title' => $book->title,
            'description' => $book->description,
            'authors' => $book->authors->map(function (Author $author) {
                return ['id' => $author->id, 'name' => $author->name, 'surname' => $author->surname];
            })->toArray(),
            'review' => [
                'avg' => (int) round($book->reviews->avg('review')),
                'count' => (int) $book->reviews->count(),
            ],
        ];
    }
}
