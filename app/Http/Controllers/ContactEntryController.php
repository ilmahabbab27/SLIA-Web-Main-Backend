<?php

namespace App\Http\Controllers;

use App\Models\ContactEntry;
use Illuminate\Http\Request;

class ContactEntryController extends Controller
{
    public function index()
    {
        return response()->json(ContactEntry::orderBy('sort_order')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'   => 'required|string|max:255',
            'label'      => 'required|string|max:255',
            'icon'       => 'required|string|in:address,phone,email,web,person,role',
            'text'       => 'required|string|max:1000',
            'href'       => 'nullable|string|max:1000',
            'is_active'  => 'nullable|boolean',
            'is_locked'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $entry = ContactEntry::create($validated);
        return response()->json($entry, 201);
    }

    public function show(ContactEntry $contactEntry)
    {
        return response()->json($contactEntry);
    }

    public function update(Request $request, ContactEntry $contactEntry)
    {
        $validated = $request->validate([
            'category'   => 'nullable|string|max:255',
            'label'      => 'nullable|string|max:255',
            'icon'       => 'nullable|string|in:address,phone,email,web,person,role',
            'text'       => 'nullable|string|max:1000',
            'href'       => 'nullable|string|max:1000',
            'is_active'  => 'nullable|boolean',
            'is_locked'  => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $contactEntry->update($validated);
        return response()->json($contactEntry->fresh());
    }

    public function destroy(ContactEntry $contactEntry)
    {
        $contactEntry->delete();
        return response()->json(null, 204);
    }
}
