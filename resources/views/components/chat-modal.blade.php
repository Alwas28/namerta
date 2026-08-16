<!-- Chat Modal - File terpisah: resources/views/components/chat-modal.blade.php -->
<div id="chatModal" class="hidden fixed inset-0 z-50 pointer-events-none">
    <div class="flex justify-end h-full">
        <div class="w-96 bg-white shadow-2xl border-l border-slate-200 flex flex-col h-full pointer-events-auto transform transition-transform duration-300 translate-x-full" id="chatContainer">
            <!-- Chat Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-edu-blue to-edu-green border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-white font-semibold">Chat Pembelajaran</h3>
                        <p class="text-white/80 text-sm">Eksplorasi Konsep</p>
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
                <!-- Chat messages akan dimuat di sini -->
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-sm">Belum ada pesan</p>
                    <p class="text-slate-400 text-xs mt-1">Mulai diskusi tentang materi pembelajaran</p>
                </div>
            </div>

            <!-- Chat Input Area -->
            <div class="p-4 border-t border-slate-200 bg-white">
                <form id="chatForm" class="flex space-x-2">
                    <div class="flex-1">
                        <textarea id="chatInput" 
                                placeholder="Tulis pesan Anda..." 
                                rows="2"
                                class="w-full px-3 py-2 border border-slate-300 rounded-lg resize-none focus:ring-2 focus:ring-edu-blue focus:border-transparent text-sm"
                                maxlength="500"></textarea>
                        <div class="text-xs text-slate-400 mt-1 text-right">
                            <span id="charCount">0</span>/500
                        </div>
                    </div>
                    <button type="submit" 
                            id="sendButton"
                            class="px-4 py-2 bg-edu-blue text-white rounded-lg hover:bg-edu-blue/90 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>

            <!-- Online Users (Optional) -->
            <div class="px-4 py-2 bg-slate-100 border-t border-slate-200">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                    <span class="text-xs text-slate-600">Online: <span id="onlineCount">1</span> pengguna</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    #chatModal.show #chatContainer {
        transform: translateX(0);
    }
    
    .chat-message {
        max-width: 80%;
        word-wrap: break-word;
    }
    
    .chat-message.own {
        margin-left: auto;
        background: linear-gradient(135deg, #3b82f6, #10b981);
        color: white;
    }
    
    .chat-message.other {
        margin-right: auto;
        background: white;
        border: 1px solid #e2e8f0;
        color: #334155;
    }
    
    .chat-time {
        font-size: 0.7rem;
        opacity: 0.7;
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
</style>

<script>
    // Chat Modal Functions
    function openChatModal() {
        const modal = document.getElementById('chatModal');
        const container = document.getElementById('chatContainer');
        
        modal.classList.remove('hidden');
        // Trigger animation after element is visible
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
        
        // Load chat history
        loadChatHistory();
        
        // Focus on input
        setTimeout(() => {
            document.getElementById('chatInput').focus();
        }, 300);
    }
    
    function closeChatModal() {
        const modal = document.getElementById('chatModal');
        const container = document.getElementById('chatContainer');
        
        modal.classList.remove('show');
        
        // Hide modal after animation
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }
    
    // Load chat history from localStorage or API
    function loadChatHistory() {
        const chatMessages = document.getElementById('chatMessages');
        
        // Get stored messages (you can replace this with API call)
        const messages = getChatMessages();
        
        if (messages.length === 0) {
            // Show empty state
            chatMessages.innerHTML = `
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <p class="text-slate-500 text-sm">Belum ada pesan</p>
                    <p class="text-slate-400 text-xs mt-1">Mulai diskusi tentang materi pembelajaran</p>
                </div>
            `;
        } else {
            // Display messages
            displayMessages(messages);
        }
    }
    
    function getChatMessages() {
        // Get from localStorage (you can replace with API call)
        const stored = localStorage.getItem('eksplorasi_chat_messages');
        return stored ? JSON.parse(stored) : [];
    }
    
    function saveChatMessage(message) {
        const messages = getChatMessages();
        messages.push(message);
        localStorage.setItem('eksplorasi_chat_messages', JSON.stringify(messages));
    }
    
    function displayMessages(messages) {
        const chatMessages = document.getElementById('chatMessages');
        const currentUser = '{{ auth()->user()->nama ?? "Pengguna" }}'; // Get current user name
        
        chatMessages.innerHTML = messages.map(msg => `
            <div class="flex flex-col ${msg.sender === currentUser ? 'items-end' : 'items-start'}">
                <div class="chat-message ${msg.sender === currentUser ? 'own' : 'other'} px-3 py-2 rounded-lg">
                    <div class="font-medium text-xs mb-1 ${msg.sender === currentUser ? 'text-white/80' : 'text-slate-500'}">
                        ${msg.sender}
                    </div>
                    <div class="text-sm">${msg.message}</div>
                    <div class="chat-time mt-1 ${msg.sender === currentUser ? 'text-white/60' : 'text-slate-400'}">
                        ${formatTime(msg.timestamp)}
                    </div>
                </div>
            </div>
        `).join('');
        
        // Scroll to bottom
        scrollToBottom();
    }
    
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }
    
    function scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Chat form handling
    document.addEventListener('DOMContentLoaded', function() {
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const sendButton = document.getElementById('sendButton');
        const charCount = document.getElementById('charCount');
        
        // Character counter
        chatInput.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count;
            
            if (count > 450) {
                charCount.classList.add('text-red-500');
            } else {
                charCount.classList.remove('text-red-500');
            }
            
            // Enable/disable send button
            sendButton.disabled = count === 0 || count > 500;
        });
        
        // Handle form submission
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = chatInput.value.trim();
            if (!message) return;
            
            // Create message object
            const newMessage = {
                id: Date.now(),
                sender: '{{ auth()->user()->nama ?? "Pengguna" }}',
                message: message,
                timestamp: new Date().toISOString()
            };
            
            // Save message
            saveChatMessage(newMessage);
            
            // Clear input
            chatInput.value = '';
            charCount.textContent = '0';
            sendButton.disabled = true;
            
            // Reload messages
            loadChatHistory();
            
            // Here you can add AJAX call to send message to server
            // sendMessageToServer(newMessage);
        });
        
        // Auto-resize textarea
        chatInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });
        
        // Enter to send (Shift+Enter for new line)
        chatInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });
    });
    
    // Close modal when clicking outside
    document.getElementById('chatModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeChatModal();
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeChatModal();
        }
    });
    
    // Optional: Send message to server (currently disabled)
    function sendMessageToServer(message) {
        // TODO: Implement server-side chat storage
        // Uncomment and create the route when ready:
        /*
        fetch('/chat/send/{{ $courseId }}/{{ $material->id_materi }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(message)
        })
        .then(response => response.json())
        .then(data => {
            console.log('Message sent:', data);
        })
        .catch(error => {
            console.error('Error sending message:', error);
        });
        */
        
        // For now, just log the message
        console.log('Message would be sent to server:', message);
    }
</script>