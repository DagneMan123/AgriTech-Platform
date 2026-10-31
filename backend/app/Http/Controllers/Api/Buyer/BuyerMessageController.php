<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BuyerMessageController extends Controller
{
    /**
     * Get all active conversations for the authenticated buyer.
     */
    public function conversations(Request $request)
    {
        try {
            $buyerId = Auth::id() ?? ($request->user() ? $request->user()->id : null);

            if (!$buyerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated'
                ], 401);
            }

            $conversations = Conversation::where('user_1_id', $buyerId)
                ->orWhere('user_2_id', $buyerId)
                ->with([
                    'userOne:id,name,email,phone,role,profile_image',
                    'userTwo:id,name,email,phone,role,profile_image',
                    'messages' => fn($q) => $q->latest()->limit(1)
                ])
                ->orderByDesc('last_message_at')
                ->get();

            $transformed = $conversations->map(function ($c) use ($buyerId) {
                $partner = ((int)$c->user_1_id === (int)$buyerId) ? $c->userTwo : $c->userOne;
                $lastMsg = $c->messages->first();
                $unreadCount = Message::where('conversation_id', $c->id)
                    ->where('sender_id', '!=', $buyerId)
                    ->where('is_read', false)
                    ->count();

                $lastMsgTime = $lastMsg ? $lastMsg->created_at : ($c->last_message_at ?: $c->created_at);

                return [
                    'id' => $c->id,
                    'conversation_code' => $c->conversation_code,
                    'partner_id' => $partner ? $partner->id : null,
                    'partner_name' => $partner ? $partner->name : 'Commercial Partner',
                    'partner_role' => $partner ? ucfirst($partner->role) : 'Producer',
                    'partner_phone' => $partner ? $partner->phone : null,
                    'partner_email' => $partner ? $partner->email : null,
                    'partner_avatar' => $partner ? ($partner->profile_image ?? null) : null,
                    'last_message' => $lastMsg ? $lastMsg->message : 'Conversation initiated',
                    'last_message_time' => $lastMsgTime ? $lastMsgTime->toISOString() : now()->toISOString(),
                    'last_message_formatted' => $lastMsgTime ? $lastMsgTime->diffForHumans() : 'Recently',
                    'unread_count' => $unreadCount
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformed
            ]);
        } catch (\Throwable $e) {
            Log::error('BuyerMessageController conversations error: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load conversations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get message thread for a specific conversation and mark incoming as read.
     */
    public function messages(Request $request, $id)
    {
        try {
            $buyerId = Auth::id() ?? ($request->user() ? $request->user()->id : null);

            $conversation = Conversation::where('id', $id)
                ->where(function ($q) use ($buyerId) {
                    $q->where('user_1_id', $buyerId)->orWhere('user_2_id', $buyerId);
                })
                ->with([
                    'userOne:id,name,email,phone,role,profile_image',
                    'userTwo:id,name,email,phone,role,profile_image'
                ])
                ->firstOrFail();

            // Mark unread messages sent by the partner as read
            Message::where('conversation_id', $id)
                ->where('sender_id', '!=', $buyerId)
                ->where('is_read', false)
                ->update(['is_read' => true, 'read_at' => now()]);

            $messages = Message::where('conversation_id', $id)
                ->orderBy('created_at', 'asc')
                ->get();

            $partner = ((int)$conversation->user_1_id === (int)$buyerId) ? $conversation->userTwo : $conversation->userOne;

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversation->id,
                    'partner' => [
                        'id' => $partner ? $partner->id : null,
                        'name' => $partner ? $partner->name : 'Commercial Partner',
                        'role' => $partner ? ucfirst($partner->role) : 'Producer',
                        'phone' => $partner ? $partner->phone : null,
                        'email' => $partner ? $partner->email : null
                    ],
                    'messages' => $messages->map(fn($m) => [
                        'id' => $m->id,
                        'sender_id' => $m->sender_id,
                        'is_me' => (int)$m->sender_id === (int)$buyerId,
                        'message' => $m->message,
                        'is_read' => (bool)$m->is_read,
                        'created_at' => $m->created_at ? $m->created_at->toISOString() : now()->toISOString(),
                        'time_formatted' => $m->created_at ? $m->created_at->format('M d, H:i') : ''
                    ])
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('BuyerMessageController messages thread error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load message thread: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send reply in an existing conversation.
     */
    public function sendMessage(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:3000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $buyerId = Auth::id() ?? ($request->user() ? $request->user()->id : null);

            $conversation = Conversation::where('id', $id)
                ->where(function ($q) use ($buyerId) {
                    $q->where('user_1_id', $buyerId)->orWhere('user_2_id', $buyerId);
                })
                ->firstOrFail();

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $buyerId,
                'message' => trim($request->message),
                'message_type' => 'text',
                'is_read' => false
            ]);

            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $message->id,
                    'conversation_id' => $conversation->id,
                    'sender_id' => $buyerId,
                    'is_me' => true,
                    'message' => $message->message,
                    'is_read' => false,
                    'created_at' => $message->created_at ? $message->created_at->toISOString() : now()->toISOString(),
                    'time_formatted' => $message->created_at ? $message->created_at->format('M d, H:i') : ''
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deliver message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start conversation with a partner (farmer, supplier).
     */
    public function startConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partner_id' => 'required|exists:users,id',
            'message' => 'required|string|max:3000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $buyerId = Auth::id() ?? ($request->user() ? $request->user()->id : null);
            $partnerId = (int)$request->partner_id;

            if ((int)$buyerId === $partnerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create conversation with yourself.'
                ], 422);
            }

            // Find existing conversation either way
            $conversation = Conversation::where(function ($q) use ($buyerId, $partnerId) {
                $q->where('user_1_id', $buyerId)->where('user_2_id', $partnerId);
            })->orWhere(function ($q) use ($buyerId, $partnerId) {
                $q->where('user_1_id', $partnerId)->where('user_2_id', $buyerId);
            })->first();

            if (!$conversation) {
                $user1 = min((int)$buyerId, $partnerId);
                $user2 = max((int)$buyerId, $partnerId);
                $code = 'CONV-' . strtoupper(Str::random(10));

                $conversation = Conversation::create([
                    'conversation_code' => $code,
                    'user_1_id' => $user1,
                    'user_2_id' => $user2,
                    'conversation_type' => 'direct',
                    'last_message_at' => now()
                ]);
            }

            $message = Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $buyerId,
                'message' => trim($request->message),
                'message_type' => 'text',
                'is_read' => false
            ]);

            $conversation->update(['last_message_at' => now()]);

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversation->id,
                    'message_id' => $message->id
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate conversation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available contacts (farmers, suppliers) to message.
     */
    public function contacts(Request $request)
    {
        try {
            $buyerId = Auth::id() ?? ($request->user() ? $request->user()->id : null);

            $contacts = User::whereIn('role', ['farmer', 'supplier', 'admin'])
                ->where('id', '!=', $buyerId)
                ->where('is_active', true)
                ->select('id', 'name', 'email', 'phone', 'role', 'region')
                ->limit(30)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $contacts
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load contacts: ' . $e->getMessage()
            ], 500);
        }
    }
}
