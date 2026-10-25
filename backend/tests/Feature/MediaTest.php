<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_media_without_token_returns_401(): void
    {
        $this->getJson('/api/media')->assertStatus(401);
    }

    public function test_user_only_sees_their_own_media(): void
    {
        $user = User::factory()->create();
        Media::factory()->count(2)->for($user)->create();
        Media::factory()->create(); // belongs to another user

        Sanctum::actingAs($user);

        $this->getJson('/api/media')
            ->assertOk()
            ->assertJsonCount(2);
    }

    public function test_creating_media_returns_201_and_saves_it(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/media', [
            'title' => 'Spirited Away',
            'type' => 'film',
            'status' => 'completed',
            'rating' => 9,
        ]);

        $response->assertStatus(201)
            ->assertJson(['title' => 'Spirited Away', 'user_id' => $user->id]);

        $this->assertDatabaseHas('media', ['title' => 'Spirited Away', 'user_id' => $user->id]);
    }

    public function test_creating_media_with_invalid_data_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/media', [
            'title' => '',
            'type' => 'podcast',
            'status' => 'completed',
            'rating' => 11,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'type', 'rating']);
    }

    public function test_showing_one_media(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/media/{$media->id}")
            ->assertOk()
            ->assertJson(['id' => $media->id, 'title' => $media->title]);
    }

    public function test_updating_media(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->for($user)->create(['status' => 'planned']);
        Sanctum::actingAs($user);

        $this->patchJson("/api/media/{$media->id}", ['status' => 'completed', 'rating' => 8])
            ->assertOk()
            ->assertJson(['status' => 'completed', 'rating' => 8]);

        $this->assertDatabaseHas('media', ['id' => $media->id, 'status' => 'completed']);
    }

    public function test_deleting_media(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->deleteJson("/api/media/{$media->id}")
            ->assertOk()
            ->assertJson(['message' => 'Media deleted.']);

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
    }

    public function test_user_cannot_access_another_users_media(): void
    {
        $otherMedia = Media::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/media/{$otherMedia->id}")->assertNotFound();
        $this->patchJson("/api/media/{$otherMedia->id}", ['title' => 'Hacked'])->assertNotFound();
        $this->deleteJson("/api/media/{$otherMedia->id}")->assertNotFound();

        $this->assertDatabaseHas('media', ['id' => $otherMedia->id, 'title' => $otherMedia->title]);
    }
}
