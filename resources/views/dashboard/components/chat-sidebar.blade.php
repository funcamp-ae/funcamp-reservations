  Chat List Sidebar
  <div class="col-md-4 border-end">
                            <div class="p-3 border-bottom bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-comments text-primary me-2"></i>
                                        Messages
                                    </h5>
                                    <button class="btn btn-sm btn-primary" id="newChatBtn">
                                        <i class="fas fa-plus"></i> New Chat
                                    </button>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" placeholder="Search chats..." id="chatSearch">
                                </div>
                            </div>
                            <div class="chat-list" id="chatList" style="height: calc(80vh - 120px); overflow-y: auto;">
                                <div class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <p class="mt-2 text-muted">Loading chats...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chat Window -->
                        <div class="col-md-8">
                            <div class="d-flex flex-column h-100">
                                <!-- Chat Header -->
                                <div class="border-bottom p-3 bg-light" id="chatHeader" style="display: none;">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;" id="chatAvatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <h5 class="mb-0" id="chatTitle">Select a chat</h5>
                                                <small class="text-muted" id="chatSubtitle">Start a conversation</small>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>View Profile</a></li>
                                                <li><a class="dropdown-item" href="#"><i class="fas fa-bell-slash me-2"></i>Mute Notifications</a></li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-trash-alt me-2"></i>Clear Chat</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Messages Area -->
                                <div class="flex-grow-1 p-3" id="messagesArea" style="overflow-y: auto; background-color: #f8f9fa;">
                                    <div class="text-center py-5">
                                        <i class="fas fa-comment text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="text-muted mt-3">Welcome to Chat</h5>
                                        <p class="text-muted">Select a chat from the sidebar to start messaging</p>
                                    </div>
                                </div>
                                
                                <!-- Message Input -->
                                <div class="border-top p-3 bg-white" id="messageInputArea" style="display: none;">
                                    <div class="input-group">
                                        <input type="text" class="form-control" placeholder="Type a message..." id="messageInput">
                                        <button class="btn btn-primary" id="sendBtn">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start New Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Chat Type</label>
                    <select class="form-select" id="chatType">
                        <option value="private">Private Chat</option>
                        <option value="group">Group Chat</option>
                    </select>
                </div>
                
                <!-- Private Chat Section -->
                <div id="privateChatSection">
                    <div class="mb-3">
                        <label class="form-label">Select User</label>
                        <select class="form-select" id="selectedUser">
                            <option value="">Choose a user...</option>
                        </select>
                    </div>
                </div>
                
                <!-- Group Chat Section -->
                <div id="groupChatSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">Group Name</label>
                        <input type="text" class="form-control" id="groupName" placeholder="Enter group name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Group Description</label>
                        <textarea class="form-control" id="groupDescription" rows="2" placeholder="Enter group description (optional)"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Members</label>
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;" id="groupMembers">
                            <!-- Group members will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="createChatBtn">Create Chat</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
class ChatSystem {
    constructor() {
        this.currentChatId = null;
        this.currentUserId = {{ auth()->id() }};
        this.chats = [];
        this.users = [];
        this.init();
    }

    init() {
        console.log('Initializing Chat System...');
        this.bindEvents();
        this.loadUsers();
        this.loadChats();
    }

    bindEvents() {
        // New chat button
        document.getElementById('newChatBtn').addEventListener('click', () => {
            new bootstrap.Modal(document.getElementById('newChatModal')).show();
        });

        // Chat type change
        document.getElementById('chatType').addEventListener('change', (e) => {
            const type = e.target.value;
            document.getElementById('privateChatSection').style.display = type === 'private' ? 'block' : 'none';
            document.getElementById('groupChatSection').style.display = type === 'group' ? 'block' : 'none';
            
            if (type === 'group') {
                this.loadGroupMembers();
            }
        });

        // Create chat button
        document.getElementById('createChatBtn').addEventListener('click', () => {
            this.createChat();
        });

        // Send message
        document.getElementById('sendBtn').addEventListener('click', () => {
            this.sendMessage();
        });

        // Send message on Enter
        document.getElementById('messageInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Search chats
        document.getElementById('chatSearch').addEventListener('input', (e) => {
            this.filterChats(e.target.value);
        });
    }

    async loadChats() {
        try {
            console.log('Loading chats...');
            const response = await fetch('/api/chat', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            console.log('Chats response:', data);

            if (data.success && data.chats) {
                this.chats = data.chats;
                this.renderChats();
            } else {
                this.renderChats([]);
            }
        } catch (error) {
            console.error('Error loading chats:', error);
            this.renderChats([]);
        }
    }

    async loadUsers() {
        try {
            const response = await fetch('/api/chat/users', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            console.log('Users response:', data);

            if (data.success && data.users) {
                this.users = data.users;
                this.renderUserOptions();
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    renderUserOptions() {
        const select = document.getElementById('selectedUser');
        select.innerHTML = '<option value="">Choose a user...</option>';
        
        this.users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = user.name;
            select.appendChild(option);
        });
    }

    renderChats(chats = this.chats) {
        const chatList = document.getElementById('chatList');
        
        if (chats.length === 0) {
            chatList.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-comments text-muted" style="font-size: 3rem;"></i>
                    <h6 class="text-muted mt-3">No chats yet</h6>
                    <p class="text-muted">Start a conversation with someone</p>
                    <button class="btn btn-primary" onclick="document.getElementById('newChatBtn').click()">
                        <i class="fas fa-plus me-1"></i> Start New Chat
                    </button>
                </div>
            `;
            return;
        }

        let html = '';
        chats.forEach(chat => {
            const isGroup = chat.type === 'group';
            const chatName = isGroup ? chat.name : this.getOtherUserName(chat.users);
            const latestMessage = chat.latest_message;
            const unreadCount = chat.unread_count || 0;
            
            html += `
                <div class="chat-item p-3 border-bottom" data-chat-id="${chat.id}" style="cursor: pointer; transition: background-color 0.2s;">
                    <div class="d-flex align-items-center">
                        <div class="me-3 position-relative">
                            ${isGroup ? 
                                '<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-users"></i></div>' :
                                '<div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-user"></i></div>'
                            }
                            ${unreadCount > 0 ? `<span class="position-absolute top-0 end-0 badge bg-danger rounded-pill" style="font-size: 0.7rem;">${unreadCount}</span>` : ''}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${chatName}</h6>
                                    <small class="text-muted">
                                        ${latestMessage ? this.truncateMessage(latestMessage.message, 40) : 'No messages yet'}
                                    </small>
                                </div>
                                <div class="text-end">
                                    ${latestMessage ? `<small class="text-muted">${this.formatTime(latestMessage.created_at)}</small>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        chatList.innerHTML = html;

        // Bind click events
        chatList.querySelectorAll('.chat-item').forEach(item => {
            item.addEventListener('click', () => {
                const chatId = item.getAttribute('data-chat-id');
                this.openChat(chatId);
            });
            
            // Hover effects
            item.addEventListener('mouseenter', () => {
                item.style.backgroundColor = '#f8f9fa';
            });
            
            item.addEventListener('mouseleave', () => {
                item.style.backgroundColor = '';
            });
        });
    }

    async openChat(chatId) {
        try {
            console.log('Opening chat:', chatId);
            this.currentChatId = chatId;
            
            // Mark as active
            document.querySelectorAll('.chat-item').forEach(item => {
                item.style.backgroundColor = '';
            });
            
            const chatItem = document.querySelector(`[data-chat-id="${chatId}"]`);
            if (chatItem) {
                chatItem.style.backgroundColor = '#e3f2fd';
            }
            
            const response = await fetch(`/api/chat/${chatId}`, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();
            console.log('Chat details:', data);

            if (data.success && data.chat) {
                this.currentMessages = data.messages || [];
                this.renderChatWindow(data.chat, this.currentMessages);
            } else {
                console.error('Failed to load chat:', data);
                alert('Failed to load chat details');
            }
        } catch (error) {
            console.error('Error opening chat:', error);
            alert('Failed to open chat: ' + error.message);
        }
    }

    renderChatWindow(chat, messages) {
        const isGroup = chat.type === 'group';
        const chatName = isGroup ? chat.name : this.getOtherUserName(chat.users);
        
        // Update header
        document.getElementById('chatTitle').textContent = chatName;
        document.getElementById('chatSubtitle').textContent = isGroup ? `${chat.users.length} members` : 'Online';
        document.getElementById('chatHeader').style.display = 'block';
        document.getElementById('messageInputArea').style.display = 'block';
        
        // Update avatar
        const avatar = document.getElementById('chatAvatar');
        if (isGroup) {
            avatar.innerHTML = '<i class="fas fa-users"></i>';
            avatar.className = 'bg-primary text-white rounded-circle d-flex align-items-center justify-content-center';
        } else {
            avatar.innerHTML = '<i class="fas fa-user"></i>';
            avatar.className = 'bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center';
        }
        
        // Render messages
        this.renderMessages(messages);
    }

    renderMessages(messages) {
        const messagesArea = document.getElementById('messagesArea');
        
        if (messages.length === 0) {
            messagesArea.innerHTML = `
                <div class="text-center py-5">
                    <i class="fas fa-comment text-muted" style="font-size: 3rem;"></i>
                    <h6 class="text-muted mt-3">No messages yet</h6>
                    <p class="text-muted">Start the conversation!</p>
                </div>
            `;
            return;
        }

        let html = '';
        messages.forEach(message => {
            const isOwn = message.user_id == this.currentUserId;
            html += `
                <div class="message mb-3 d-flex ${isOwn ? 'justify-content-start' : 'justify-content-end'}">
                    <div class="message-content ${isOwn ? 'text-end' : 'text-start'}" style="max-width: 70%;">
                        <div class="d-inline-block p-3 rounded ${isOwn ? 'bg-primary text-white' : 'bg-white border shadow-sm'}" style="max-width: 100%;">
                            ${this.escapeHtml(message.message)}
                        </div>
                        <div class="small text-muted mt-1">
                            ${this.formatTime(message.created_at)}
                            ${isOwn && message.is_read ? '<i class="fas fa-check-double text-primary ms-1"></i>' : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        messagesArea.innerHTML = html;
        messagesArea.scrollTop = messagesArea.scrollHeight;
    }

    async sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        
        if (!message || !this.currentChatId) return;

        // Clear input immediately
        input.value = '';
        
        // Create temporary message object for instant display
        const tempMessage = {
            id: 'temp_' + Date.now(),
            user_id: this.currentUserId,
            message: message,
            created_at: new Date().toISOString(),
            is_read: false,
            is_temp: true
        };

        // Add message to current messages array
        if (!this.currentMessages) {
            this.currentMessages = [];
        }
        this.currentMessages.push(tempMessage);
        
        // Render messages instantly
        this.renderMessages(this.currentMessages);

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            
            if (data.success) {
                // Remove temp message and add real message
                this.currentMessages = this.currentMessages.filter(msg => !msg.is_temp);
                this.currentMessages.push(data.message);
                this.renderMessages(this.currentMessages);
                
                // Reload chat list to update latest message
                this.loadChats();
            } else {
                // Remove temp message if sending failed
                this.currentMessages = this.currentMessages.filter(msg => !msg.is_temp);
                this.renderMessages(this.currentMessages);
                alert('Failed to send message: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error sending message:', error);
            // Remove temp message if sending failed
            this.currentMessages = this.currentMessages.filter(msg => !msg.is_temp);
            this.renderMessages(this.currentMessages);
            alert('Failed to send message: ' + error.message);
        }
    }

    async createChat() {
        const chatType = document.getElementById('chatType').value;
        
        if (chatType === 'private') {
            const userId = document.getElementById('selectedUser').value;
            if (!userId) {
                alert('Please select a user');
                return;
            }
            
            try {
                const response = await fetch('/api/chat/private', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ user_id: userId })
                });

                const data = await response.json();
                
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('newChatModal')).hide();
                    this.loadChats();
                    // Wait a bit for the chat list to update before opening the chat
                    setTimeout(() => {
                        this.openChat(data.chat.id);
                    }, 500);
                } else {
                    console.error('Failed to create private chat:', data);
                    alert('Failed to create chat: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error creating private chat:', error);
                alert('Failed to create chat: ' + error.message);
            }
        } else {
            // Group chat creation
            const groupName = document.getElementById('groupName').value.trim();
            const groupDescription = document.getElementById('groupDescription').value.trim();
            const selectedMembers = Array.from(document.querySelectorAll('#groupMembers input:checked')).map(cb => parseInt(cb.value));
            
            if (!groupName) {
                alert('Please enter a group name');
                return;
            }
            
            if (selectedMembers.length === 0) {
                alert('Please select at least one member');
                return;
            }
            
            try {
                const response = await fetch('/api/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        name: groupName,
                        description: groupDescription,
                        user_ids: selectedMembers
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('newChatModal')).hide();
                    this.loadChats();
                    // Wait a bit for the chat list to update before opening the chat
                    setTimeout(() => {
                        this.openChat(data.chat.id);
                    }, 500);
                } else {
                    console.error('Failed to create group chat:', data);
                    alert('Failed to create group chat: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error creating group chat:', error);
                alert('Failed to create group chat: ' + error.message);
            }
        }
    }

    loadGroupMembers() {
        const container = document.getElementById('groupMembers');
        let html = '';
        
        this.users.forEach(user => {
            html += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${user.id}" id="user-${user.id}">
                    <label class="form-check-label" for="user-${user.id}">
                        ${user.name}
                    </label>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    filterChats(query) {
        const filteredChats = this.chats.filter(chat => {
            const chatName = chat.type === 'group' ? chat.name : this.getOtherUserName(chat.users);
            return chatName.toLowerCase().includes(query.toLowerCase());
        });
        
        this.renderChats(filteredChats);
    }

    // Utility functions
    getOtherUserName(users) {
        const otherUser = users.find(user => user.id != this.currentUserId);
        return otherUser?.name || 'Unknown User';
    }

    truncateMessage(message, length = 50) {
        return message.length > length ? message.substring(0, length) + '...' : message;
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);

        if (diffInHours < 1) {
            const minutes = Math.floor((now - date) / (1000 * 60));
            return minutes === 0 ? 'Just now' : `${minutes}m ago`;
        } else if (diffInHours < 24) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } else {
            return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
        }
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    window.chatSystem = new ChatSystem();
});
</script>
@endpush

@push('styles')
<style>
.chat-item:hover {
    background-color: #f8f9fa !important;
}

.message {
    word-wrap: break-word;
}

.message-content {
    display: flex;
    flex-direction: column;
}

/* Your messages (right side) */
.message.justify-content-end .message-content {
    align-items: flex-end;
}

.message.justify-content-end .message-content .d-inline-block {
    background-color: #007bff !important;
    color: white !important;
    border-radius: 18px 18px 5px 18px;
}

/* Other messages (left side) */
.message.justify-content-start .message-content {
    align-items: flex-start;
}

.message.justify-content-start .message-content .d-inline-block {
    background-color: #f1f3f6 !important;
    color: #333 !important;
    border-radius: 18px 18px 18px 5px;
    border: 1px solid #e1e5e9 !important;
}

#chatList {
    scrollbar-width: thin;
    scrollbar-color: #ccc #f1f1f1;
}

#chatList::-webkit-scrollbar {
    width: 6px;
}

#chatList::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#chatList::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

#messagesArea {
    scrollbar-width: thin;
    scrollbar-color: #ccc #f1f1f1;
}

#messagesArea::-webkit-scrollbar {
    width: 6px;
}

#messagesArea::-webkit-scrollbar-track {
    background: #f1f1f1;
}

#messagesArea::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}
</style>
@endpush
