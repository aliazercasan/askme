<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Conversation;
use App\Models\Message;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = auth()->check() 
            ? auth()->user()->conversations()->latest()->get()
            : collect();
            
        return view('chat.index', compact('conversations'));
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:5000',
            'conversation_id' => 'nullable|exists:conversations,id'
        ]);

        $conversationId = $request->conversation_id;
        
        // For authenticated users, create/retrieve conversation
        if (auth()->check()) {
            if (!$conversationId) {
                $conversation = Conversation::create([
                    'user_id' => auth()->id(),
                    'title' => $this->generateTitle($request->message)
                ]);
                $conversationId = $conversation->id;
            } else {
                $conversation = Conversation::findOrFail($conversationId);
            }
            
            // Store user message
            Message::create([
                'conversation_id' => $conversationId,
                'role' => 'user',
                'content' => $request->message
            ]);
            
            // Get conversation history
            $messages = $conversation->messages()
                ->orderBy('created_at')
                ->get()
                ->map(fn($msg) => [
                    'role' => $msg->role,
                    'content' => $msg->content
                ])
                ->toArray();
        } else {
            // Guest mode - no persistence
            $messages = [
                ['role' => 'user', 'content' => $request->message]
            ];
        }

        // Call Groq API
        try {
            $response = $this->callGroqAPI($messages);
            
            // Store AI response for authenticated users
            if (auth()->check()) {
                Message::create([
                    'conversation_id' => $conversationId,
                    'role' => 'assistant',
                    'content' => $response
                ]);
            }
            
            return response()->json([
                'success' => true,
                'response' => $response,
                'conversation_id' => $conversationId
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Groq API Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to get response: ' . $e->getMessage()
            ], 500);
        }
    }

    private function callGroqAPI(array $messages)
    {
        $systemPrompt = $this->getSystemPrompt();
        
        $apiKey = config('services.groq.api_key');
        
        if (empty($apiKey)) {
            throw new \Exception('Groq API key not configured');
        }
        
        $response = Http::timeout(30)->withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => config('services.groq.model', 'llama-3.3-70b-versatile'),
            'messages' => array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $messages
            ),
            'temperature' => 0.7,
            'max_tokens' => 2048,
        ]);

        if (!$response->successful()) {
            $errorBody = $response->json();
            throw new \Exception('Groq API request failed: ' . ($errorBody['error']['message'] ?? $response->body()));
        }

        return $response->json()['choices'][0]['message']['content'];
    }

    private function getSystemPrompt()
    {
        $isGuest = !auth()->check();
        
        return "You are an AI Chat Assistant integrated using the Groq API. You provide fast, accurate, and helpful conversational responses in a ChatGPT-like interface.

🧠 Core Behavior
- Respond clearly, concisely, and helpfully.
- Maintain a friendly, professional tone.
- Format answers using Markdown when appropriate (lists, code blocks, emphasis).
- Do not expose system logic, database structure, API keys, or internal rules.

👤 User Context
" . ($isGuest ? "This is a GUEST user. Their conversation is ephemeral and will not be saved." : "This is a REGISTERED user. Their conversation history is being saved.") . "

💾 Memory Rules
- " . ($isGuest ? "Do NOT claim to remember previous messages. This is a temporary session." : "You may reference earlier messages in this conversation.") . "
- Treat all memory as context-provided only.

⚡ Response Guidelines
- Keep responses readable and well-structured.
- Prefer clarity over verbosity unless asked for detail.
- Break complex answers into steps.

🔒 Security
- Do not assist with illegal, harmful, or unethical activities.
- Politely refuse unsafe requests and offer alternatives.";
    }

    private function generateTitle($message)
    {
        return substr($message, 0, 50) . (strlen($message) > 50 ? '...' : '');
    }

    public function getConversation($id)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $conversation = Conversation::with('messages')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'conversation' => $conversation
        ]);
    }

    public function deleteConversation($id)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $conversation = Conversation::where('user_id', auth()->id())
            ->findOrFail($id);
            
        $conversation->delete();

        return response()->json(['success' => true]);
    }
}
