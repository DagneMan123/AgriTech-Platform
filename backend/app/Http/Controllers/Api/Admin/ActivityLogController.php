<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Get all activity logs with pagination
     */
    public function index(Request $request)
    {
        $query = Activity::query();

        if ($request->has('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        if ($request->has('model')) {
            $query->where('subject_type', $request->model);
        }

        if ($request->has('action')) {
            $query->where('description', $request->action);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        $logs = $query->latest()->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get activity log details
     */
    public function show(Activity $activity)
    {
        return response()->json([
            'success' => true,
            'data' => $activity,
        ]);
    }

    /**
     * Get user activity logs
     */
    public function userActivity(Request $request, int $userId)
    {
        $logs = Activity::where('causer_id', $userId)
            ->latest()
            ->paginate($request->get('limit', 50));

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }

    /**
     * Get activity statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Activity::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $stats = [
            'total_activities' => $query->clone()->count(),
            'by_action' => $query->clone()->groupBy('description')->selectRaw('description, count(*) as count')->get(),
            'by_model' => $query->clone()->groupBy('subject_type')->selectRaw('subject_type, count(*) as count')->get(),
            'most_active_users' => Activity::selectRaw('causer_id, count(*) as count')
                ->groupBy('causer_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->with('causer')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Export activity logs
     */
    public function export(Request $request)
    {
        $logs = Activity::query();

        if ($request->has('start_date') && $request->has('end_date')) {
            $logs->whereBetween('created_at', [
                $request->start_date,
                $request->end_date,
            ]);
        }

        $logs = $logs->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
