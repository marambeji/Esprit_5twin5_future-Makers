<?php

namespace Tests\Feature;

use Tests\TestCase;

class TemplateTest extends TestCase
{
    public function test_template_pages_render_with_compiled_assets_and_shared_navigation(): void
    {
        foreach (['/', '/dashboard', '/about', '/service', '/testimonials', '/blog', '/contact'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('Nutri<span>trace</span>', false)
                ->assertSee('/build/assets/', false)
                ->assertSee('Free html Templates')
                ->assertDontSee('href="index.html"', false)
                ->assertDontSee('src="images/', false);
        }
    }
}
