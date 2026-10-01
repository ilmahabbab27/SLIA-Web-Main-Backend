<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use Illuminate\Http\Request;

class NewsItemController extends Controller
{
    /** GET /api/news-items */
    public function index(Request $request)
    {
        $query = NewsItem::orderBy('sort_order');
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        return response()->json($query->get());
    }

    /** POST /api/news-items */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'link'        => 'nullable|string|max:500',
            'type'        => 'nullable|string|in:news,highlight',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $item = NewsItem::create($validated);
        return response()->json($item, 201);
    }

    /** GET /api/news-items/{newsItem} */
    public function show(NewsItem $newsItem)
    {
        return response()->json($newsItem);
    }

    /** PUT /api/news-items/{newsItem} */
    public function update(Request $request, NewsItem $newsItem)
    {
        $validated = $request->validate([
            'title'       => 'nullable|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|string',
            'link'        => 'nullable|string|max:500',
            'type'        => 'nullable|string|in:news,highlight',
            'is_active'   => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
        ]);

        $newsItem->update($validated);
        return response()->json($newsItem->fresh());
    }

    /** DELETE /api/news-items/{newsItem} */
    public function destroy($newsItem)
    {
        $deleted = NewsItem::whereKey($newsItem)->delete();

        if ($deleted === 0) {
            return response()->json([
                'message' => 'News item not found.',
            ], 404);
        }

        return response()->json(null, 204);
    }
}
