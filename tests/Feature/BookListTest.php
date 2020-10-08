<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use App\BookReview;
use App\User;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class BooksListTest extends TestCase
{
    public function testResponseStructure()
    {
        factory(Book::class, 5)->make()->each(function (Book $book) {
            $book->save();
            $book->authors()->saveMany([
                factory(Author::class)->create(),
            ]);

            $reviews = factory(BookReview::class, 3)->make()->each(function (BookReview $review) {
                $review->user()->associate(factory(User::class)->create());
            });
            $book->reviews()->saveMany($reviews);
        });

        $response = $this->getJson('/api/books');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'isbn',
                    'title',
                    'description',
                    'authors' => [
                        '*' => [
                            'id',
                            'name',
                            'surname',
                        ],
                    ],
                    'review' => [
                        'avg',
                        'count',
                    ],
                ],
            ],
            'links' => [
                'first',
                'last',
                'next',
                'prev',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }

    public function testListWithoutFilters()
    {
        $books = factory(Book::class, 5)->make()->each(function (Book $book) {
            $book->save();
            $book->authors()->saveMany([
                factory(Author::class)->create(),
            ]);

            $reviews = factory(BookReview::class, 3)->make()->each(function (BookReview $review) {
                $review->user()->associate(factory(User::class)->create());
            });
            $book->reviews()->saveMany($reviews);
        });

        $response = $this->getJson('/api/books');

        $response->assertStatus(200);
        $this->assertResponseContainsBooks($response, ...$books);
    }

    public function testTitleFilter()
    {
        $book1 = factory(Book::class)->create(['title' => 'PHP for begginers']);
        $book2 = factory(Book::class)->create(['title' => 'Javascript for dummies']);
        $book3 = factory(Book::class)->create(['title' => 'Advanced Python']);

        $response = $this->getJson('/api/books?title=php');

        $response->assertStatus(200);
        $this->assertResponseContainsBooks($response, $book1);

        $response = $this->getJson('/api/books?title=for');

        $response->assertStatus(200);
        $this->assertResponseContainsBooks($response, $book1, $book2);
    }

    public function testAuthorsFilter()
    {
        $author1 = factory(Author::class)->create();
        $author2 = factory(Author::class)->create();

        $book1 = factory(Book::class)->create(['title' => 'PHP for begginers']);
        $book1->authors()->saveMany([$author1, $author2]);

        $book2 = factory(Book::class)->create(['title' => 'Javascript for dummies']);
        $book2->authors()->saveMany([$author1]);

        $book3 = factory(Book::class)->create(['title' => 'Advanced Python']);
        $book3->authors()->saveMany([$author2]);


        $response = $this->getJson('/api/books?authors='.$author1->id);
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, $book1, $book2);

        $response = $this->getJson('/api/books?authors='.$author2->id);
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, $book1, $book3);

        $response = $this->getJson('/api/books?authors='.$author1->id.','.$author2->id);
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, $book1, $book2, $book3);
    }

    public function testTitleSort()
    {
        $book1 = factory(Book::class)->create(['title' => 'PHP for begginers']);
        $book2 = factory(Book::class)->create(['title' => 'Javascript for dummies']);
        $book3 = factory(Book::class)->create(['title' => 'Advanced Python']);

        $response = $this->getJson('/api/books?sortColumn=title');
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, $book3, $book2, $book1);

        $response = $this->getJson('/api/books?sortColumn=title&sortDirection=DESC');
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, $book1, $book2, $book3);
    }

    public function testAvgReviewSort()
    {
        $user = factory(User::class)->create();

        $book1 = factory(Book::class)->create(['title' => 'PHP for begginers']); // 3
        $book1Review1 = factory(BookReview::class)->make(['review' => 1]);
        $book1Review1->user()->associate($user);
        $book1Review2 = factory(BookReview::class)->make(['review' => 5]);
        $book1Review2->user()->associate($user);
        $book1->reviews()->saveMany([$book1Review1, $book1Review2]);

        $book2 = factory(Book::class)->create(['title' => 'Javascript for dummies']); // 6
        $book2Review1 = factory(BookReview::class)->make(['review' => 4]);
        $book2Review1->user()->associate($user);
        $book2Review2 = factory(BookReview::class)->make(['review' => 8]);
        $book2Review2->user()->associate($user);
        $book2->reviews()->saveMany([$book2Review1, $book2Review2]);

        $book3 = factory(Book::class)->create(['title' => 'Advanced Python']); // 0

        $response = $this->getJson('/api/books?sortColumn=avg_review');
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, $book3, $book1, $book2);

        $response = $this->getJson('/api/books?sortColumn=avg_review&sortDirection=DESC');
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, $book2, $book1, $book3);
    }

    public function testPagination()
    {
        $books = factory(Book::class, 30)->create();
        $firstPageBooks = $books->forPage(1, 15);
        $secondPageBooks = $books->forPage(2, 15);

        $response = $this->getJson('/api/books?page=1');
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, ...$firstPageBooks);

        $response = $this->getJson('/api/books?page=2');
        $response->assertStatus(200);

        $this->assertResponseContainsBooks($response, ...$secondPageBooks);
    }

    private function assertResponseContainsBooks(TestResponse $response, ...$books): void
    {
        $response->assertJson([
            'data' => array_map(function (Book $book) {
                return $this->bookToResourceArray($book);
            }, $books),
        ]);
    }
}
