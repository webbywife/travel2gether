<?php

namespace App\Http\Controllers;

use App\Models\Stop;
use App\Models\Trip;
use App\Models\TripDay;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * "📅 Add to calendar" — the itinerary as an iCalendar (.ics) file that
 * Google Calendar, Apple Calendar and Outlook import. One event per stop,
 * using the option currently picked, plus an all-day banner per day.
 *
 * Times are "floating" (no time zone): 09:00 stays 9 AM wherever the phone
 * is, which is what you want for an itinerary in the destination's local time.
 */
class TripCalendarController extends Controller
{
    private const DEFAULT_MINUTES = 90;

    public function show(Request $request, Trip $trip): Response
    {
        abort_unless($trip->canView($request->user()), 404);

        $data = $request->validate([
            'day' => ['nullable', 'integer', 'min:1'],
            // "stopId:optionId,…" — the picks shown on the page (needed for per-device picks on samples)
            'picks' => ['nullable', 'string', 'max:4000', 'regex:/^(\d+:\d+)(,\d+:\d+)*$/'],
        ]);

        $trip->load(['days.stops.options', 'picks.picker:id,name']);
        $days = $trip->days->when(isset($data['day']), fn ($c) => $c->where('day_number', (int) $data['day']));
        abort_if($days->isEmpty(), 404);

        $chosen = $this->chosenOptions($trip, $data['picks'] ?? null);

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Travel2gether//Itinerary//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $this->text($trip->title),
        ];

        foreach ($days as $day) {
            array_push($lines, ...$this->dayBanner($trip, $day));
            $stops = $day->stops->sortBy('sort')->values();
            foreach ($stops as $i => $stop) {
                array_push($lines, ...$this->stopEvent($trip, $day, $stop, $stops->get($i + 1), $chosen));
            }
        }
        $lines[] = 'END:VCALENDAR';

        $name = Str::slug($trip->title) ?: 'trip';
        if (isset($data['day'])) {
            $name .= '-day-' . (int) $data['day'];
        }

        return response(implode("\r\n", array_map([$this, 'fold'], $lines)) . "\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $name . '.ics"',
            'Cache-Control' => 'no-store',
        ]);
    }

    /** stop_id => option (group pick, else the page's pick from ?picks, else the default). */
    private function chosenOptions(Trip $trip, ?string $picksParam): array
    {
        $optionsById = $trip->days->flatMap->stops->flatMap->options->keyBy('id');

        $fromPage = [];
        foreach (array_filter(explode(',', (string) $picksParam)) as $pair) {
            [$stopId, $optionId] = array_map('intval', explode(':', $pair));
            $opt = $optionsById->get($optionId);
            if ($opt && $opt->stop_id === $stopId) { // only options that really belong to that stop
                $fromPage[$stopId] = $opt;
            }
        }

        $out = [];
        foreach ($trip->days->flatMap->stops as $stop) {
            $group = $trip->picks->firstWhere('stop_id', $stop->id);
            $out[$stop->id] = ($group ? $optionsById->get($group->stop_option_id) : null)
                ?? $fromPage[$stop->id]
                ?? $stop->options->firstWhere('is_default_pick', true)
                ?? $stop->options->first();
        }

        return $out;
    }

    private function dayBanner(Trip $trip, TripDay $day): array
    {
        if (! $day->date) {
            return [];
        }

        return [
            'BEGIN:VEVENT',
            "UID:day-{$day->id}@travel2gether.net",
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART;VALUE=DATE:' . $day->date->format('Ymd'),
            'DTEND;VALUE=DATE:' . $day->date->copy()->addDay()->format('Ymd'),
            'SUMMARY:' . $this->text("Day {$day->day_number} · {$day->title}"),
            'DESCRIPTION:' . $this->text(trim(implode("\n\n", array_filter([
                $day->summary,
                $day->weather_note ? 'Weather: ' . $day->weather_note : null,
                $day->hotel_name ? 'Staying at ' . $day->hotel_name : null,
                route('trips.show', $trip) . '#' . $day->day_number,
            ])))),
            'TRANSP:TRANSPARENT',
            'END:VEVENT',
        ];
    }

    private function stopEvent(Trip $trip, TripDay $day, Stop $stop, ?Stop $next, array $chosen): array
    {
        if (! $day->date || ! preg_match('/^(\d{1,2}):(\d{2})(?:\s*[–\-—]\s*(\d{1,2}):(\d{2}))?/u', (string) $stop->time, $m)) {
            return []; // no clock time → it's covered by the day banner
        }

        $start = Carbon::parse($day->date->format('Y-m-d'))->setTime((int) $m[1], (int) $m[2]);
        if (! empty($m[3])) {
            $end = $start->copy()->setTime((int) $m[3], (int) $m[4]);
        } elseif ($next && preg_match('/^(\d{1,2}):(\d{2})/', (string) $next->time, $n)) {
            $end = $start->copy()->setTime((int) $n[1], (int) $n[2]);
        } else {
            $end = $start->copy()->addMinutes(self::DEFAULT_MINUTES);
        }
        if ($end <= $start) {
            $end = $start->copy()->addMinutes(self::DEFAULT_MINUTES);
        }
        if (! $end->isSameDay($start)) {
            $end = $start->copy()->setTime(23, 59);
        }

        $opt = $chosen[$stop->id] ?? null;
        $group = $trip->picks->firstWhere('stop_id', $stop->id);
        $summary = $opt ? "{$stop->title} — {$opt->name}" : $stop->title;

        $desc = array_filter([
            $opt?->note,
            $opt?->costRangeLabel() ? 'Cost: ' . $opt->costRangeLabel() : ($stop->cost_label ? 'Cost: ' . $stop->cost_label : null),
            $group?->picker ? 'Picked by ' . $group->picker->name : null,
            $stop->description,
            $stop->hiccup ? 'Heads-up: ' . $stop->hiccup : null,
            ($opt?->map_url ?: $stop->map_url) ? 'Map: ' . ($opt?->map_url ?: $stop->map_url) : null,
            route('trips.show', $trip) . '#' . $day->day_number,
        ]);

        $lines = [
            'BEGIN:VEVENT',
            "UID:stop-{$stop->id}@travel2gether.net",
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $start->format('Ymd\THis'),
            'DTEND:' . $end->format('Ymd\THis'),
            'SUMMARY:' . $this->text($summary),
            'DESCRIPTION:' . $this->text(implode("\n\n", $desc)),
        ];
        if ($opt?->name) {
            $lines[] = 'LOCATION:' . $this->text($opt->name);
        }
        $url = $opt?->map_url ?: $stop->map_url;
        if ($url && str_starts_with($url, 'https://')) {
            $lines[] = 'URL:' . $url;
        }
        $lines[] = 'END:VEVENT';

        return $lines;
    }

    /** RFC 5545 TEXT escaping. */
    private function text(?string $s): string
    {
        $s = str_replace(["\r\n", "\r"], "\n", (string) $s);

        return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\;', '\,', '\n'], $s);
    }

    /** RFC 5545 line folding at 75 octets, without splitting a UTF-8 character. */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }
        $out = [];
        $current = '';
        foreach (mb_str_split($line) as $ch) {
            if (strlen($current) + strlen($ch) > ($out ? 74 : 75)) {
                $out[] = $current;
                $current = '';
            }
            $current .= $ch;
        }
        $out[] = $current;

        return implode("\r\n ", $out);
    }
}
