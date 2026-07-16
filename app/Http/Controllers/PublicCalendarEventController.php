<?php

namespace App\Http\Controllers;

use App\Models\PublicCalendarEvent;
use Illuminate\Http\Request;

class PublicCalendarEventController extends Controller
{
    /** GET /api/public-calendar-events */
    public function index()
    {
        return response()->json(
            PublicCalendarEvent::query()
                ->orderBy('event_date')
                ->orderBy('event_time')
                ->get()
        );
    }

    /** POST /api/public-calendar-events */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $event = PublicCalendarEvent::create($validated);

        return response()->json($event, 201);
    }

    /** GET /api/public-calendar-events/{public_calendar_event} */
    public function show(PublicCalendarEvent $public_calendar_event)
    {
        return response()->json($public_calendar_event);
    }

    /** PUT /api/public-calendar-events/{public_calendar_event} */
    public function update(Request $request, PublicCalendarEvent $public_calendar_event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'venue' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $public_calendar_event->update($validated);

        return response()->json($public_calendar_event->fresh());
    }

    /** DELETE /api/public-calendar-events/{public_calendar_event} */
    public function destroy(PublicCalendarEvent $public_calendar_event)
    {
        $public_calendar_event->delete();

        return response()->json(null, 204);
    }
}
