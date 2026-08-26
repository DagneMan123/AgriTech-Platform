<?php

namespace App\Http\Controllers\Api\Expert;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\Consultation;
use App\Models\TrainingMaterial;
use App\Models\Article;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get expert dashboard overview
     */
    public function index(Request $request)
    {
        $expert = $request->user();

        // Consultation statistics
        $totalConsultations = Consultation::where('expert_id', $expert->id)->count();
        $pendingConsultations = Consultation::where('expert_id', $expert->id)
            ->where('status', 'pending')
            ->count();
        $completedConsultations = Consultation::where('expert_id', $expert->id)
            ->where('status', 'completed')
            ->count();

        // Training and content statistics
        $totalTrainingMaterials = TrainingMaterial::where('expert_id', $expert->id)->count();
        $publishedTraining = TrainingMaterial::where('expert_id', $expert->id)
            ->where('status', 'published')
            ->count();

        $totalArticles = Article::where('expert_id', $expert->id)->count();
        $publishedArticles = Article::where('expert_id', $expert->id)
            ->where('status', 'published')
            ->count();

        // Engagement metrics
        $totalViews = TrainingMaterial::where('expert_id', $expert->id)
            ->sum('views_count') + Article::where('expert_id', $expert->id)->sum('views_count');

        $totalInteractions = Consultation::where('expert_id', $expert->id)
            ->count() + TrainingMaterial::where('expert_id', $expert->id)->sum('engagement_count');

        // Recent consultations
        $recentConsultations = Consultation::where('expert_id', $expert->id)
            ->with('farmer')
            ->latest()
            ->limit(5)
            ->get();

        // Recent content
        $recentTraining = TrainingMaterial::where('expert_id', $expert->id)
            ->latest()
            ->limit(3)
            ->get();

        $recentArticles = Article::where('expert_id', $expert->id)
            ->latest()
            ->limit(3)
            ->get();

        // Consultations by status
        $consultationsByStatus = Consultation::where('expert_id', $expert->id)
            ->groupBy('status')
            ->selectRaw('status, count(*) as count')
            ->get();

        // Monthly consultation trend (database-agnostic)
        $consultationByMonth = Consultation::where('expert_id', $expert->id)
            ->whereDate('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(function($consultation) {
                return $consultation->created_at->format('Y-m');
            })
            ->map(function($group) {
                return [
                    'month' => $group->first()->created_at->format('Y-m'),
                    'consultations' => $group->count(),
                ];
            })
            ->values();

        // Expert rating (if available)
        $averageRating = Consultation::where('expert_id', $expert->id)
            ->whereNotNull('rating')
            ->avg('rating') ?? 0;

        return response()->json([
            'summary' => [
                'total_consultations' => $totalConsultations,
                'pending_consultations' => $pendingConsultations,
                'completed_consultations' => $completedConsultations,
                'total_training_materials' => $totalTrainingMaterials,
                'published_training' => $publishedTraining,
                'total_articles' => $totalArticles,
                'published_articles' => $publishedArticles,
                'total_views' => $totalViews,
                'total_interactions' => $totalInteractions,
                'average_rating' => round($averageRating, 2),
            ],
            'recent_consultations' => $recentConsultations,
            'recent_training' => $recentTraining,
            'recent_articles' => $recentArticles,
            'consultations_by_status' => $consultationsByStatus,
            'consultations_by_month' => $consultationByMonth,
        ]);
    }

    /**
     * Get consultations
     */
    public function consultations(Request $request)
    {
        $expert = $request->user();
        $status = $request->query('status');

        $query = Consultation::where('expert_id', $expert->id)
            ->with('farmer');

        if ($status) {
            $query->where('status', $status);
        }

        $consultations = $query->latest()->paginate(20);

        return response()->json($consultations);
    }

    /**
     * Get training materials
     */
    public function trainingMaterials(Request $request)
    {
        $expert = $request->user();
        $status = $request->query('status', 'all');

        $query = TrainingMaterial::where('expert_id', $expert->id);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $materials = $query->latest()->paginate(20);

        return response()->json($materials);
    }

    /**
     * Get articles
     */
    public function articles(Request $request)
    {
        $expert = $request->user();
        $status = $request->query('status', 'all');

        $query = Article::where('expert_id', $expert->id);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $articles = $query->latest()->paginate(20);

        return response()->json($articles);
    }

    /**
     * Get engagement analytics
     */
    public function engagementAnalytics(Request $request)
    {
        $expert = $request->user();
        $period = $request->query('period', 30);

        // Most viewed training materials
        $topTraining = TrainingMaterial::where('expert_id', $expert->id)
            ->where('status', 'published')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        // Most viewed articles
        $topArticles = Article::where('expert_id', $expert->id)
            ->where('status', 'published')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        // Consultation engagement by crop type
        $consultationByCropType = Consultation::where('expert_id', $expert->id)
            ->whereDate('created_at', '>=', now()->subDays($period))
            ->with('farm')
            ->get()
            ->groupBy(fn($consultation) => $consultation->farm?->primary_crop ?? 'Unknown')
            ->map(fn($group) => [
                'crop_type' => $group->first()->farm?->primary_crop ?? 'Unknown',
                'count' => $group->count(),
            ]);

        // Average consultation duration
        $avgConsultationDuration = Consultation::where('expert_id', $expert->id)
            ->where('status', 'completed')
            ->whereNotNull('ended_at')
            ->get()
            ->avg(fn($consultation) => $consultation->ended_at->diffInMinutes($consultation->created_at));

        return response()->json([
            'period_days' => $period,
            'top_training_materials' => $topTraining,
            'top_articles' => $topArticles,
            'consultations_by_crop_type' => $consultationByCropType,
            'average_consultation_minutes' => round($avgConsultationDuration ?? 0, 2),
        ]);
    }

    /**
     * Get farmer reach
     */
    public function farmerReach(Request $request)
    {
        $expert = $request->user();

        // Unique farmers consulted
        $uniqueFarmers = Consultation::where('expert_id', $expert->id)
            ->distinct('farmer_id')
            ->count('farmer_id');

        // Unique farms reached
        $uniqueFarms = Farm::whereIn('user_id', function($query) use ($expert) {
            $query->select('farmer_id')
                ->from('consultations')
                ->where('expert_id', $expert->id);
        })->count();

        // Farmer satisfaction
        $satisfactionRating = Consultation::where('expert_id', $expert->id)
            ->whereNotNull('rating')
            ->avg('rating') ?? 0;

        // Geographic reach
        $geographicReach = Consultation::where('expert_id', $expert->id)
            ->with('farmer')
            ->get()
            ->groupBy(fn($consultation) => $consultation->farmer?->location ?? 'Unknown')
            ->map(fn($group) => [
                'location' => $group->first()->farmer?->location ?? 'Unknown',
                'count' => $group->count(),
            ]);

        return response()->json([
            'unique_farmers' => $uniqueFarmers,
            'unique_farms' => $uniqueFarms,
            'satisfaction_rating' => round($satisfactionRating, 2),
            'geographic_reach' => $geographicReach->take(10),
        ]);
    }

    /**
     * Get consultation details
     */
    public function consultationDetails(Request $request, $id)
    {
        $expert = $request->user();

        $consultation = Consultation::where('expert_id', $expert->id)
            ->where('id', $id)
            ->with('farmer')
            ->first();

        if (!$consultation) {
            return response()->json(['message' => 'Consultation not found'], 404);
        }

        return response()->json($consultation);
    }
}
