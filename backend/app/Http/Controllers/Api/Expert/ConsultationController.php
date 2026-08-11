<?php

namespace App\Http\Controllers\Api\Expert;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\ConsultationMessage;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $consultations = Consultation::where('expert_id', $user->id)
            ->with('messages')
            ->latest()
            ->paginate(15);

        return response()->json($consultations);
    }

    public function show(Consultation $consultation)
    {
        $consultation->load('messages', 'farmer', 'expert');
        return response()->json($consultation);
    }

    public function respond(Request $request, Consultation $consultation)
    {
        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        ConsultationMessage::create([
            'consultation_id' => $consultation->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json(['message' => 'Response added']);
    }

    public function close(Consultation $consultation)
    {
        $consultation->update(['status' => 'closed', 'closed_at' => now()]);
        return response()->json(['message' => 'Consultation closed']);
    }
}
