<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Product;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ConversationApiController extends Controller
{
    /**
     * List conversations for the authenticated user.
     */
    public function index()
    {
        $conversations = Conversation::forUser(Auth::id())
            ->with(['client', 'vendor', 'product', 'messages' => function ($query) {
                $query->latest()->take(1);
            }])
            ->orderByDesc('last_message_at')
            ->paginate(10);

        return response()->json([
            'conversations' => $conversations,
        ], 200);
    }

    /**
     * Start a new conversation.
     */
    public function startConversation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vendor_id' => 'required|exists:users,id',
            'product_id' => 'nullable|exists:products,id',
            'message' => 'required_without:image|string|nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        // $clientId = auth()->id();
        $client = Auth::user();
        // if ($client->user_type !== 'client') {
        //     return response()->json(['message' => 'Only clients can start conversations'], 403);
        // }

        // Verify vendor and product
        $vendor = User::where('id', $request->vendor_id)
            ->where('user_type', 'vendor')
            ->firstOrFail();

        if ($request->product_id) {
            Product::where('id', $request->product_id)
                ->whereHas('store', function ($query) use ($request) {
                    $query->where('vendor_id', $request->vendor_id);
                })->firstOrFail();
        }

        $conversation = Conversation::firstOrCreate(
            [
                'client_id' => $client->id,
                'vendor_id' => $request->vendor_id,
                'product_id' => $request->product_id,
            ],
            ['last_message_at' => now()]
        );

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/messages', 'public');
        }

        $message = $conversation->messages()->create([
            'sender_id' => $client->id,
            'message' => $request->message,
            'image' => $path,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Broadcast event
        // broadcast(new NewMessageEvent($message))->toOthers();

        $conversation->load(['messages', 'product', 'client', 'vendor']);

        return response()->json([
            'message' => 'Conversation started',
            'conversation' => $conversation,
        ], 201);
    }

    /**
     * Send a message in a conversation.
     */
    public function sendMessage(Request $request, $conversationId)
    {
        $conversation = Conversation::forUser(Auth::id())->findOrFail($conversationId);

        $validator = Validator::make($request->all(), [
            'message' => 'required_without:image|string|nullable',
            'image' => 'required_without:message|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422);
        }

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('images/messages', 'public');
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'message' => $request->message,
            'image' => $path,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Broadcast event
        // broadcast(new NewMessageEvent($message))->toOthers();

        return response()->json([
            'message' => 'Message sent',
            'message' => $message->load('sender'),
        ], 201);
    }

    /**
     * Fetch messages for a conversation.
     */
    public function getMessages($conversationId)
    {
        $conversation = Conversation::forUser(Auth::id())
            ->with(['messages.sender', 'product', 'client', 'vendor'])
            ->findOrFail($conversationId);

        // Mark messages as read (except sender's own messages)
        $conversation->messages()
            ->where('sender_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'conversation' => $conversation,
            'messages' => $conversation->messages()->with('sender')->paginate(20),
        ], 200);
    }
}
