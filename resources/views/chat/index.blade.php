@extends('layouts.app')

@section('title', 'Ask Me - AI Chat Assistant')

@section('content')
    <div x-data="chatApp()">

        <!-- Auth Modal -->
        <div x-show="showAuthModal" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 p-4"
             @click.self="showAuthModal = false">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <div class="flex flex-col items-center mb-8">
                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-4 rounded-2xl mb-4 shadow-lg">
                        <img src="/logo.svg" alt="Ask Me Logo" class="h-16 w-16">
                    </div>
                    <h2 class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Welcome to Ask Me</h2>
                    <p class="text-gray-500 mt-2 text-center">Your intelligent AI assistant</p>
                </div>

                <div class="space-y-3">
                    <button @click="continueAsGuest"
                        class="w-full bg-gradient-to-r from-gray-600 to-gray-700 text-white py-3.5 px-6 rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Continue as Guest
                    </button>

                    <a href="/login"
                        class="block w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3.5 px-6 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 text-center font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Login
                    </a>

                    <a href="/register"
                        class="block w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white py-3.5 px-6 rounded-xl hover:from-emerald-700 hover:to-teal-700 transition-all duration-200 text-center font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Register
                    </a>
                </div>

                <div class="mt-6 p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                    <p class="text-sm text-indigo-900 text-center">
                        <span class="font-semibold">Guest mode:</span> Chat without saving history. Register to save conversations.
                    </p>
                </div>
            </div>
        </div>

        <!-- Alert/Confirm Modal -->
        <div x-show="modal.show" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-md flex items-center justify-center z-50 p-4"
             @click.self="modal.show = false">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-gray-900 mb-3" x-text="modal.title"></h3>
                <p class="text-gray-600 mb-6" x-text="modal.message"></p>

                <div class="flex gap-3 justify-end">
                    <button @click="modal.show = false" x-show="modal.type === 'confirm'"
                        class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                        Cancel
                    </button>
                    <button @click="modal.callback(); modal.show = false"
                        :class="modal.type === 'confirm' ? 'bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700' : 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700'"
                        class="px-5 py-2.5 text-white rounded-xl transition-all duration-200 font-medium shadow-lg">
                        <span x-text="modal.type === 'confirm' ? 'Delete' : 'OK'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar - Responsive -->
            <div class="hidden lg:flex lg:w-80 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white flex-col shadow-2xl">
                <div class="p-6 border-b border-slate-700/50">
                    <button @click="newChat" 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 py-3.5 px-6 rounded-xl transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        New Chat
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    @auth
                        @foreach ($conversations as $conv)
                            <div @click="loadConversation({{ $conv->id }})"
                                :class="conversationId === {{ $conv->id }} ? 'bg-indigo-600/20 border-indigo-500/50' : 'bg-slate-800/50 border-slate-700/50 hover:bg-slate-700/50'"
                                class="p-4 rounded-xl cursor-pointer transition-all duration-200 group relative border backdrop-blur-sm">
                                <p class="text-sm font-medium truncate pr-8">{{ $conv->title }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $conv->updated_at->diffForHumans() }}</p>
                                <button @click.stop="deleteConversation({{ $conv->id }})"
                                    class="absolute right-3 top-4 opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-300 transition-all duration-200 bg-slate-900/80 rounded-lg p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-slate-400 p-8">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="font-medium">Guest Mode</p>
                            <p class="text-xs mt-2 text-slate-500">Conversations are not saved</p>
                        </div>
                    @endauth
                </div>

                <div class="border-t border-slate-700/50 p-6 bg-slate-900/50 backdrop-blur-sm">
                    @auth
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center font-bold text-lg">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="/logout">
                            @csrf
                            <button class="w-full text-sm text-slate-400 hover:text-white py-2 px-4 rounded-lg hover:bg-slate-800/50 transition-all duration-200 text-left">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Logout
                            </button>
                        </form>
                    @else
                        <div class="text-center">
                            <p class="text-xs text-slate-400 mb-3">Guest Mode Active</p>
                            <button @click="showAuthModal = true" 
                                class="w-full bg-indigo-600/20 hover:bg-indigo-600/30 text-indigo-300 py-2.5 px-4 rounded-lg transition-all duration-200 text-sm font-medium border border-indigo-500/30">
                                Login / Register
                            </button>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Main Chat Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Header -->
                <div class="bg-white/80 backdrop-blur-xl border-b border-slate-200/50 px-4 sm:px-6 py-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 hover:bg-slate-100 rounded-lg transition-colors">
                            <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>
                        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-2 rounded-xl shadow-lg">
                            <img src="/logo.svg" alt="Ask Me Logo" class="h-8 w-8">
                        </div>
                        <div>
                            <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Ask Me</h1>
                            <p class="text-xs text-slate-500 hidden sm:block">AI-Powered Assistant</p>
                        </div>
                    </div>
                    @guest
                        <button @click="showAuthModal = true" 
                            class="hidden sm:flex items-center gap-2 text-sm bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 font-medium shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                            Login
                        </button>
                    @endguest
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6" x-ref="messagesContainer">
                    <template x-if="messages.length === 0">
                        <div class="flex items-center justify-center h-full">
                            <div class="text-center max-w-md">
                                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-6 rounded-3xl inline-block mb-6 shadow-2xl">
                                    <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 mb-3">Start a Conversation</h2>
                                <p class="text-slate-500 text-sm sm:text-base">Ask me anything! I'm here to help you with information, ideas, and answers.</p>
                            </div>
                        </div>
                    </template>

                    <template x-for="(msg, index) in messages" :key="index">
                        <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'" class="message-enter">
                            <div :class="msg.role === 'user' 
                                ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white' 
                                : 'bg-white border border-slate-200 text-slate-800'"
                                class="max-w-[85%] sm:max-w-3xl rounded-2xl p-4 sm:p-5 shadow-lg">
                                <div class="flex items-start gap-3 mb-2" x-show="msg.role === 'assistant'">
                                    <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-1.5 rounded-lg">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-600">AI Assistant</span>
                                </div>
                                <div x-html="formatMessage(msg.content)" class="prose prose-sm max-w-none" :class="msg.role === 'user' ? 'prose-invert' : ''"></div>
                            </div>
                        </div>
                    </template>

                    <!-- Loading Skeleton -->
                    <div x-show="loading" class="flex justify-start message-enter">
                        <div class="bg-white border border-slate-200 max-w-3xl rounded-2xl p-5 shadow-lg">
                            <div class="flex items-center gap-3">
                                <div class="bg-gradient-to-br from-indigo-500 to-purple-600 p-1.5 rounded-lg">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div class="flex space-x-1.5">
                                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="bg-white/80 backdrop-blur-xl border-t border-slate-200/50 p-4 sm:p-6 shadow-lg">
                    <form @submit.prevent="sendMessage" class="max-w-4xl mx-auto">
                        <div class="flex gap-2 sm:gap-3">
                            <input type="text" 
                                x-model="userInput" 
                                :disabled="loading"
                                placeholder="Type your message..."
                                class="flex-1 border-2 border-slate-200 rounded-xl sm:rounded-2xl px-4 sm:px-6 py-3 sm:py-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-slate-100 disabled:text-slate-400 transition-all duration-200 text-sm sm:text-base">
                            <button type="submit" 
                                :disabled="loading || !userInput.trim()"
                                class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-5 sm:px-8 py-3 sm:py-4 rounded-xl sm:rounded-2xl hover:from-indigo-700 hover:to-purple-700 disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed transition-all duration-200 font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:transform-none flex items-center gap-2">
                                <span class="hidden sm:inline">Send</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
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
                    sidebarOpen: false,
                    messages: [],
                    userInput: '',
                    loading: false,
                    conversationId: null,
                    isGuest: {{ auth()->check() ? 'false' : 'true' }},
                    modal: {
                        show: false,
                        title: '',
                        message: '',
                        type: 'alert',
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
                        this.messages.push({
                            role: 'user',
                            content: message
                        });
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
                                this.messages.push({
                                    role: 'assistant',
                                    content: data.response
                                });
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
                        return content
                            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                            .replace(/\*(.*?)\*/g, '<em>$1</em>')
                            .replace(/`(.*?)`/g, '<code class="bg-slate-100 text-indigo-600 px-1.5 py-0.5 rounded text-sm font-mono">$1</code>')
                            .replace(/\n/g, '<br>');
                    }
                }
            }
        </script>
    </div>
@endsection
