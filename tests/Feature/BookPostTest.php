<?php

namespace Tests\Feature;

use App\Author;
use App\Book;
use App\User;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\TestCase;

class BookPostTest extends TestCase
{
    public function testDenyGuestAccess()
    {
        $author = factory(Author::class)->create();

        $response = $this->postJson('/api/books', [
            'isbn' => '9788328302341',
            'title' => 'PHP for beginners',
            'description' => 'Lorem ipsum',
            'authors' => [$author->id],
        ]);

        $response->assertStatus(401);
    }

    public function testDenyNonAdminUserAccess()
    {
        $user = factory(User::class)->create();
        $author = factory(Author::class)->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/api/books', [
                'isbn' => '9788328302341',
                'title' => 'Clean code',
                'description' => 'Lorem ipsum',
                'authors' => [$author->id],
            ]);

        $response->assertStatus(403);
    }

    public function testSuccessfulPost()
    {
        $user = factory(User::class)->state('admin')->create();
        $author = factory(Author::class)->create();

        $response = $this
            ->actingAs($user)
            ->postJson('/api/books', [
                'isbn' => '9788328302341',
                'title' => 'Clean code',
                'description' => 'Lorem ipsum',
                'authors' => [$author->id],
            ]);

        $response->assertStatus(201);
        $id = $response->json('data.id');
        $book = Book::find($id);
        $this->assertResponseContainsBook($response, $book);
        $this->assertEquals('9788328302341', $book->isbn);
        $this->assertEquals('Clean code', $book->title);
        $this->assertEquals('Lorem ipsum', $book->description);
        $this->assertEquals($author->id, $book->authors[0]->id);
    }

    /**
     * @dataProvider validationDataProvider
     */
    public function testValidation(array $invalidData, string $invalidParameter)
    {
        $book = factory(Book::class)->create(['isbn' => '9788328302341']);
        $user = factory(User::class)->state('admin')->create();
        $authors = factory(Author::class, 2)->create();
        $authorIds = $authors->pluck('id');

        $validData = [
            'isbn' => '9788328347786',
            'title' => 'Book title',
            'description' => 'Lorem ipsum',
            'authors' => $authorIds,
        ];
        $data = array_merge($validData, $invalidData);

        $response = $this
            ->actingAs($user)
            ->postJson('/api/books', $data);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([$invalidParameter]);
    }

    public function validationDataProvider()
    {
        return [
            [['isbn' => null], 'isbn'],
            [['isbn' => ''], 'isbn'],
            [['isbn' => '9788328302341'], 'isbn'],
            [['isbn' => '978832830234'], 'isbn'],
            [['isbn' => '97883283023422'], 'isbn'],
            [['isbn' => []], 'isbn'],
            [['isbn' => [673890]], 'isbn'],
            [['isbn' => ['978832830234']], 'isbn'],
            [['isbn' => 'FCKGWRHQQ2'], 'isbn'],
            [['title' => null], 'title'],
            [['title' => ''], 'title'],
            [['title' => []], 'title'],
            [['description' => null], 'description'],
            [['description' => ''], 'description'],
            [['description' => []], 'description'],
            [['authors' => null], 'authors'],
            [['authors' => []], 'authors'],
            [['authors' => ''], 'authors'],
            [['authors' => 1], 'authors'],
            [['authors' => [999999]], 'authors.0'],
            [['authors' => [[]]], 'authors.0'],
            [['authors' => [null]], 'authors.0'],
        ];
    }

    private function assertResponseContainsBook(TestResponse $response, Book $book): void
    {
        $response->assertJson([
            'data' => $this->bookToResourceArray($book),
        ]);
    }
}
