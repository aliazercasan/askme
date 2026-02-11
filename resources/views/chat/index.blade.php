@extends('layouts.app')

@section('title', 'Ask Me - AI Chat Assistant')

@section('content')
<div x-data="chatApp()">
    
    <!-- Auth Modal -->
    <div x-show="showAuthModal" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50"
         @click.self="showAuthModal = false">
        <div class="bg-white rounded-lg p-8 max-w-md w-full mx-4">
            <div class="flex flex-col items-center mb-6">
                <img src="/logo.svg" alt="Ask Me Logo" class="h-20 w-20 mb-4">
                <h2 class="text-2xl font-bold">Welcome to Ask Me</h2>
            </div>
            
            <div class="space-y-4">
                <button @click="continueAsGuest" 
                        class="w-full bg-gray-600 text-white py-3 rounded-lg hover:bg-gray-700 transition">
                    Continue as Guest
                </button>
                
                <a href="/login" 
                   class="block w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition text-center">
                    Login
                </a>
                
                <a href="/register" 
                   class="block w-full bg-green-600 text-white py-3 rounded-lg hover:bg-green-700 transition text-center">
                    Register
                </a>
            </div>
            
            <p class="text-sm text-gray-600 mt-4">
                Guest mode: Chat without saving history. Register to save conversations.
            </p>
        </div>
    </div>

    <!-- Alert/Confirm Modal -->
    <div x-show="modal.show" 
         x-cloak
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50"
         @click.self="modal.show = false">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4" x-text="modal.title"></h3>
            <p class="text-gray-700 mb-6" x-text="modal.message"></p>
            
            <div class="flex gap-3 justify-end">
                <button @click="modal.show = false" 
                        x-show="modal.type === 'confirm'"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
                    Cancel
                </button>
                <button @click="modal.callback(); modal.show = false" 
                        :class="modal.type === 'confirm' ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                        class="px-4 py-2 text-white rounded-lg transition">
                    <span x-text="modal.type === 'confirm' ? 'Delete' : 'OK'"></span>
                </button>
            </div>
        </div>
    </div>

    <div class="flex h-screen">
        <!-- Sidebar for both Auth and Guest -->
        <div class="w-64 bg-gray-900 text-white p-4 flex flex-col">
            <button @click="newChat" 
                    class="bg-blue-600 hover:bg-blue-700 py-2 px-4 rounded mb-4 transition">
                + New Chat
            </button>
            
            <div class="flex-1 overflow-y-auto space-y-2">
                @auth
                <!-- Authenticated user conversations -->
                @foreach($conversations as $conv)
                <div @click="loadConversation({{ $conv->id }})"
                     :class="conversationId === {{ $conv->id }} ? 'bg-gray-700' : 'bg-gray-800'"
                     class="p-3 rounded cursor-pointer hover:bg-gray-700 transition group relative">
                    <p class="text-sm truncate">{{ $conv->title }}</p>
                    <button @click.stop="deleteConversation({{ $conv->id }})"
                            class="absolute right-2 top-3 opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-300">
                        ×
                    </button>
                </div>
                @endforeach
                @else
                <!-- Guest mode - no history -->
                <div class="text-center text-gray-400 text-sm p-4">
                    <p>Guest mode</p>
                    <p class="text-xs mt-2">Conversations are not saved</p>
                </div>
                @endauth
            </div>
            
            <div class="border-t border-gray-700 pt-4 mt-4">
                @auth
                <p class="text-sm">{{ auth()->user()->name }}</p>
                <form method="POST" action="/logout">
                    @csrf
                    <button class="text-sm text-gray-400 hover:text-white">Logout</button>
                </form>
                @else
                <p class="text-xs text-gray-400">Guest Mode</p>
                <button @click="showAuthModal = true" 
                        class="text-sm text-blue-400 hover:text-blue-300 mt-1">
                    Login / Register
                </button>
                @endauth
            </div>
        </div>

        <!-- Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <div class="bg-white border-b px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="/logo.svg" alt="Ask Me Logo" class="h-10 w-10">
                    <h1 class="text-xl font-semibold">Ask Me</h1>
                </div>
                @guest
                <div class="flex items-center gap-3">
                    <span class="text-sm text-gray-600">Guest Mode</span>
                    <button @click="showAuthModal = true" 
                            class="text-sm text-blue-600 hover:text-blue-700 underline">
                        Login / Register
                    </button>
                </div>
                @endguest
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-6 space-y-4" x-ref="messagesContainer">
                <template x-for="(msg, index) in messages" :key="index">
                    <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                        <div :class="msg.role === 'user' ? 'bg-blue-600 text-white' : 'bg-white border'"
                             class="max-w-3xl rounded-lg p-4 shadow-sm">
                            <div x-html="formatMessage(msg.content)"></div>
                        </div>
                    </div>
                </template>
                
                <!-- Loading Skeleton -->
                <div x-show="loading" class="flex justify-start">
                    <div class="bg-white border max-w-3xl rounded-lg p-4 shadow-sm">
                        <div class="flex space-x-2">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input -->
            <div class="bg-white border-t p-4">
                <form @submit.prevent="sendMessage" class="max-w-4xl mx-auto">
                    <div class="flex gap-2">
                        <input type="text" 
                               x-model="userInput"
                               :disabled="loading"
                               placeholder="Type your message..."
                               class="flex-1 border rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-gray-100">
                        <button type="submit" 
                                :disabled="loading || !userInput.trim()"
                                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition">
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function chatApp() {
            return {
                showAuthModal: {{ auth()->check() ? 'false' : 'true' }},
                messages: [],
                userInput: '',
                loading: false,
                conversationId: null,
                isGuest: {{ auth()->check() ? 'false' : 'true' }},
                modal: {
                    show: false,
                    title: '',
                    message: '',
                    type: 'alert', // 'alert' or 'confirm'
                    callback: () => {}
                },

                
                showAlert(title, message) {
                    this.modal = {
                        show: true,
                        title: title,
                        message: message,
                        type: 'alert',
                        callback: () => {}
                    };
                },
                
                showConfirm(title, message, callback) {
                    this.modal = {
                        show: true,
                        title: title,
                        message: message,
                        type: 'confirm',
                        callback: callback
                    };
                },
                
                continueAsGuest() {
                    this.showAuthModal = false;
                },
                
                newChat() {
                    this.messages = [];
                    this.conversationId = null;
                },
                
                async sendMessage() {
                    if (!this.userInput.trim() || this.loading) return;
                    
                    const message = this.userInput.trim();
                    this.messages.push({ role: 'user', content: message });
                    this.userInput = '';
                    this.loading = true;
                    
                    this.$nextTick(() => {
                        this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                    });
                    
                    try {
                        const response = await fetch('/chat/send', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                message: message,
                                conversation_id: this.conversationId
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            this.messages.push({ role: 'assistant', content: data.response });
                            this.conversationId = data.conversation_id;
                        } else {
                            this.showAlert('Error', data.error || 'Failed to get response');
                        }
                    } catch (error) {
                        this.showAlert('Network Error', 'Unable to connect. Please try again.');
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        });
                    }
                },
                
                async loadConversation(id) {
                    try {
                        const response = await fetch(`/chat/conversation/${id}`);
                        const data = await response.json();
                        
                        this.conversationId = id;
                        this.messages = data.conversation.messages.map(msg => ({
                            role: msg.role,
                            content: msg.content
                        }));
                        
                        this.$nextTick(() => {
                            this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                        });
                    } catch (error) {
                        this.showAlert('Error', 'Failed to load conversation');
                    }
                },
                
                async deleteConversation(id) {
                    this.showConfirm(
                        'Delete Conversation',
                        'Are you sure you want to delete this conversation?',
                        async () => {
                            try {
                                await fetch(`/chat/conversation/${id}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    }
                                });
                                
                                if (this.conversationId === id) {
                                    this.newChat();
                                }
                                
                                window.location.reload();
                            } catch (error) {
                                this.showAlert('Error', 'Failed to delete conversation');
                            }
                        }
                    );
                },
                
                formatMessage(content) {
                    // Basic markdown formatting
                    return content
                        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                        .replace(/\*(.*?)\*/g, '<em>$1</em>')
                        .replace(/`(.*?)`/g, '<code class="bg-gray-100 px-1 rounded">$1</code>')
                        .replace(/\n/g, '<br>');
                }
            }
        }
    </script>
</div>
@endsection
