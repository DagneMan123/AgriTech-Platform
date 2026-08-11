<?php

namespace App\Http\Controllers\Api\Expert;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    /**
     * Get all articles
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
            'status' => 'sometimes|in:draft,published,archived',
            'sort' => 'sometimes|in:newest,oldest,views_high,views_low,likes_high',
            'search' => 'sometimes|string',
        ]);

        $query = $expert->articles();

        if (isset($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (isset($validated['search'])) {
            $query->where('title', 'ilike', '%' . $validated['search'] . '%')
                ->orWhere('content', 'ilike', '%' . $validated['search'] . '%');
        }

        if (isset($validated['sort'])) {
            match ($validated['sort']) {
                'newest' => $query->orderBy('created_at', 'desc'),
                'oldest' => $query->orderBy('created_at', 'asc'),
                'views_high' => $query->orderBy('views', 'desc'),
                'views_low' => $query->orderBy('views', 'asc'),
                'likes_high' => $query->orderBy('likes', 'desc'),
            };
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $articles = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Create article
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
            'content' => 'required|string',
            'excerpt' => 'sometimes|string|max:500',
            'topic' => 'required|string',
            'tags' => 'sometimes|array',
            'featured_image' => 'sometimes|file|mimes:jpeg,png,jpg|max:2048',
            'status' => 'sometimes|in:draft,published',
        ]);

        $imagePath = null;
        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('articles', 'public');
        }

        $article = Article::create([
            'expert_id' => $expert->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'excerpt' => $validated['excerpt'] ?? null,
            'topic' => $validated['topic'],
            'tags' => json_encode($validated['tags'] ?? []),
            'featured_image' => $imagePath,
            'status' => $validated['status'] ?? 'draft',
            'views' => 0,
            'likes' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Article created successfully',
            'data' => $article,
        ], 201);
    }

    /**
     * Get article details
     */
    public function show(Article $article)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $article->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    /**
     * Update article
     */
    public function update(Request $request, Article $article)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $article->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'excerpt' => 'sometimes|string|max:500',
            'topic' => 'sometimes|string',
            'tags' => 'sometimes|array',
            'status' => 'sometimes|in:draft,published,archived',
        ]);

        if (isset($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }

        $article->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Article updated successfully',
            'data' => $article,
        ]);
    }

    /**
     * Delete article
     */
    public function destroy(Article $article)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $article->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }

        $article->delete();

        return response()->json([
            'success' => true,
            'message' => 'Article deleted successfully',
        ]);
    }

    /**
     * Publish article
     */
    public function publish(Article $article)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $article->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }

        $article->update([
            'status' => 'published',
            'published_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Article published successfully',
            'data' => $article,
        ]);
    }

    /**
     * Archive article
     */
    public function archive(Article $article)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $article->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }

        $article->update(['status' => 'archived']);

        return response()->json([
            'success' => true,
            'message' => 'Article archived successfully',
        ]);
    }

    /**
     * Get popular articles
     */
    public function popular()
    {
        $articles = Article::where('status', 'published')
            ->orderBy('views', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Get articles by topic
     */
    public function byTopic(Request $request)
    {
        $validated = $request->validate([
            'topic' => 'required|string',
        ]);

        $articles = Article::where('status', 'published')
            ->where('topic', $validated['topic'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Search articles
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $articles = Article::where('status', 'published')
            ->where(function ($query) use ($validated) {
                $query->where('title', 'ilike', '%' . $validated['q'] . '%')
                    ->orWhere('content', 'ilike', '%' . $validated['q'] . '%')
                    ->orWhere('tags', 'ilike', '%' . $validated['q'] . '%');
            })
            ->orderBy('views', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $articles,
        ]);
    }

    /**
     * Record article view
     */
    public function recordView(Article $article)
    {
        if ($article->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Article not available',
            ], 403);
        }

        $article->increment('views');

        return response()->json([
            'success' => true,
            'message' => 'View recorded',
        ]);
    }

    /**
     * Like article
     */
    public function like(Article $article)
    {
        if ($article->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Article not available',
            ], 403);
        }

        $article->increment('likes');

        return response()->json([
            'success' => true,
            'message' => 'Like recorded',
            'data' => [
                'likes' => $article->likes,
            ],
        ]);
    }

    /**
     * Get article statistics
     */
    public function statistics(Article $article)
    {
        $expert = Expert::where('user_id', Auth::id())->first();

        if (!$expert || $article->expert_id !== $expert->id) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found',
            ], 404);
        }

        $stats = [
            'views' => $article->views,
            'likes' => $article->likes,
            'engagement_rate' => $article->views > 0 ? ($article->likes / $article->views) * 100 : 0,
            'published_date' => $article->published_date,
            'days_published' => $article->published_date ? now()->diffInDays($article->published_date) : 0,
            'comments_count' => $article->comments()->count(),
            'shares' => $article->shares ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
