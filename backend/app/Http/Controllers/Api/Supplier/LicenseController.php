<?php

namespace App\Http\Controllers\Api\Supplier;

use App\Http\Controllers\Controller;
use App\Models\SupplierLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LicenseController extends Controller
{
    /**
     * Get supplier license
     */
    public function index()
    {
        $license = SupplierLicense::where('supplier_id', Auth::id())
            ->first();

        if (!$license) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No license found. Please apply for a license.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $license,
        ]);
    }

    /**
     * Apply for license
     */
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'business_registration_number' => 'required|string|unique:supplier_licenses',
            'tax_id' => 'sometimes|string',
            'business_address' => 'required|string',
            'contact_person' => 'required|string',
            'contact_phone' => 'required|string',
            'contact_email' => 'required|email',
            'products_category' => 'required|array',
            'products_category.*' => 'string',
            'documents' => 'required|array',
            'documents.registration' => 'required|file|mimes:pdf,doc,docx|max:5120',
            'documents.tax' => 'sometimes|file|mimes:pdf,doc,docx|max:5120',
            'documents.bank_statement' => 'sometimes|file|mimes:pdf,doc,docx|max:5120',
        ]);

        // Check if already has license
        $existingLicense = SupplierLicense::where('supplier_id', Auth::id())->first();
        if ($existingLicense) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a license application',
            ], 422);
        }

        $documents = [];
        foreach ($validated['documents'] as $docType => $file) {
            if ($file) {
                $documents[$docType] = $file->store('licenses', 'public');
            }
        }

        $license = SupplierLicense::create([
            'supplier_id' => Auth::id(),
            'business_name' => $validated['business_name'],
            'business_registration_number' => $validated['business_registration_number'],
            'tax_id' => $validated['tax_id'] ?? null,
            'business_address' => $validated['business_address'],
            'contact_person' => $validated['contact_person'],
            'contact_phone' => $validated['contact_phone'],
            'contact_email' => $validated['contact_email'],
            'products_category' => json_encode($validated['products_category']),
            'documents' => json_encode($documents),
            'status' => 'pending',
            'applied_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'License application submitted successfully. Please wait for approval.',
            'data' => $license,
        ], 201);
    }

    /**
     * Update license application
     */
    public function update(Request $request, SupplierLicense $license)
    {
        $this->authorize('update', $license);

        if ($license->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update approved or rejected license',
            ], 422);
        }

        $validated = $request->validate([
            'business_address' => 'sometimes|string',
            'contact_phone' => 'sometimes|string',
            'contact_email' => 'sometimes|email',
            'products_category' => 'sometimes|array',
        ]);

        $license->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'License application updated successfully',
            'data' => $license,
        ]);
    }

    /**
     * Get license status
     */
    public function status()
    {
        $license = SupplierLicense::where('supplier_id', Auth::id())->first();

        if (!$license) {
            return response()->json([
                'success' => true,
                'data' => [
                    'has_license' => false,
                    'status' => 'no_application',
                ],
            ]);
        }

        $daysRemaining = $license->expiry_date 
            ? $license->expiry_date->diffInDays(now())
            : null;

        return response()->json([
            'success' => true,
            'data' => [
                'has_license' => true,
                'status' => $license->status,
                'license_number' => $license->license_number,
                'issued_date' => $license->issued_date,
                'expiry_date' => $license->expiry_date,
                'days_remaining' => $daysRemaining,
                'is_expiring_soon' => $daysRemaining && $daysRemaining <= 30,
            ],
        ]);
    }

    /**
     * Renew license
     */
    public function renew(Request $request)
    {
        $license = SupplierLicense::where('supplier_id', Auth::id())->firstOrFail();

        if ($license->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Only approved licenses can be renewed',
            ], 422);
        }

        $validated = $request->validate([
            'documents' => 'sometimes|array',
        ]);

        $license->update([
            'status' => 'renewal_pending',
            'renewal_date' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Renewal application submitted',
            'data' => $license,
        ]);
    }

    /**
     * Download license document
     */
    public function downloadDocument(string $documentType)
    {
        $license = SupplierLicense::where('supplier_id', Auth::id())->firstOrFail();
        $documents = json_decode($license->documents, true);

        if (!isset($documents[$documentType])) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found',
            ], 404);
        }

        $filePath = $documents[$documentType];
        return Storage::disk('public')->download($filePath);
    }

    /**
     * Get license history
     */
    public function history()
    {
        $licenses = SupplierLicense::where('supplier_id', Auth::id())
            ->orderBy('applied_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $licenses,
        ]);
    }
}
