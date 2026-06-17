<?php

namespace App\Http\Controllers;

use App\Models\AnnualEvent;
use Illuminate\Http\Request;

class AnnualEventController extends Controller
{
    /** GET /api/annual-events */
    public function index()
    {
        return response()->json(
            AnnualEvent::orderByDesc('year')
                ->orderBy('sort_order')
                ->get()
        );
    }

    /** POST /api/annual-events */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year'        => 'required|string|max:20',
            'event_type'  => 'required|string|in:agm,inauguration,conference,exhibition,members-night',
            'title'       => 'required|string|max:255',
            'subtitle'    => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if (empty($validated['image']) && !empty($validated['images'][0])) {
            $validated['image'] = $validated['images'][0];
        }

        $annualEvent = AnnualEvent::create($validated);
        return response()->json($annualEvent, 201);
    }

    /** GET /api/annual-events/{annualEvent} */
    public function show(AnnualEvent $annualEvent)
    {
        return response()->json($annualEvent);
    }

    /** PUT /api/annual-events/{annualEvent} */
    public function update(Request $request, AnnualEvent $annualEvent)
    {
        $validated = $request->validate([
            'year'        => 'nullable|string|max:20',
            'event_type'  => 'nullable|string|in:agm,inauguration,conference,exhibition,members-night',
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'nullable|string',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        if (array_key_exists('images', $validated) && empty($validated['image']) && !empty($validated['images'][0])) {
            $validated['image'] = $validated['images'][0];
        }

        $annualEvent->update($validated);
        return response()->json($annualEvent->fresh());
    }

    /** DELETE /api/annual-events/{annualEvent} */
    public function destroy(AnnualEvent $annualEvent)
    {
        $annualEvent->delete();
        return response()->json(null, 204);
    }
}
