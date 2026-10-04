<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CloudflareProxyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Route::get('/_ip', fn () => request()->ip());
    }

    public function test_the_real_visitor_ip_is_used_when_the_request_comes_through_cloudflare(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '172.70.1.1'])          // a Cloudflare edge
            ->withHeader('X-Forwarded-For', '180.191.73.235')
            ->get('/_ip')->assertSee('180.191.73.235');
    }

    public function test_a_forged_forwarded_header_from_anyone_else_is_ignored(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '91.92.42.62'])
            ->withHeader('X-Forwarded-For', '1.2.3.4')
            ->get('/_ip')->assertSee('91.92.42.62');
    }
}
