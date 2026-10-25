<?php

namespace App\Support;

/**
 * Optional "anything we should plan around?" ticks from the trip planner. Only the ticked keys are stored (never free
 * text, never a diagnosis). The AI gets the plain instruction, not the label, so no condition name leaves the app.
 */
class TravelNeeds
{
    /** key => [label shown to the traveller, instruction given to the day planner] */
    public const OPTIONS = [
        'long_walks' => ['Gets tired on long walks', 'keep walking to short stretches (about 10-15 minutes at a time), put a seat or cafe stop between sights, prefer taxis or rides over long walks'],
        'stairs_hills' => ['Avoid many stairs or steep hills', 'avoid sights that need many stairs or steep climbs; choose step-free or lift-accessible places and say when there are stairs'],
        'joints' => ['Joint pain or arthritis (needs rest stops)', 'plan a rest or sit-down stop at least every 90 minutes, avoid long standing queues, keep the pace unhurried'],
        'heart_bp' => ['High blood pressure or heart condition (keep it gentle)', 'keep days gentle with no strenuous climbs, avoid midday heat and very crowded places, include shade and rest, and avoid high altitude'],
        'temperature' => ['Sensitive to heat or cold', 'avoid exposed outdoor stretches at the hottest or coldest part of the day, favour indoor or covered options then, and mention what to wear'],
        'mobility_aid' => ['Uses a wheelchair or walker', 'only suggest step-free, accessible places and accessible transport, and note where accessibility is uncertain'],
        'motion_sick' => ['Motion sickness', 'avoid long winding road, boat or cable-car legs where possible, keep transfers short, and note a gentler alternative'],
        'food_needs' => ['Food allergies or dietary needs', 'for meals, prefer places that cater to allergies and dietary needs and say to confirm ingredients with the restaurant'],
        'unwell' => ['Recovering or not feeling well', 'keep the day light and close to the hotel with easy exits and plenty of downtime'],
        'kids_elders' => ['Travelling with young kids or older relatives', 'choose family-friendly, easy places with seating and toilets nearby, early dinners, and no late nights'],
    ];

    /** @return array<int, string> */
    public static function keys(): array
    {
        return array_keys(self::OPTIONS);
    }

    /** Plain planning instructions for the ticked keys (unknown keys ignored). @return array<int, string> */
    public static function instructions(array $keys): array
    {
        return collect($keys)->filter(fn ($k) => isset(self::OPTIONS[$k]))->unique()->map(fn ($k) => self::OPTIONS[$k][1])->values()->all();
    }
}
