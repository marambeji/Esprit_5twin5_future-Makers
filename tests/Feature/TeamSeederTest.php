<?php

namespace Tests\Feature;

use App\Models\Delivery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_seeders_generate_delivery_references_without_duplicates(): void
    {
        $this->seed();
        $this->assertDatabaseCount('deliveries', 9);
        $references = Delivery::pluck('reference')->all();
        $this->assertCount(9, array_unique($references));
        foreach ($references as $reference) {
            $this->assertMatchesRegularExpression('/^LIV-\d{8}-\d{4}$/', $reference);
        }
        $this->assertDatabaseCount('producteurs', 5);
        $this->assertDatabaseCount('fermes', 15);
        $this->assertDatabaseCount('certifications', 1);
        $this->seed();
        $this->assertSame($references, Delivery::pluck('reference')->all());
        $this->assertDatabaseCount('fermes', 15);
    }
}
