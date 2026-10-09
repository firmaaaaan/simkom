<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_renders_custom_design(): void
    {
        $this->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('SimKom')
            ->assertSee('Ke Beranda')
            ->assertSee('404');
    }

    public function test_403_page_renders_custom_design(): void
    {
        $user = User::create(['name' => 'Tanpa Izin', 'email' => 'tanpa-izin@uji.test', 'password' => 'rahasia123']);

        $this->actingAs($user)
            ->get(route('users.export'))
            ->assertForbidden()
            ->assertSee('Akses Ditolak')
            ->assertSee('tidak memiliki izin')
            ->assertSee('403');
    }
}
