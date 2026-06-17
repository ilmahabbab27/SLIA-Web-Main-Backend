<?php

namespace App\Http\Controllers;

use App\Models\TickerItem;
use Illuminate\Http\Request;

class TickerController extends Controller
{
    public function index()
    {
        return response()->json(TickerItem::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text'       => 'required|string|max:500',
            'link'       => 'nullable|string|max:500',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $item = TickerItem::create($validated);
        return response()->json($item, 201);
    }

    public function show(TickerItem $tickerItem)
    {
        return response()->json($tickerItem);
    }

    public function update(Request $request, TickerItem $tickerItem)
    {
        $validated = $request->validate([
            'text'       => 'nullable|string|max:500',
            'link'       => 'nullable|string|max:500',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $tickerItem->update($validated);
        return response()->json($tickerItem->fresh());
    }

    public function destroy(TickerItem $tickerItem)
    {
        $tickerItem->delete();
        return response()->json(null, 204);
    }
}
