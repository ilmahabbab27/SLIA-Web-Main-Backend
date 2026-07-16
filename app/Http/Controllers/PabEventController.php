<?php

namespace App\Http\Controllers;

use App\Models\PabEvent;
use Illuminate\Http\Request;

class PabEventController extends Controller
{
    public function index()
    {
        return response()->json(PabEvent::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'event_time' => 'nullable|string',
            'venue' => 'nullable|string',
            'link' => 'nullable|string',
            'file_name' => 'nullable|string',
            'file_src' => 'nullable|string',
            'image_name' => 'nullable|string',
            'image_src' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $event = PabEvent::create($validated);
        return response()->json($event, 201);
    }

    public function show($id)
    {
        $event = PabEvent::findOrFail($id);
        return response()->json($event);
    }

    public function update(Request $request, $id)
    {
        $event = PabEvent::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'event_time' => 'nullable|string',
            'venue' => 'nullable|string',
            'link' => 'nullable|string',
            'file_name' => 'nullable|string',
            'file_src' => 'nullable|string',
            'image_name' => 'nullable|string',
            'image_src' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $event->update($validated);
        return response()->json($event);
    }

    public function destroy($id)
    {
        PabEvent::findOrFail($id)->delete();
        return response()->json(null, 204);
    }
}
