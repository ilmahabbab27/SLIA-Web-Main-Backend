<?php

namespace App\Http\Controllers;

use App\Models\BaeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BaeDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = BaeDocument::query();

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        return response()->json(
            $query->orderBy('sort_order')
                ->orderByDesc('uploaded_at')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules(true));
        $validated = $this->storeUpload($validated);

        $document = BaeDocument::create($validated);
        return response()->json($document, 201);
    }

    public function show(BaeDocument $baeDocument)
    {
        return response()->json($baeDocument);
    }

    public function update(Request $request, BaeDocument $baeDocument)
    {
        $validated = $request->validate($this->rules(false));
        $validated = $this->storeUpload($validated);

        $baeDocument->update($validated);
        return response()->json($baeDocument->fresh());
    }

    public function destroy(BaeDocument $baeDocument)
    {
        $baeDocument->delete();
        return response()->json(null, 204);
    }

    private function rules($creating)
    {
        $required = $creating ? 'required' : 'nullable';

        return [
            'category' => [$required, 'string', 'in:applications,notices,references'],
            'title' => [$required, 'string', 'max:255'],
            'meta' => ['nullable', 'string', 'max:80'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'file_src' => ['nullable', 'string'],
            'external_url' => ['nullable', 'string'],
            'uploaded_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function storeUpload(array $data)
    {
        if (!empty($data['file_src']) && $this->isDataUrl($data['file_src'])) {
            $data['file_src'] = $this->storeDataUrl($data['file_src'], $data['file_name'] ?? null);
        }

        return $data;
    }

    private function isDataUrl($value)
    {
        return is_string($value) && strpos($value, 'data:') === 0;
    }

    private function storeDataUrl($dataUrl, $originalName = null)
    {
        if (!preg_match('/^data:([^;]+);base64,(.+)$/', $dataUrl, $matches)) {
            return $dataUrl;
        }

        $content = base64_decode($matches[2]);

        if ($content === false) {
            return $dataUrl;
        }

        $extension = $originalName && pathinfo($originalName, PATHINFO_EXTENSION)
            ? strtolower(pathinfo($originalName, PATHINFO_EXTENSION))
            : 'bin';

        $directory = public_path('uploads/bae');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = 'document-' . Str::random(24) . '.' . $extension;
        file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $content);

        return url('public/uploads/bae/' . $filename);
    }
}
