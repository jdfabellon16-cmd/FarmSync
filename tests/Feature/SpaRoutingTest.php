<?php

namespace Tests\Feature;

use Tests\TestCase;

class SpaRoutingTest extends TestCase
{
    public function test_react_routes_are_served_by_the_laravel_spa_shell(): void
    {
        foreach (['/login', '/farms', '/reports/production'] as $path) {
            $response = $this->get($path);
            $response->assertOk()
                ->assertSee('id="app"', false);

            $this->assertMatchesRegularExpression(
                '#(?:/build/assets/app-[^"]+\.js|/resources/js/app\.jsx)#',
                $response->getContent(),
            );
        }
    }

    public function test_api_paths_are_not_rewritten_to_the_react_spa(): void
    {
        $this->getJson('/api/v1/unknown')
            ->assertNotFound()
            ->assertJsonMissingPath('id');
    }
}
