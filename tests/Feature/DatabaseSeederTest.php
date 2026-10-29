<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_seeding_creates_users_stores_and_balances_with_generated_ids(): void
    {
        $this->seed();

        $this->assertDatabaseHas('users', ['email' => 'admin@blue.com']);
        $this->assertDatabaseCount('stores', 10);
        $this->assertDatabaseCount('store_ballances', 10);
    }
}
