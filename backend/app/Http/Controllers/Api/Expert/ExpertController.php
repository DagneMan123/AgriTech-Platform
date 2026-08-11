<?php

namespace App\Http\Controllers\Api\Expert;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ExpertController extends Controller
{
    /**
     * Get list of available experts for consultations
     */
    public function index(Request $request)
    {
        try {
            $experts = User::where('id', '!=', auth()->id())
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'expert');
                })
                ->with('expert')
                ->select('id', 'name', 'email', 'phone')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'specialization' => $user->expert?->specialization ?? 'General Expert',
                        'expertise_area' => $user->expert?->expertise_area,
                        'years_experience' => $user->expert?->years_experience,
                    ];
                });

            return response()->json(['data' => $experts]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching experts', 'error' => $e->getMessage()], 500);
        }
    }

    public function dashboard(Request $request)
    {
        $expert = $request->user();

        return response()->json([
            'pending_consultations' => \App\Models\Consultation::where('expert_id', $expert->id)
                ->where('status', 'open')
                ->count(),
            'completed_consultations' => \App\Models\Consultation::where('expert_id', $expert->id)
                ->where('status', 'closed')
                ->count(),
        ]);
    }
}
