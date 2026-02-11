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
            class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="showAuthModal = false">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
                <div class="flex flex-col items-center mb-6">
                    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-4 rounded-2xl mb-4 shadow-lg">
                        <img src="/logo.svg" alt="Ask Me Logo" class="h-12 w-12">
                    </div>
                    <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Welcome to Ask Me</h2>
                    <p class="text-gray-500 text-sm mt-2">Your AI-powered chat assistant</p>
                </div>

                <div class="space-y-3">
                    <button @click="continueAsGuest"
                        class="w-full bg-gradient-to-r from-gray-600 to-gray-700 text-white py-3.5 px-4 rounded-xl hover:from-gray-700 hover:to-gray-800 transition-all duration-200 font-medium shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Continue as Guest
                        </span>
                    </button>

                    <a href="/login"
                        class="block w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3.5 px-4 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 text-center font-medium shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Login
                        </span>
                    </a>

                    <a href="/register"
                        class="block w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-3.5 px-4 rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 text-center font-medium shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Register
                        </span>
                    </a>
                </div>

                <div class="mt-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-sm text-gray-600 text-center">
                        <span class="font-semibold text-blue-600">Guest mode:</span> Chat without saving history. Register to save conversations.
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
            class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            @click.self="modal.show = false">
            <div x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="bg-white rounded-2xl shadow-2xl p-6 max-w-md w-full">
                <h3 class="text-xl font-bold text-gray-900 mb-3" x-text="modal.title"></h3>
                <p class="text-gray-600 mb-6" x-text="modal.message"></p>

                <div class="flex gap-3 justify-end">
                    <button @click="modal.show = false" x-show="modal.type === 'confirm'"
                        class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium">
                        Cancel
                    </button>
                    <button @click="modal.callback(); modal.show = false"
                        :class="modal.type === 'confirm' ? 'bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800' : 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800'"
                        class="px-5 py-2.5 text-white rounded-xl transition-all duration-200 font-medium shadow-md hover:shadow-lg">
                        <span x-text="modal.type === 'confirm' ? 'Delete' : 'OK'"></span>
                    </button>
                </div>
            </div>
        </div>

        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <div class="w-80 bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col shadow-2xl hidden lg:flex">
                <div class="p-6 border-b border-slate-700">
                    <button @click="newChat" 
                        class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 py-3 px-4 rounded-xl transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Chat
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    @auth
                        @foreach ($conversations as $conv)
                            <div @click="loadConversation({{ $conv->id }})"
                                :class="conversationId === {{ $conv->id }} ? 'bg-slate-700 border-blue-500' : 'bg-slate-800/50 border-transparent hover:bg-slate-700/70'"
                                class="p-4 rounded-xl cursor-pointer transition-all duration-200 group relative border-l-4">
                                <p class="text-sm font-medium truncate pr-8">{{ $conv->title }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $conv->updated_at->diffForHumans() }}</p>
                                <button @click.stop="deleteConversation({{ $conv->id }})"
                                    class="absolute right-3 top-4 opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-300 transition-all duration-200 bg-slate-900 rounded-lg p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-slate-400 p-6 bg-slate-800/30 rounded-xl border border-slate-700">
                            <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <p class="font-medium">Guest Mode</p>
                            <p class="text-xs mt-2">Conversations are not saved</p>
                        </div>
                    @endauth
                </div>

                <div class="border-t border-slate-700 p-4">
                    @auth
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center font-bold text-white">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <form method="POST" action="/logout">
                            @csrf
                            <button class="w-full text-sm text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 py-2 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Logout
                            </button>
                        </form>
                    @else
                        <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                            <p class="text-xs text-slate-400 mb-2">Guest Mode Active</p>
                            <button @click="showAuthModal = true" class="w-full text-sm text-blue-400 hover:text-blue-300 bg-blue-900/30 hover:bg-blue-900/50 py-2 px-4 rounded-lg transition-all duration-200 font-medium">
                                Login / Register
                            </button>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Mobile Sidebar Toggle -->
            <div x-data="{ sidebarOpen: false }" class="flex-1 flex flex-col min-w-0">
                <!-- Mobile Sidebar -->
                <div x-show="sidebarOpen" 
                     x-cloak
                     @click="sidebarOpen = false"
                     class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden">
                    <div @click.stop 
                         x-show="sidebarOpen"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="-translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="-translate-x-full"
                         class="w-80 h-full bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col shadow-2xl">
                        <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                            <button @click="newChat" 
                                class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 py-3 px-4 rounded-xl transition-all duration-200 font-medium shadow-lg flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                New Chat
                            </button>
                            <button @click="sidebarOpen = false" class="ml-3 text-slate-400 hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-4 space-y-2">
                            @auth
                                @foreach ($conversations as $conv)
                                    <div @click="loadConversation({{ $conv->id }}); sidebarOpen = false"
                                        :class="conversationId === {{ $conv->id }} ? 'bg-slate-700 border-blue-500' : 'bg-slate-800/50 border-transparent hover:bg-slate-700/70'"
                                        class="p-4 rounded-xl cursor-pointer transition-all duration-200 group relative border-l-4">
                                        <p class="text-sm font-medium truncate pr-8">{{ $conv->title }}</p>
                                        <p class="text-xs text-slate-400 mt-1">{{ $conv->updated_at->diffForHumans() }}</p>
                                        <button @click.stop="deleteConversation({{ $conv->id }})"
                                            class="absolute right-3 top-4 opacity-0 group-hover:opacity-100 text-red-400 hover:text-red-300 transition-all duration-200 bg-slate-900 rounded-lg p-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-slate-400 p-6 bg-slate-800/30 rounded-xl border border-slate-700">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="font-medium">Guest Mode</p>
                                    <p class="text-xs mt-2">Conversations are not saved</p>
                                </div>
                            @endauth
                        </div>

                        <div class="border-t border-slate-700 p-4">
                            @auth
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center font-bold text-white">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                                    </div>
                                </div>
                                <form method="POST" action="/logout">
                                    @csrf
                                    <button class="w-full text-sm text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 py-2 px-4 rounded-lg transition-all duration-200 flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Logout
                                    </button>
                                </form>
                            @else
                                <div class="bg-slate-800/50 rounded-xl p-4 border border-slate-700">
                                    <p class="text-xs text-slate-400 mb-2">Guest Mode Active</p>
                                    <button @click="showAuthModal = true" class="w-full text-sm text-blue-400 hover:text-blue-300 bg-blue-900/30 hover:bg-blue-900/50 py-2 px-4 rounded-lg transition-all duration-200 font-medium">
                                        Login / Register
                                    </button>
                                </div>
                            @endauth
                        </div>
                    </div>
                </div>

                <!-- Header -->
                <div class="bg-white/80 backdrop-blur-lg border-b border-gray-200 px-4 sm:px-6 py-4 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="lg:hidden text-gray-600 hover:text-gray-900">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-2 rounded-xl shadow-lg">
                            <img src="/logo.svg" alt="Ask Me Logo" class="h-8 w-8">
                        </div>
                        <div>
                            <h1 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Ask Me</h1>
                            <p class="text-xs text-gray-500 hidden sm:block">AI Chat Assistant</p>
                        </div>
                    </div>
                    @guest
                        <div class="flex items-center gap-3">
                            <span class="text-xs sm:text-sm text-gray-600 bg-gray-100 px-3 py-1.5 rounded-full font-medium">Guest Mode</span>
                            <button @click="showAuthModal = true" class="text-xs sm:text-sm text-blue-600 hover:text-blue-700 font-medium hidden sm:block">
                                Login / Register
                            </button>
                        </div>
                    @endguest
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-4" x-ref="messagesContainer">
                    <template x-for="(msg, index) in messages" :key="index">
                        <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'" class="message-enter">
                            <div :class="msg.role === 'user' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-800'"
                                class="max-w-[85%] sm:max-w-3xl rounded-2xl p-4 sm:p-5 shadow-lg">
                                <div x-html="formatMessage(msg.content)" class="prose prose-sm sm:prose max-w-none"></div>
                            </div>
                        </div>
                    </template>

                    <!-- Loading Skeleton -->
                    <div x-show="loading" class="flex justify-start message-enter">
                        <div class="bg-white border border-gray-200 max-w-[85%] sm:max-w-3xl rounded-2xl p-5 shadow-lg">
                            <div class="flex space-x-2">
                                <div class="w-3 h-3 bg-gradient-to-r from-blue-400 to-indigo-400 rounded-full animate-bounce"></div>
                                <div class="w-3 h-3 bg-gradient-to-r from-blue-400 to-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                <div class="w-3 h-3 bg-gradient-to-r from-blue-400 to-indigo-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div x-show="messages.length === 0 && !loading" class="flex flex-col items-center justify-center h-full text-center px-4">
                        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-6 rounded-3xl shadow-2xl mb-6">
                            <img src="/logo.svg" alt="Ask Me Logo" class="h-16 w-16 sm:h-20 sm:w-20">
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-3">Welcome to Ask Me</h2>
                        <p class="text-gray-600 mb-8 max-w-md text-sm sm:text-base">Your AI-powered chat assistant. Ask me anything and I'll help you find the answers.</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl w-full">
                            <button @click="userInput = 'What can you help me with?'; sendMessage()" 
                                class="bg-white border-2 border-gray-200 hover:border-blue-500 p-4 rounded-xl text-left transition-all duration-200 hover:shadow-lg group">
                                <div class="flex items-start gap-3">
                                    <div class="bg-blue-100 p-2 rounded-lg group-hover:bg-blue-200 transition-colors">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">What can you help me with?</p>
                                        <p class="text-xs text-gray-500 mt-1">Learn about my capabilities</p>
                                    </div>
                                </div>
                            </button>
                            <button @click="userInput = 'Explain quantum computing in simple terms'; sendMessage()" 
                                class="bg-white border-2 border-gray-200 hover:border-blue-500 p-4 rounded-xl text-left transition-all duration-200 hover:shadow-lg group">
                                <div class="flex items-start gap-3">
                                    <div class="bg-indigo-100 p-2 rounded-lg group-hover:bg-indigo-200 transition-colors">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">Explain quantum computing</p>
                                        <p class="text-xs text-gray-500 mt-1">In simple terms</p>
                                    </div>
                                </div>
                            </button>
                            <button @click="userInput = 'Write a Python function to sort a list'; sendMessage()" 
                                class="bg-white border-2 border-gray-200 hover:border-blue-500 p-4 rounded-xl text-left transition-all duration-200 hover:shadow-lg group">
                                <div class="flex items-start gap-3">
                                    <div class="bg-green-100 p-2 rounded-lg group-hover:bg-green-200 transition-colors">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">Write Python code</p>
                                        <p class="text-xs text-gray-500 mt-1">Function to sort a list</p>
                                    </div>
                                </div>
                            </button>
                            <button @click="userInput = 'Give me tips for better productivity'; sendMessage()" 
                                class="bg-white border-2 border-gray-200 hover:border-blue-500 p-4 rounded-xl text-left transition-all duration-200 hover:shadow-lg group">
                                <div class="flex items-start gap-3">
                                    <div class="bg-purple-100 p-2 rounded-lg group-hover:bg-purple-200 transition-colors">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">Productivity tips</p>
                                        <p class="text-xs text-gray-500 mt-1">Improve your workflow</p>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Input -->
                <div class="bg-white/80 backdrop-blur-lg border-t border-gray-200 p-4 sm:p-6 shadow-lg">
                    <form @submit.prevent="sendMessage" class="max-w-4xl mx-auto">
                        <div class="flex gap-2 sm:gap-3">
                            <input type="text" 
                                   x-model="userInput" 
                                   :disabled="loading"
                                   placeholder="Type your message..."
                                   class="flex-1 border-2 border-gray-200 rounded-xl sm:rounded-2xl px-4 sm:px-6 py-3 sm:py-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed transition-all duration-200 text-sm sm:text-base">
                            <button type="submit" 
                                    :disabled="loading || !userInput.trim()"
                                    class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 sm:px-8 py-3 sm:py-4 rounded-xl sm:rounded-2xl hover:from-blue-700 hover:to-indigo-700 disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed transition-all duration-200 font-medium shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:transform-none flex items-center gap-2">
                                <span class="hidden sm:inline">Send</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mt-3 text-center">Ask Me can make mistakes. Consider checking important information.</p>
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
                            'Are you sure you want to delete this conversation? This action cannot be undone.',
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
                            .replace(/\*\*(.*?)\*\*/g, '<strong class="font-semibold">$1</strong>')
                            .replace(/\*(.*?)\*/g, '<em class="italic">$1</em>')
                            .replace(/`(.*?)`/g, '<code class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded text-sm font-mono">$1</code>')
                            .replace(/```([\s\S]*?)```/g, '<pre class="bg-slate-900 text-slate-100 p-4 rounded-xl overflow-x-auto my-3"><code>$1</code></pre>')
                            .replace(/\n/g, '<br>');
                    }
                }
            }
        </script>
    </div>
@endsection
