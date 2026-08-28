<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DocumentVerificationController extends Controller
{
    /**
     * Get all pending documents for verification
     */
    public function pending(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 20);

            $documents = UserDocument::with('user')
                ->where('verification_status', 'pending')
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'data' => $documents->items(),
                'pagination' => [
                    'total' => $documents->total(),
                    'per_page' => $documents->perPage(),
                    'current_page' => $documents->currentPage(),
                    'last_page' => $documents->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching pending documents: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch documents',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific document
     */
    public function show($documentId)
    {
        try {
            $document = UserDocument::with('user')->findOrFail($documentId);

            return response()->json([
                'data' => $document,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching document: ' . $e->getMessage());
            return response()->json([
                'message' => 'Document not found',
            ], 404);
        }
    }

    /**
     * Get documents for a specific user
     */
    public function userDocuments($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            $documents = UserDocument::where('user_id', $userId)
                ->get();

            return response()->json([
                'user' => $user->only(['id', 'name', 'email', 'phone', 'role']),
                'documents' => $documents,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching user documents: ' . $e->getMessage());
            return response()->json([
                'message' => 'User not found or error fetching documents',
            ], 404);
        }
    }

    /**
     * Verify a document
     */
    public function verify(Request $request, $documentId)
    {
        try {
            $document = UserDocument::findOrFail($documentId);

            $document->verification_status = 'verified';
            $document->verified_by = $request->user()->id;
            $document->verified_at = now();
            $document->save();

            // Check if all documents for this user are verified
            $this->checkAndActivateUser($document->user);

            Log::info("Document verified by admin {$request->user()->id}: {$document->id}");

            return response()->json([
                'message' => 'Document verified successfully',
                'document' => $document,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error verifying document: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to verify document',
            ], 500);
        }
    }

    /**
     * Reject a document
     */
    public function reject(Request $request, $documentId)
    {
        try {
            $request->validate([
                'reason' => 'required|string|max:500',
            ]);

            $document = UserDocument::findOrFail($documentId);

            $document->verification_status = 'rejected';
            $document->rejection_reason = $request->input('reason');
            $document->verified_by = $request->user()->id;
            $document->verified_at = now();
            $document->save();

            // Deactivate user if documents are rejected
            $document->user->update(['is_active' => false]);

            Log::warning("Document rejected by admin {$request->user()->id}: {$document->id}. Reason: {$request->input('reason')}");

            return response()->json([
                'message' => 'Document rejected',
                'document' => $document,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error rejecting document: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to reject document',
            ], 500);
        }
    }

    /**
     * Download document
     */
    public function download($documentId)
    {
        try {
            $document = UserDocument::findOrFail($documentId);

            if (!Storage::disk('public')->exists($document->file_path)) {
                return response()->json([
                    'message' => 'Document file not found',
                ], 404);
            }

            return Storage::disk('public')->download($document->file_path, $document->document_name);
        } catch (\Exception $e) {
            Log::error('Error downloading document: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to download document',
            ], 500);
        }
    }

    /**
     * Get document statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'total_documents' => UserDocument::count(),
                'pending' => UserDocument::where('verification_status', 'pending')->count(),
                'verified' => UserDocument::where('verification_status', 'verified')->count(),
                'rejected' => UserDocument::where('verification_status', 'rejected')->count(),
                'pending_by_role' => $this->getPendingByRole(),
            ];

            return response()->json($stats, 200);
        } catch (\Exception $e) {
            Log::error('Error fetching document statistics: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch statistics',
            ], 500);
        }
    }

    /**
     * Get pending documents count by role
     */
    private function getPendingByRole()
    {
        $roles = ['farmer', 'buyer', 'supplier', 'transport', 'expert', 'financial', 'cooperative'];
        $result = [];

        foreach ($roles as $role) {
            $result[$role] = UserDocument::join('users', 'user_documents.user_id', '=', 'users.id')
                ->where('users.role', $role)
                ->where('user_documents.verification_status', 'pending')
                ->count();
        }

        return $result;
    }

    /**
     * Check and activate user if all documents are verified
     */
    private function checkAndActivateUser(User $user)
    {
        $pendingDocs = UserDocument::where('user_id', $user->id)
            ->where('verification_status', 'pending')
            ->count();

        if ($pendingDocs === 0) {
            // Check if there are any rejected documents
            $rejectedDocs = UserDocument::where('user_id', $user->id)
                ->where('verification_status', 'rejected')
                ->count();

            if ($rejectedDocs === 0) {
                // All documents verified, activate user
                $user->update(['is_active' => true]);
                Log::info("User {$user->id} activated after all documents verified");
            }
        }
    }
}

