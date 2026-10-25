<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Safe to run many times: it never creates duplicates.
     */
    public function run(): void
    {
        // Demo account (there is no register endpoint).
        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Demo User', 'password' => 'password'],
        );

        // A few sample entries, only for a fresh account.
        if ($user->media()->exists()) {
            return;
        }

        $user->media()->createMany([
            ['title' => 'Spirited Away', 'type' => 'film', 'status' => 'completed', 'rating' => 9, 'consumed_at' => '2026-09-14'],
            ['title' => 'Frieren: Beyond Journey\'s End', 'type' => 'anime', 'status' => 'in_progress', 'rating' => 10],
            ['title' => 'Random Access Memories', 'type' => 'music', 'status' => 'completed', 'rating' => 8, 'consumed_at' => '2026-10-02'],
            ['title' => 'Laravel 12 from scratch', 'type' => 'youtube', 'status' => 'planned'],
        ]);
    }
}
