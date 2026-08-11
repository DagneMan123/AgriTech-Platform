<?php

namespace App\Http\Controllers\Api\Expert;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\TrainingMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TrainingController extends Controller
{
    /**
     * Get all training materials
     */
    public function index(Request $request)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert) {
            return response()->json([
                'success' => false,
                'message' => 'Expert not found',
            ], 404);
        }

        $validated = $request->validate([
            'category' => 'sometimes|string',
            'status' => 'sometimes|in:draft,published,archived',
            'sort' => 'sometimes|in:newest,oldest,views_high,views_low',
            'search' => 'sometimes|string',
        ]);

        $query = $expert->trainingMaterials();

        if (isset($validated['category'])) {
            $query->where('category', $validated['category']);
        }

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['search'])) {
            $query->where('title', 'ilike', '%' . $validated['search'] . '%')
                ->orWhere('description', 'ilike', '%' . $validated['search'] . '%');
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'oldest' => $query->orderBy('created_at', 'asc'),
                'views_high' => $query->orderBy('views', 'desc'),
                'views_low' => $query->orderBy('views', 'asc'),
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $materials = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $materials,
        ]);
    }

    /**
     * Create training material
     */
    public function store(Request $request)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert) {
            return response()->json([
                'success' => false,
                'message' => 'Expert not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'content' => 'required|string',
            'file' => 'sometimes|file|mimes:pdf,doc,docx,ppt,pptx,xlsx|max:10240',
            'video_url' => 'sometimes|url',
            'thumbnail' => 'sometimes|file|mimes:jpeg,png,jpg|max:2048',
            'tags' => 'sometimes|array',
            'difficulty_level' => 'sometimes|in:beginner,intermediate,advanced',
            'target_audience' => 'sometimes|array',
            'status' => 'sometimes|in:draft,published',
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('training-materials', 'public');
        }

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('training-thumbnails', 'public');
        }

        $material = TrainingMaterial::create([
            'expert_id' => $expert->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'content' => $validated['content'],
            'file_path' => $filePath,
            'video_url' => $validated['video_url'] ?? null,
            'thumbnail_path' => $thumbnailPath,
            'tags' => json_encode($validated['tags'] ?? []),
            'difficulty_level' => $validated['difficulty_level'] ?? 'intermediate',
            'target_audience' => json_encode($validated['target_audience'] ?? []),
            'status' => $validated['status'] ?? 'draft',
            'views' => 0,
            'downloads' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Training material created successfully',
            'data' => $material,
        ], 201);
    }

    /**
     * Get material details
     */
    public function show(TrainingMaterial $material)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $material->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $material,
        ]);
    }

    /**
     * Update training material
     */
    public function update(Request $request, TrainingMaterial $material)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $material->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'sometimes|string',
            'content' => 'sometimes|string',
            'video_url' => 'sometimes|url',
            'tags' => 'sometimes|array',
            'difficulty_level' => 'sometimes|in:beginner,intermediate,advanced',
            'target_audience' => 'sometimes|array',
            'status' => 'sometimes|in:draft,published,archived',
        ]);

        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        if (isset($validated['target_audience'])) {
            $validated['target_audience'] = json_encode($validated['target_audience']);
        }

        $material->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Material updated successfully',
            'data' => $material,
        ]);
    }

    /**
     * Delete training material
     */
    public function destroy(TrainingMaterial $material)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $material->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found',
            ], 404);
        }

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }

        if ($material->thumbnail_path) {
            Storage::disk('public')->delete($material->thumbnail_path);
        }

        $material->delete();

        return response()->json([
            'success' => true,
            'message' => 'Material deleted successfully',
        ]);
    }

    /**
     * Download material file
     */
    public function download(TrainingMaterial $material)
    {
        if ($material->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'This material is not available for download',
            ], 403);
        }

        if (!$material->file_path) {
            return response()->json([
                'success' => false,
                'message' => 'No file available for this material',
            ], 404);
        }

        $material->increment('downloads');

        return Storage::disk('public')->download($material->file_path);
    }

    /**
     * Publish material
     */
    public function publish(TrainingMaterial $material)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $material->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found',
            ], 404);
        }

        $material->update([
            'status' => 'published',
            'published_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Material published successfully',
            'data' => $material,
        ]);
    }

    /**
     * Archive material
     */
    public function archive(TrainingMaterial $material)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $material->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Material not found',
            ], 404);
        }

        $material->update(['status' => 'archived']);

        return response()->json([
            'success' => true,
            'message' => 'Material archived successfully',
            'data' => $material,
        ]);
    }

    /**
     * Get popular materials
     */
    public function popular()
    {
        $materials = TrainingMaterial::where('status', 'published')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $materials,
        ]);
    }

    /**
     * Get materials by category
     */
    public function byCategory(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
        ]);

        $materials = TrainingMaterial::where('status', 'published')
            ->where('category', $validated['category'])
            ->orderBy('views', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $materials,
        ]);
    }

    /**
     * Search materials
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $materials = TrainingMaterial::where('status', 'published')
            ->where(function ($query) use ($validated) {
                $query->where('title', 'ilike', '%' . $validated['q'] . '%')
                    ->orWhere('description', 'ilike', '%' . $validated['q'] . '%')
                    ->orWhere('tags', 'ilike', '%' . $validated['q'] . '%');
            })
            ->orderBy('views', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $materials,
        ]);
    }

    /**
     * Record material view
     */
    public function recordView(TrainingMaterial $material)
    {
        if ($material->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Material not available',
            ], 403);
        }

        $material->increment('views');

        return response()->json([
            'success' => true,
            'message' => 'View recorded',
        ]);
    }
}
