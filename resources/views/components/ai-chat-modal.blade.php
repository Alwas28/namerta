<!-- Updated Chat AI Modal - File: resources/views/components/ai-chat-modal.blade.php -->
<div id="chatModal" class="hidden fixed inset-0 z-50 pointer-events-none">
    <div class="flex justify-end h-full">
        <div class="w-96 bg-white shadow-2xl border-l border-slate-200 flex flex-col h-full pointer-events-auto transform transition-transform duration-300 translate-x-full" id="chatContainer">
            <!-- Chat Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-edu-blue to-edu-green border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold">Rasya</h3>
                        <p class="text-white/80 text-sm flex items-center">
                            <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
                            Online - {{ $material->nama_materi ?? 'Eksplorasi Konsep' }}
                        </p>
                    </div>
                </div>
                <button onclick="closeChatModal()" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Chat Messages Area -->
            <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4 bg-slate-50" id="chatMessages">
                <!-- Welcome message from AI -->
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="bg-white rounded-lg px-4 py-3 border border-slate-200 max-w-[85%]">
                        <div class="font-medium text-xs text-purple-600 mb-1">AI Tutor</div>
                        <div class="text-sm text-slate-700">
                            Halo! Saya AI Tutor yang siap membantu Anda memahami materi <strong>{{ $material->nama_materi ?? 'pembelajaran' }}</strong>
                            @if(isset($material->modul))
                                dari modul <strong>{{ $material->modul->nama_modul }}</strong>
                            @endif
                            . Silakan tanyakan apapun tentang konsep yang sedang dipelajari!
                        </div>
                        <div class="text-xs text-slate-400 mt-2">Baru saja</div>
                    </div>
                </div>
            </div>

            <!-- Typing Indicator -->
            <div id="typingIndicator" class="hidden px-4 py-2">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="bg-white rounded-lg px-4 py-3 border border-slate-200">
                        <div class="flex space-x-1">
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="px-4 py-2 bg-slate-100 border-t border-slate-200">
                <div class="text-xs text-slate-600 mb-2">Pertanyaan Cepat:</div>
                <div class="flex flex-wrap gap-1">
                    <button onclick="sendQuickMessage('Jelaskan konsep utama dari materi ini')" 
                            class="bg-white text-slate-600 px-3 py-1 rounded-full text-xs hover:bg-slate-200 transition-colors border border-slate-300">
                        Konsep Utama
                    </button>
                    <button onclick="sendQuickMessage('Berikan contoh penerapan dari konsep ini')" 
                            class="bg-white text-slate-600 px-3 py-1 rounded-full text-xs hover:bg-slate-200 transition-colors border border-slate-300">
                        Contoh Penerapan
                    </button>
                    <button onclick="sendQuickMessage('Bagaimana cara melakukan refleksi untuk materi ini?')" 
                            class="bg-white text-slate-600 px-3 py-1 rounded-full text-xs hover:bg-slate-200 transition-colors border border-slate-300">
                        Panduan Refleksi
                    </button>
                    <button onclick="sendQuickMessage('Apa yang perlu saya pelajari selanjutnya?')" 
                            class="bg-white text-slate-600 px-3 py-1 rounded-full text-xs hover:bg-slate-200 transition-colors border border-slate-300">
                        Langkah Selanjutnya
                    </button>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="p-4 border-t border-slate-200 bg-white">
                <form id="chatForm" class="flex space-x-2" method="POST">
                    <div class="flex-1">
                        <textarea id="chatInput" 
                                placeholder="Tanyakan sesuatu tentang materi pembelajaran..." 
                                rows="2"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg resize-none focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                                maxlength="1000"></textarea>
                        <div class="text-xs text-slate-400 mt-1 flex justify-between">
                            <span>Shift + Enter untuk baris baru</span>
                            <span><span id="charCount">0</span>/1000</span>
                        </div>
                    </div>
                    <button type="submit" 
                            id="sendButton"
                            disabled
                            class="px-4 py-2 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    #chatModal.show #chatContainer {
        transform: translateX(0);
    }
    
    .chat-message-user {
        background: linear-gradient(135deg, #3b82f6, #10b981);
        color: white;
        margin-left: auto;
        max-width: 85%;
        border-radius: 18px 18px 4px 18px;
    }
    
    .chat-message-ai {
        background: white;
        border: 1px solid #e2e8f0;
        color: #334155;
        margin-right: auto;
        max-width: 85%;
        border-radius: 18px 18px 18px 4px;
    }

    /* Scrollbar styling */
    #chatMessages::-webkit-scrollbar {
        width: 4px;
    }
    
    #chatMessages::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    
    #chatMessages::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 2px;
    }
    
    #chatMessages::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Animation for new messages */
    @keyframes messageSlideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .message-animate {
        animation: messageSlideIn 0.3s ease-out;
    }
</style>

<script>
    // Chat AI Functions dengan parameter yang benar
    let isAIResponding = false;
    const modulId = {{ $material->id_modul ?? 'null' }};
    const materiId = {{ $material->id_materi ?? 'null' }};
    const csrfToken = '{{ csrf_token() }}';
    
    function openChatModal() {
        const modal = document.getElementById('chatModal');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        // Load chat history from server
        loadChatHistoryFromServer();
        
        // Focus on input after animation
        setTimeout(() => {
            document.getElementById('chatInput').focus();
        }, 300);
    }
    
    function closeChatModal() {
        const modal = document.getElementById('chatModal');
        modal.classList.remove('show');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
    
    // Load chat history from server
    async function loadChatHistoryFromServer() {
        try {
            const response = await fetch(`/api/ai-chat/${modulId}/${materiId}/history?context=eksplorasi_konsep`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.messages.length > 0) {
                displayServerMessages(data.messages);
            }
        } catch (error) {
            console.log('Failed to load chat history, using localStorage fallback');
            loadChatHistory();
        }
    }
    
    function displayServerMessages(messages) {
        const chatMessages = document.getElementById('chatMessages');
        const welcomeMessage = chatMessages.querySelector('.flex.items-start.space-x-3');
        
        // Convert server messages to display format
        const messagesHtml = messages.map(msg => {
            // Add user message
            const userMessageHtml = createMessageHtml({
                content: msg.user_message,
                type: 'user',
                timestamp: msg.timestamp
            });
            
            // Add AI response
            const aiMessageHtml = createMessageHtml({
                content: msg.ai_response,
                type: 'ai', 
                timestamp: msg.timestamp
            });
            
            return userMessageHtml + aiMessageHtml;
        }).join('');
        
        if (welcomeMessage) {
            welcomeMessage.insertAdjacentHTML('afterend', messagesHtml);
        }
        
        scrollToBottom();
    }
    
    // Fallback to localStorage
    function loadChatHistory() {
        const messages = getChatMessages();
        if (messages.length > 0) {
            displayMessages(messages);
        }
    }
    
    function getChatMessages() {
        const stored = localStorage.getItem(`ai_chat_${modulId}_${materiId}`);
        return stored ? JSON.parse(stored) : [];
    }
    
    function saveChatMessage(message) {
        const messages = getChatMessages();
        messages.push(message);
        // Keep only last 50 messages in localStorage
        if (messages.length > 50) {
            messages.splice(0, messages.length - 50);
        }
        localStorage.setItem(`ai_chat_${modulId}_${materiId}`, JSON.stringify(messages));
    }
    
    function displayMessages(messages) {
        const chatMessages = document.getElementById('chatMessages');
        const welcomeMessage = chatMessages.querySelector('.flex.items-start.space-x-3');
        
        const messagesHtml = messages.map(msg => createMessageHtml(msg)).join('');
        
        if (welcomeMessage) {
            welcomeMessage.insertAdjacentHTML('afterend', messagesHtml);
        } else {
            chatMessages.innerHTML = messagesHtml;
        }
        
        scrollToBottom();
    }
    
    function createMessageHtml(message) {
        const time = formatTime(message.timestamp);
        
        if (message.type === 'user') {
            return `
                <div class="flex justify-end message-animate">
                    <div class="chat-message-user px-4 py-3">
                        <div class="text-sm">${escapeHtml(message.content)}</div>
                        <div class="text-xs text-white/70 mt-1">${time}</div>
                    </div>
                </div>
            `;
        } else {
            return `
                <div class="flex items-start space-x-3 message-animate">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="chat-message-ai px-4 py-3">
                        <div class="font-medium text-xs text-purple-600 mb-1">Rasya</div>
                        <div class="text-sm text-slate-700">${formatAIResponse(message.content)}</div>
                        <div class="text-xs text-slate-400 mt-2">${time}</div>
                    </div>
                </div>
            `;
        }
    }
    
    function addMessage(content, type) {
        const message = {
            content: content,
            type: type,
            timestamp: new Date().toISOString()
        };
        
        // Save to localStorage as backup
        saveChatMessage(message);
        
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.insertAdjacentHTML('beforeend', createMessageHtml(message));
        
        scrollToBottom();
        return message;
    }
    
    function showTypingIndicator() {
        document.getElementById('typingIndicator').classList.remove('hidden');
        scrollToBottom();
    }
    
    function hideTypingIndicator() {
        document.getElementById('typingIndicator').classList.add('hidden');
    }
    
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function formatAIResponse(text) {
        // Simple formatting for AI responses
        return escapeHtml(text)
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>');
    }
    
    function scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    }
    
    // Send message to AI
    async function sendMessageToAI(message) {
        if (isAIResponding) return;
        
        isAIResponding = true;
        showTypingIndicator();
        
        try {
            const response = await fetch(`/api/ai-chat/${modulId}/${materiId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    context: 'eksplorasi_konsep'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                hideTypingIndicator();
                addMessage(data.response, 'ai');
            } else {
                throw new Error(data.message || 'Gagal mendapat respon dari AI');
            }
        } catch (error) {
            hideTypingIndicator();
            addMessage('Maaf, saya mengalami kesulitan teknis. Coba tanyakan lagi nanti ya!', 'ai');
            console.error('AI Chat Error:', error);
        }
        
        isAIResponding = false;
    }
    
    function sendQuickMessage(message) {
        if (isAIResponding) return;
        
        const chatInput = document.getElementById('chatInput');
        chatInput.value = message;
        chatInput.dispatchEvent(new Event('input'));
        
        // Trigger form submission
        document.getElementById('chatForm').dispatchEvent(new Event('submit'));
    }
    
    // Initialize chat functionality
    document.addEventListener('DOMContentLoaded', function() {
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const sendButton = document.getElementById('sendButton');
        const charCount = document.getElementById('charCount');
        
        if (!chatForm || !chatInput || !sendButton || !charCount) {
            console.error('Chat elements not found');
            return;
        }
        
        // Character counter and button state
        chatInput.addEventListener('input', function() {
            const count = this.value.trim().length;
            charCount.textContent = count;
            
            if (count > 900) {
                charCount.classList.add('text-red-500');
            } else {
                charCount.classList.remove('text-red-500');
            }
            
            sendButton.disabled = count === 0 || count > 1000 || isAIResponding;
        });
        
        // Handle form submission
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = chatInput.value.trim();
            if (!message || isAIResponding) return;
            
            // Add user message
            addMessage(message, 'user');
            
            // Clear input
            chatInput.value = '';
            charCount.textContent = '0';
            sendButton.disabled = true;
            
            // Send to AI
            sendMessageToAI(message);
        });
        
        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
        });
        
        // Enter to send (Shift+Enter for new line)
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (!sendButton.disabled) {
                    chatForm.dispatchEvent(new Event('submit'));
                }
            }
        });
    });
    
    // Close modal handlers
    document.getElementById('chatModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeChatModal();
        }
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeChatModal();
        }
    });
</script>