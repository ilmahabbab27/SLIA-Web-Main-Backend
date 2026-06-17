<?php

namespace App\Http\Controllers;

use App\Models\BapPublication;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BapPublicationController extends Controller
{
    public function index(Request $request)
    {
        $query = BapPublication::query();

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
        $validated = $this->storeUploads($validated);

        $publication = BapPublication::create($validated);
        return response()->json($publication, 201);
    }

    public function show(BapPublication $bapPublication)
    {
        return response()->json($bapPublication);
    }

    public function update(Request $request, BapPublication $bapPublication)
    {
        $validated = $request->validate($this->rules(false));
        $validated = $this->storeUploads($validated);

        $bapPublication->update($validated);
        return response()->json($bapPublication->fresh());
    }

    public function destroy(BapPublication $bapPublication)
    {
        $bapPublication->delete();
        return response()->json(null, 204);
    }

    private function rules($creating)
    {
        $required = $creating ? 'required' : 'nullable';

        return [
            'category' => [$required, 'string', 'in:the-architect,vastu,built-environment,now-architecture,archive-journal,year-book'],
            'title' => [$required, 'string', 'max:255'],
            'volume' => ['nullable', 'string', 'max:255'],
            'uploaded_at' => ['nullable', 'date'],
            'cover_src' => ['nullable', 'string'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'file_src' => ['nullable', 'string'],
            'external_url' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    private function storeUploads(array $data)
    {
        if (!empty($data['cover_src']) && $this->isDataUrl($data['cover_src'])) {
            $data['cover_src'] = $this->storeDataUrl($data['cover_src'], 'cover');
        }

        if (!empty($data['file_src']) && $this->isDataUrl($data['file_src'])) {
            $data['file_src'] = $this->storeDataUrl($data['file_src'], 'publication', $data['file_name'] ?? null);
        }

        return $data;
    }

    private function isDataUrl($value)
    {
        return is_string($value) && strpos($value, 'data:') === 0;
    }

    private function storeDataUrl($dataUrl, $prefix, $originalName = null)
    {
        if (!preg_match('/^data:([^;]+);base64,(.+)$/', $dataUrl, $matches)) {
            return $dataUrl;
        }

        $mime = $matches[1];
        $content = base64_decode($matches[2]);

        if ($content === false) {
            return $dataUrl;
        }

        $extension = $this->extensionForMime($mime, $originalName);
        $directory = public_path('uploads/bap');

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = $prefix . '-' . Str::random(24) . '.' . $extension;
        file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $content);

        return url('uploads/bap/' . $filename);
    }

    private function extensionForMime($mime, $originalName = null)
    {
        if ($originalName && pathinfo($originalName, PATHINFO_EXTENSION)) {
            return strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        }

        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        return $map[$mime] ?? 'bin';
    }
}
