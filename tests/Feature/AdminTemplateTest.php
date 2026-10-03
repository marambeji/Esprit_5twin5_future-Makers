<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminTemplateTest extends TestCase
{
    public function test_admin_template_uses_its_own_layout_and_assets(): void
    {
        $this->get('/admin')->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertOk()
            ->assertSee('Tableau de bord NutriTrace')
            ->assertSee('sidebar-wrapper', false)
            ->assertSee('Mazer')->assertDontSee('staradmin', false)
            ->assertSee('/build/assets/', false)
            ->assertDontSee('loader_bg', false);
        $this->get('/')->assertOk()->assertDontSee('sidebar-wrapper', false);
    }
}
