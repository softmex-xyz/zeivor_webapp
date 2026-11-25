<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class CorsTest extends TestCase
{
    #[TestWith(['http://localhost:8000'])]
    #[TestWith(['https://softmex.xyz'])]
    public function test_registration_preflight_allows_configured_origins_with_credentials(string $origin): void
    {
        $response = $this->call('OPTIONS', '/users/register', server: [
            'HTTP_ORIGIN' => $origin,
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'content-type,x-xsrf-token',
        ]);

        $response->assertNoContent();
        $response->assertHeader('Access-Control-Allow-Origin', $origin);
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
        $this->assertStringContainsString('POST', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertStringContainsString('content-type', strtolower($response->headers->get('Access-Control-Allow-Headers')));
        $this->assertStringContainsString('x-xsrf-token', strtolower($response->headers->get('Access-Control-Allow-Headers')));
    }

    public function test_untrusted_origin_is_not_allowed(): void
    {
        $response = $this->call('OPTIONS', '/users/register', server: [
            'HTTP_ORIGIN' => 'https://untrusted.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $response->assertNoContent();
        $response->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_actual_api_response_allows_a_configured_origin(): void
    {
        Route::get('/api/cors-test', fn () => response()->json(['ok' => true]));

        $response = $this->get('/api/cors-test', ['Origin' => 'https://softmex.xyz']);

        $response->assertOk();
        $response->assertHeader('Access-Control-Allow-Origin', 'https://softmex.xyz');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_future_api_preflight_allows_a_configured_origin(): void
    {
        $response = $this->call('OPTIONS', '/api/users', server: [
            'HTTP_ORIGIN' => 'https://softmex.xyz',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
        ]);

        $response->assertNoContent();
        $response->assertHeader('Access-Control-Allow-Origin', 'https://softmex.xyz');
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_actual_api_response_does_not_allow_an_untrusted_origin(): void
    {
        Route::get('/api/cors-test', fn () => response()->json(['ok' => true]));

        $response = $this->get('/api/cors-test', ['Origin' => 'https://untrusted.example']);

        $response->assertOk();
        $response->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_unrelated_routes_do_not_receive_cors_headers(): void
    {
        Route::get('/cors-test', fn () => response()->noContent());

        $response = $this->get('/cors-test', ['Origin' => 'https://softmex.xyz']);

        $response->assertNoContent();
        $response->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
