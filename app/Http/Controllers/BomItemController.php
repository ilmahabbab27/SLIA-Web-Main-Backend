<?php

namespace App\Http\Controllers;

use App\Models\BomItem;
use Illuminate\Http\Request;

class BomItemController extends Controller
{
    public function index(Request $request)
    {
        $query = BomItem::query();

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        return response()->json(
            $query->orderBy('sort_order')
                ->orderByDesc('created_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(true));

        $item = BomItem::create($validated);
        return response()->json($item, 201);
    }

    public function show(BomItem $bomItem)
    {
        return response()->json($bomItem);
    }

    public function update(Request $request, BomItem $bomItem)
    {
        $validated = $request->validate($this->rules(false));

        $bomItem->update($validated);
        return response()->json($bomItem->fresh());
    }

    public function destroy(BomItem $bomItem)
    {
        $bomItem->delete();
        return response()->json(null, 204);
    }

    private function rules($creating)
    {
        $required = $creating ? 'required' : 'nullable';

        return [
            'category' => [$required, 'string', 'in:programs,slapm,slaud,slaia,slaac'],
            'title' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'image' => ['nullable', 'string'],
            'link' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
