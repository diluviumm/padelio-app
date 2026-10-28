<?php

namespace Tests\Feature;

use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoSeeder::class);
    }

    public function test_public_information_pages_render(): void
    {
        $this->get('/about')->assertOk()->assertSee('Tentang Padelio');
        $this->get('/privacy')->assertOk()->assertSee('Kebijakan Privasi');
        $this->get('/terms')->assertOk()->assertSee('Ketentuan Layanan');
    }
}
