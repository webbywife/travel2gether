<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Daily exchange rates for the budget's "total in PHP". Free, keyless sources:
 * ExchangeRate-API's open endpoint first, the ECB via Frankfurter as a fallback.
 * Returns null when no source answers — callers must show "—", never a guess.
 */
class ExchangeRates
{
    /** Currencies a trip can be planned in (each destination's local one, plus home and the majors). */
    public const CURRENCIES = [
        'PHP', 'USD', 'EUR', 'GBP', 'JPY', 'KRW', 'CNY', 'HKD', 'MOP', 'TWD',
        'SGD', 'THB', 'MYR', 'IDR', 'VND', 'AED', 'TRY', 'AUD', 'CAD', 'MXN',
    ];

    private const OPEN_ER = 'https://open.er-api.com/v6/latest/';
    private const FRANKFURTER = 'https://api.frankfurter.dev/v1/latest';

    /** @return array{rate: float, date: string, source: string}|null  1 $from = rate $to */
    public function rate(string $from, string $to = 'PHP'): ?array
    {
        $from = strtoupper($from);
        $to = strtoupper($to);
        if (! in_array($from, self::CURRENCIES, true) || ! in_array($to, self::CURRENCIES, true)) {
            return null;
        }
        if ($from === $to) {
            return ['rate' => 1.0, 'date' => now()->toDateString(), 'source' => 'same currency'];
        }

        $key = "fx:{$from}:{$to}";
        if ($hit = Cache::get($key)) {
            return $hit;
        }

        // After a full failure, don't retry for 10 minutes (keeps trip pages fast while the APIs are down).
        if (Cache::has('fx:down')) {
            return null;
        }

        $result = $this->fromOpenEr($from, $to) ?? $this->fromFrankfurter($from, $to);
        $result ? Cache::put($key, $result, now()->addHours(12)) : Cache::put('fx:down', true, now()->addMinutes(10));

        return $result;
    }

    private function fromOpenEr(string $from, string $to): ?array
    {
        $res = rescue(fn () => Http::timeout(6)->get(self::OPEN_ER . $from), null, false);
        $rate = $res?->successful() && $res->json('result') === 'success' ? $res->json("rates.{$to}") : null;

        return is_numeric($rate) && $rate > 0
            ? ['rate' => (float) $rate, 'date' => date('Y-m-d', (int) $res->json('time_last_update_unix')), 'source' => 'ExchangeRate-API']
            : null;
    }

    private function fromFrankfurter(string $from, string $to): ?array
    {
        $res = rescue(fn () => Http::timeout(6)->get(self::FRANKFURTER, ['from' => $from, 'to' => $to]), null, false);
        $rate = $res?->successful() ? $res->json("rates.{$to}") : null;

        return is_numeric($rate) && $rate > 0
            ? ['rate' => (float) $rate, 'date' => (string) $res->json('date'), 'source' => 'European Central Bank']
            : null;
    }
}
