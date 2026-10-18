<?php

namespace App\Actions;

use App\Models\Trip;
use App\Services\ExchangeRates;
use Illuminate\Support\Facades\DB;

/**
 * Switching a trip's currency converts its numbers instead of just relabelling
 * them: budget lines and the option costs that inherit the trip currency
 * (options with their own explicit currency keep it). Uses today's rate;
 * returns false — and changes nothing — when no rate is available.
 */
class ConvertTripCurrency
{
    public function __construct(private ExchangeRates $rates) {}

    public function __invoke(Trip $trip, string $to): bool
    {
        $from = $trip->currency ?: 'USD';
        if ($from === $to) {
            return true;
        }

        $fx = $this->rates->rate($from, $to);
        if (! $fx) {
            return false;
        }
        $r = $fx['rate'];
        $conv = fn (?int $v) => $v === null ? null : (int) round($v * $r);

        DB::transaction(function () use ($trip, $to, $conv) {
            foreach ($trip->budgetLines as $line) {
                $line->update(['amount' => $conv($line->amount)]);
            }
            $trip->load('days.stops.options');
            foreach ($trip->days as $day) {
                foreach ($day->stops as $stop) {
                    foreach ($stop->options as $opt) {
                        if ($opt->currency === null) {
                            $opt->update(['cost_min' => $conv($opt->cost_min), 'cost_max' => $conv($opt->cost_max)]);
                        }
                    }
                }
            }
            $trip->update(['currency' => $to]);
        });

        return true;
    }
}
