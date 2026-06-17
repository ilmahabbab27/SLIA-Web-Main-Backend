<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /** GET /api/events */
    public function index(Request $request)
    {
        $query = Event::query();
        if ($request->has('category')) {
            $category = $request->category;
            $flag = [
                'latest' => 'show_latest',
                'upcoming' => 'show_upcoming',
                'past' => 'show_past',
                'program' => 'show_program',
                'highlight' => 'show_highlight',
            ][$category] ?? null;

            $query->where(function ($q) use ($category, $flag) {
                if ($flag) {
                    $q->where($flag, true);
                }

                if ($category !== 'latest') {
                    $flag ? $q->orWhere('category', $category) : $q->where('category', $category);
                }
            });
        }
        return response()->json($query->orderBy('sort_order')->get());
    }

    /** POST /api/events */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image'      => 'nullable|string',
            'images'     => 'nullable|array|max:5',
            'images.*'   => 'nullable|string',
            'title'      => 'required|string|max:255',
            'subtitle'   => 'nullable|string|max:500',
            'description'=> 'nullable|string',
            'date'       => 'nullable|string|max:255',
            'venue'      => 'nullable|string|max:255',
            'link'       => 'nullable|string|max:500',
            'category'   => 'nullable|string|in:highlight,program,upcoming,past',
            'show_latest'=> 'nullable|boolean',
            'show_upcoming'=> 'nullable|boolean',
            'show_past'  => 'nullable|boolean',
            'show_program'=> 'nullable|boolean',
            'show_highlight'=> 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['image']) && !empty($validated['images'][0])) {
            $validated['image'] = $validated['images'][0];
        }

        $event = Event::create($validated);
        return response()->json($event, 201);
    }

    /** GET /api/events/{event} */
    public function show(Event $event)
    {
        return response()->json($event);
    }

    /** PUT /api/events/{event} */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'image'      => 'nullable|string',
            'images'     => 'nullable|array|max:5',
            'images.*'   => 'nullable|string',
            'title'      => 'nullable|string|max:255',
            'subtitle'   => 'nullable|string|max:500',
            'description'=> 'nullable|string',
            'date'       => 'nullable|string|max:255',
            'venue'      => 'nullable|string|max:255',
            'link'       => 'nullable|string|max:500',
            'category'   => 'nullable|string|in:highlight,program,upcoming,past',
            'show_latest'=> 'nullable|boolean',
            'show_upcoming'=> 'nullable|boolean',
            'show_past'  => 'nullable|boolean',
            'show_program'=> 'nullable|boolean',
            'show_highlight'=> 'nullable|boolean',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        if (array_key_exists('images', $validated) && empty($validated['image']) && !empty($validated['images'][0])) {
            $validated['image'] = $validated['images'][0];
        }

        $event->update($validated);
        return response()->json($event->fresh());
    }

    /** DELETE /api/events/{event} */
    public function destroy(Event $event)
    {
        $event->delete();
        return response()->json(null, 204);
    }
}
