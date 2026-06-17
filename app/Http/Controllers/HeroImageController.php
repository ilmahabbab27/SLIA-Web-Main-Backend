<?php

namespace App\Http\Controllers;

use App\Models\HeroImage;
use Illuminate\Http\Request;

class HeroImageController extends Controller
{
    /**
     * GET /api/hero-images
     * Return all hero slides ordered by creation.
     */
    public function index()
    {
        return response()->json(HeroImage::orderBy('id')->get());
    }

    /**
     * POST /api/hero-images
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image'       => 'required|string',          // base64 data-URL
            'tag'         => 'nullable|string|max:255',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'link'        => 'nullable|string|max:500',
        ]);

        $heroImage = HeroImage::create($validated);

        return response()->json($heroImage, 201);
    }

    /**
     * GET /api/hero-images/{heroImage}
     */
    public function show(HeroImage $heroImage)
    {
        return response()->json($heroImage);
    }

    /**
     * PUT/PATCH /api/hero-images/{heroImage}
     */
    public function update(Request $request, HeroImage $heroImage)
    {
        $validated = $request->validate([
            'image'       => 'nullable|string',
            'tag'         => 'nullable|string|max:255',
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'link'        => 'nullable|string|max:500',
        ]);

        $heroImage->update($validated);

        return response()->json($heroImage->fresh());
    }

    /**
     * DELETE /api/hero-images/{heroImage}
     */
    public function destroy(HeroImage $heroImage)
    {
        $heroImage->delete();

        return response()->json(null, 204);
    }
}
