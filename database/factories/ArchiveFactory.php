<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ArchiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Kita akan override ini di Seeder, tapi ini default-nya
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'slug' => fake()->slug(),
            'file_path' => 'archives/dummy.pdf', 
            'file_size' => fake()->numberBetween(100, 50000), // dalam KB
            'download_count' => fake()->numberBetween(0, 1000),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'user_id' => 1, // Asumsi ada user ID 1 (Admin)
        ];
    }
}