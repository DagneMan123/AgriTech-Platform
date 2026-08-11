<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($invoices);
    }

    public function show(Invoice $invoice)
    {
        return response()->json($invoice);
    }

    public function download(Invoice $invoice)
    {
        // Generate PDF or return file
        return response()->json(['message' => 'Invoice download']);
    }
}
