<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fact-check fix (2026-10-18): the Kyoto/Osaka samples told Filipinos to get a
 * Japan visa "through an accredited agency". Independent travellers now apply
 * through the Japan Visa Application Centre. Updates the already-seeded public
 * samples only (created_by = null) — users' own copies are their data.
 */
return new class extends Migration
{
    private const OLD_HICCUP = 'Japan needs a visa for a Philippine passport — apply through an accredited agency several weeks before you fly.';
    private const NEW_HICCUP = 'Japan needs a visa for a Philippine passport — apply through the Japan Visa Application Centre several weeks before you fly (package tours can use the eVisa).';

    public function up(): void
    {
        $sampleIds = DB::table('trips')->whereNull('created_by')->pluck('id');

        DB::table('trip_days')->whereIn('trip_id', $sampleIds)->where('hiccups', 'like', '%accredited agency%')
            ->get(['id', 'hiccups'])
            ->each(function ($day) {
                $hiccups = array_map(fn ($h) => $h === self::OLD_HICCUP ? self::NEW_HICCUP : $h, json_decode($day->hiccups, true) ?: []);
                DB::table('trip_days')->where('id', $day->id)->update(['hiccups' => json_encode($hiccups, JSON_UNESCAPED_UNICODE)]);
            });

        DB::table('budget_lines')->whereIn('trip_id', $sampleIds)->where('label', 'Japan visa via accredited agency')
            ->update(['label' => 'Japan visa + application centre fee', 'note' => 'PH passport · check the current fee']);
    }

    public function down(): void
    {
        // Text correction only; nothing to undo.
    }
};
