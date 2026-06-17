<?php

namespace App\Http\Controllers;

use App\Models\AwardCategory;
use Illuminate\Http\Request;

class AwardCategoryController extends Controller
{
    /** GET /api/award-categories */
    public function index(Request $request)
    {
        $query = AwardCategory::query();

        if ($request->filled('year')) {
            $query->where('year', $request->query('year'));
        }

        return response()->json(
            $query->orderByDesc('year')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
        );
    }

    /** POST /api/award-categories */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'year'              => 'required|string|max:20',
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'guideline'         => 'nullable|string',
            'guideline_attachment_name' => 'nullable|string|max:255',
            'guideline_attachment' => 'nullable|string',
            'general_guideline' => 'nullable|string',
            'is_active'         => 'nullable|boolean',
            'sort_order'        => 'nullable|integer|min:0',
        ]);

        $awardCategory = AwardCategory::create($validated);
        return response()->json($awardCategory, 201);
    }

    /** GET /api/award-categories/{awardCategory} */
    public function show(AwardCategory $awardCategory)
    {
        return response()->json($awardCategory);
    }

    /** PUT /api/award-categories/{awardCategory} */
    public function update(Request $request, AwardCategory $awardCategory)
    {
        $validated = $request->validate([
            'year'              => 'nullable|string|max:20',
            'name'              => 'nullable|string|max:255',
            'description'       => 'nullable|string',
            'guideline'         => 'nullable|string',
            'guideline_attachment_name' => 'nullable|string|max:255',
            'guideline_attachment' => 'nullable|string',
            'general_guideline' => 'nullable|string',
            'is_active'         => 'nullable|boolean',
            'sort_order'        => 'nullable|integer|min:0',
        ]);

        $awardCategory->update($validated);
        return response()->json($awardCategory->fresh());
    }

    /** DELETE /api/award-categories/{awardCategory} */
    public function destroy(AwardCategory $awardCategory)
    {
        $awardCategory->delete();
        return response()->json(null, 204);
    }
}
