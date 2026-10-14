<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    private array $valid = [
        'title' => 'Laskar Pelangi',
        'author' => 'Andrea Hirata',
        'status' => 'reading',
        'rating' => 5,
    ];

    public function test_register_returns_201_and_token(): void
    {
        $this->postJson('/api/register', [
            'name' => 'Galih',
            'email' => 'galih@example.com',
            'password' => 'rahasia123',
        ])->assertCreated()->assertJsonStructure(['user', 'token']);
    }

    public function test_login_success_returns_200_and_token(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'rahasia123']);

        $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'rahasia123'])
            ->assertOk()->assertJsonStructure(['token']);
    }

    public function test_login_wrong_password_returns_401(): void
    {
        User::factory()->create(['email' => 'a@example.com', 'password' => 'rahasia123']);

        $this->postJson('/api/login', ['email' => 'a@example.com', 'password' => 'salah'])
            ->assertUnauthorized();
    }

    public function test_books_require_token(): void
    {
        $this->getJson('/api/books')->assertUnauthorized();
    }

    public function test_user_can_create_and_list_own_books(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', $this->valid)->assertCreated()->assertJsonPath('title', 'Laskar Pelangi');
        $this->getJson('/api/books')->assertOk()->assertJsonCount(1);
    }

    public function test_invalid_payload_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/books', ['title' => '', 'status' => 'lainnya', 'rating' => 9])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'author', 'status', 'rating']);
    }

    public function test_missing_book_returns_404(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/books/999')->assertNotFound();
    }

    public function test_cannot_access_another_users_book(): void
    {
        $other = Book::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/books/{$other->id}")->assertNotFound();
    }

    public function test_user_can_update_and_delete_book(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $book = Book::factory()->for($user)->create();

        $this->putJson("/api/books/{$book->id}", array_merge($this->valid, ['status' => 'done']))
            ->assertOk()->assertJsonPath('status', 'done');

        $this->deleteJson("/api/books/{$book->id}")->assertOk();
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}