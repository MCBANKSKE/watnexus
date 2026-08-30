<x-filament-panels::page>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <h2 class="text-2xl font-bold">Chat</h2>
            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-500">
                    {{ $this->conversations->count() }} conversations
                </span>
                <button wire:click="refreshConversations" class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-220px)]">
        <!-- Conversations List -->
        <div class="lg:col-span-1 h-full">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full flex flex-col overflow-hidden">
                <!-- Search & Filter -->
                <div class="p-4 border-b border-gray-200 space-y-3">
                    <div class="relative">
                        <input 
                            wire:model.live="search" 
                            type="text" 
                            placeholder="Search conversations..." 
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border-0 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white transition"
                        >
                        <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    
                    <div class="flex space-x-1">
                        <button wire:click="setFilter('all')" class="px-3 py-1.5 text-xs font-medium rounded-full transition {{ $filter === 'all' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            All
                        </button>
                        <button wire:click="setFilter('open')" class="px-3 py-1.5 text-xs font-medium rounded-full transition {{ $filter === 'open' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Open
                        </button>
                        <button wire:click="setFilter('pending')" class="px-3 py-1.5 text-xs font-medium rounded-full transition {{ $filter === 'pending' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Pending
                        </button>
                        <button wire:click="setFilter('closed')" class="px-3 py-1.5 text-xs font-medium rounded-full transition {{ $filter === 'closed' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            Closed
                        </button>
                    </div>
                </div>

                <!-- Conversations -->
                <div class="flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 hover:scrollbar-thumb-gray-400">
                    @forelse($this->conversations as $conversation)
                        <div 
                            wire:click="selectConversation({{ $conversation->id }})"
                            class="relative px-4 py-3 cursor-pointer transition hover:bg-gray-50 border-b border-gray-100 {{ $this->selectedConversationId === $conversation->id ? 'bg-indigo-50 border-l-4 border-l-indigo-500' : '' }}"
                        >
                            <div class="flex items-start space-x-3">
                                <!-- Avatar -->
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-indigo-400 to-purple-400 flex items-center justify-center text-white font-semibold text-sm">
                                        {{ strtoupper(substr($conversation->contact->name ?? 'U', 0, 2)) }}
                                    </div>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-semibold text-gray-900 truncate">
                                            {{ $conversation->contact->name ?? 'Unknown' }}
                                        </h4>
                                        <span class="text-xs text-gray-400 flex-shrink-0 ml-2">
                                            {{ $conversation->last_message_at?->diffForHumans() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between mt-0.5">
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ $conversation->last_message ?: 'No messages yet' }}
                                        </p>
                                        @if($conversation->unread_count > 0)
                                            <span class="flex-shrink-0 ml-2 px-2 py-0.5 text-xs font-bold rounded-full bg-red-500 text-white">
                                                {{ $conversation->unread_count }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2 mt-1">
                                        @if($conversation->status === 'pending')
                                            <span class="text-[10px] font-medium text-yellow-600 bg-yellow-100 px-2 py-0.5 rounded-full">Pending</span>
                                        @elseif($conversation->status === 'closed')
                                            <span class="text-[10px] font-medium text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">Closed</span>
                                        @else
                                            <span class="text-[10px] font-medium text-green-600 bg-green-100 px-2 py-0.5 rounded-full">Active</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-center p-8">
                            <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p class="text-sm text-gray-500">No conversations found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="lg:col-span-2 h-full">
            @if($this->selectedConversation)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full flex flex-col overflow-hidden">
                    <!-- Chat Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-white to-gray-50">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-indigo-400 to-purple-400 flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($this->selectedConversation->contact->name ?? 'U', 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $this->selectedConversation->contact->name ?? 'Unknown' }}</h3>
                                <div class="flex items-center space-x-2 text-xs">
                                    <span class="text-gray-500">{{ $this->selectedConversation->contact->phone }}</span>
                                    <span class="text-gray-300">•</span>
                                    <span class="{{ $this->selectedConversation->isOpen() ? 'text-green-600' : 'text-gray-500' }}">
                                        {{ $this->selectedConversation->isOpen() ? 'Online' : 'Offline' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button wire:click="toggleConversationStatus" class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $this->selectedConversation->isOpen() ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }}">
                                {{ $this->selectedConversation->isOpen() ? 'Close' : 'Open' }}
                            </button>
                            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="flex-1 overflow-y-auto p-6 space-y-3 bg-gray-50 scrollbar-thin scrollbar-thumb-gray-300 hover:scrollbar-thumb-gray-400" id="messages-container">
                        @foreach($this->messages as $message)
                            <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }} animate-fade-in">
                                <div class="max-w-[70%] {{ $message->direction === 'outbound' ? 'bg-indigo-600 text-white rounded-2xl rounded-br-none' : 'bg-white text-gray-800 rounded-2xl rounded-bl-none shadow-sm border border-gray-200' }} px-4 py-2.5">
                                    <div class="text-sm leading-relaxed">{{ $message->body }}</div>
                                    <div class="flex items-center justify-end space-x-1 mt-1">
                                        <span class="text-[10px] {{ $message->direction === 'outbound' ? 'text-indigo-200' : 'text-gray-400' }}">
                                            {{ $message->created_at->format('H:i') }}
                                        </span>
                                        @if($message->direction === 'outbound')
                                            <svg class="w-3 h-3 text-indigo-200" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L14.586 10l-4.293-4.293a1 1 0 111.414-1.414l5 5a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div x-data x-init="$el.scrollIntoView({ behavior: 'smooth' })"></div>
                    </div>

                    <!-- Message Input -->
                    <div class="p-4 border-t border-gray-200 bg-white">
                        <div class="space-y-3">
                            <!-- Phone Number & Template Selection -->
                            <div class="grid grid-cols-2 gap-3">
                                <select wire:model="selectedPhoneNumberId" class="text-sm rounded-lg border-gray-200 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 transition">
                                    <option value="">📱 Select Number</option>
                                    @foreach($this->connectedPhoneNumbers as $phoneNumber)
                                        <option value="{{ $phoneNumber->id }}">{{ $phoneNumber->phone_number }}</option>
                                    @endforeach
                                </select>

                                @if($this->approvedTemplates->count() > 0)
                                    <select wire:model="selectedTemplateId" class="text-sm rounded-lg border-gray-200 bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 transition">
                                        <option value="">📋 Select Template</option>
                                        @foreach($this->approvedTemplates as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>

                            <!-- Input & Actions -->
                            <div class="flex items-end space-x-2">
                                <div class="flex-1 relative">
                                    <input 
                                        wire:model="message" 
                                        type="text" 
                                        placeholder="Type a message..." 
                                        class="w-full pr-12 py-2.5 pl-4 rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 transition bg-gray-50 focus:bg-white"
                                        @keydown.enter="sendMessage"
                                        wire:keydown.enter="sendMessage"
                                    >
                                    <button class="absolute right-2 top-2 p-1.5 text-gray-400 hover:text-indigo-500 rounded-lg hover:bg-indigo-50 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 4.5" />
                                        </svg>
                                    </button>
                                </div>
                                @if($this->selectedTemplateId)
                                    <button wire:click="sendTemplate" class="px-4 py-2.5 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition flex items-center space-x-2 text-sm font-medium whitespace-nowrap">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>Template</span>
                                    </button>
                                @endif
                                <button wire:click="sendMessage" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center space-x-2 text-sm font-medium">
                                    <span>Send</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Character counter -->
                            <div class="text-xs text-gray-400 text-right">
                                {{ strlen($message ?? '') }}/160 characters
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full flex flex-col items-center justify-center p-12">
                    <div class="w-24 h-24 bg-gradient-to-br from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Conversation Selected</h3>
                    <p class="text-sm text-gray-500 max-w-sm text-center">Select a conversation from the list to start chatting, or wait for a new message to arrive.</p>
                </div>
            @endif
        </div>
    </div>

    @push('styles')
    <style>
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 9999px;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
    @endpush
</x-filament-panels::page>