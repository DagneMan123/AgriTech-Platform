<?php

namespace App\Http\Controllers\Api\Farmer;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Http\Requests\Farmer\StoreConsultationRequest;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Get all consultations for the authenticated farmer
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $consultations = Consultation::where('farmer_id', $farmer->id)
                ->with(['expert', 'messages'])
                ->latest()
                ->paginate(15);

            return response()->json([
                'data' => $consultations->items(),
                'pagination' => [
                    'total' => $consultations->total(),
                    'per_page' => $consultations->perPage(),
                    'current_page' => $consultations->currentPage(),
                    'last_page' => $consultations->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching consultations', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create a new consultation request
     */
    public function store(StoreConsultationRequest $request)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $validated = $request->validated();

            $consultation = Consultation::create([
                'farmer_id' => $farmer->id,
                'expert_id' => $validated['expert_id'],
                'question' => $validated['title'], // Using title as question
                'answer' => $validated['description'], // Using description as answer (initial description)
                'category' => $validated['consultation_type'],
                'status' => 'pending',
                'is_public' => false,
            ]);

            // Store additional metadata if needed
            // This can be extended based on your requirements

            return response()->json([
                'message' => 'Consultation request created successfully',
                'data' => $consultation->load(['expert', 'messages']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating consultation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific consultation
     */
    public function show(Request $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $consultation = Consultation::where('farmer_id', $farmer->id)
                ->with(['expert', 'messages'])
                ->findOrFail($id);

            return response()->json(['data' => $consultation]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Consultation not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error fetching consultation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a consultation
     */
    public function update(StoreConsultationRequest $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $consultation = Consultation::where('farmer_id', $farmer->id)->findOrFail($id);

            // Only allow updating pending consultations
            if ($consultation->status !== 'pending') {
                return response()->json(['message' => 'Can only update pending consultations'], 403);
            }

            $validated = $request->validated();

            $consultation->update([
                'expert_id' => $validated['expert_id'],
                'question' => $validated['title'],
                'answer' => $validated['description'],
                'category' => $validated['consultation_type'],
            ]);

            return response()->json([
                'message' => 'Consultation updated successfully',
                'data' => $consultation->load(['expert', 'messages']),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Consultation not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating consultation', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a consultation
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $farmer = $user->farmer;

            if (!$farmer) {
                return response()->json(['message' => 'Farmer profile not found'], 404);
            }

            $consultation = Consultation::where('farmer_id', $farmer->id)->findOrFail($id);

            $consultation->delete();

            return response()->json(['message' => 'Consultation deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Consultation not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error deleting consultation', 'error' => $e->getMessage()], 500);
        }
    }
}
