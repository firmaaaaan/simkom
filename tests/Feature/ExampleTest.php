<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('jadwal.index'));
    }

    public function test_daftar_komputer_page_returns_a_successful_response(): void
    {
        $response = $this->get('/daftar-komputer');

        $response->assertStatus(200);
    }
}
