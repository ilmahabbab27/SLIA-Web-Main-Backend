<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /** GET /api/faqs */
    public function index()
    {
        return response()->json(Faq::orderBy('sort_order')->get());
    }

    /** POST /api/faqs */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question'   => 'required|string',
            'answer'     => 'required|string',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $faq = Faq::create($validated);
        return response()->json($faq, 201);
    }

    /** GET /api/faqs/{faq} */
    public function show(Faq $faq)
    {
        return response()->json($faq);
    }

    /** PUT /api/faqs/{faq} */
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question'   => 'nullable|string',
            'answer'     => 'nullable|string',
            'is_active'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $faq->update($validated);
        return response()->json($faq->fresh());
    }

    /** DELETE /api/faqs/{faq} */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->json(null, 204);
    }
}
