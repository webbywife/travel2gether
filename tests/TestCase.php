<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    /** Exchange rates the fake rate API answers with, e.g. ['JPY' => ['PHP' => 0.4]]. Empty = API down. */
    protected array $fxRates = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Never call the real exchange-rate APIs from tests.
        Http::fake([
            'open.er-api.com/*' => function (Request $request) {
                $base = basename(parse_url($request->url(), PHP_URL_PATH));

                return isset($this->fxRates[$base])
                    ? Http::response(['result' => 'success', 'time_last_update_unix' => 1792281600, 'rates' => $this->fxRates[$base]])
                    : Http::response(['result' => 'error'], 500);
            },
            'api.frankfurter.dev/*' => Http::response([], 500),
        ]);
    }
}
