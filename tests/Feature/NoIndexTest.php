<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_admin_and_root_responses_are_marked_noindex(): void
    {
        foreach (['/', '/up', '/api/products', '/admin/login'] as $path) {
            $this->get($path)->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        }
    }
}
