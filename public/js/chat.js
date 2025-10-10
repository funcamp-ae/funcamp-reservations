/**
 * Chat System JavaScript
 * Handles real-time chat functionality with Pusher and Echo
 */

class ChatSystem {
    constructor() {
        this.currentChatId = null;
        this.currentUserId = window.currentUserId || null;
        this.echo = null;
        this.chatChannels = {};
        this.init();
    }

    init() {
        this.initializePusher();
        this.bindEvents();
        this.loadUnreadCount();
        this.loadUsers();
    }

    initializePusher() {
        // Initialize Pusher and Echo
        if (typeof window.Echo !== 'undefined' && this.currentUserId) {
            this.echo = window.Echo.private('user.' + this.currentUserId);
            
            // Listen for new chats
            this.echo.listen('.chat.created', (e) => {
                this.handleNewChat(e.chat);
            });
        }
    }

    bindEvents() {

        // Chat toggle button
        $('#chat-toggle-btn').on('click', () => {
            $('#chat-sidebar').offcanvas('show');
            this.loadChats();
        });

        // Chat tabs
        $('.chat-tab').on('click', function() {
            const tab = $(this).data('tab');
            this.switchTab(tab);
        }.bind(this));

        // Back to chats button
        $('#back-to-chats').on('click', function() {
            $('#chat-window').offcanvas('hide');
            $('#chat-sidebar').offcanvas('show');
        });

        // Send message button
        $('#send-message-btn').on('click', () => {
            this.sendMessage();
        });

        // Send message on Enter key
        $('#message-input').on('keypress', (e) => {
            if (e.which === 13) {
                this.sendMessage();
            }
        });

        // Create group button
        $('#create-group-btn').on('click', () => {
            $('#createGroupModal').modal('show');
            this.loadUsersForGroup();
        });

        // Create group form
        $('#create-group-form').on('submit', (e) => {
            e.preventDefault();
            this.createGroup();
        });

        // Search functionality
        $('#chat-search').on('input', function() {
            const query = $(this).val().toLowerCase();
            this.filterChats(query);
        }.bind(this));

        $('#user-search').on('input', function() {
            const query = $(this).val().toLowerCase();
            this.filterUsers(query);
        }.bind(this));
    }

    switchTab(tab) {
        console.log('Switching to tab:', tab);
        $('.chat-tab').removeClass('active');
        $('.chat-tab-content').removeClass('active');
        
        $(`.chat-tab[data-tab="${tab}"]`).addClass('active');
        $(`#${tab}-tab`).addClass('active');

        if (tab === 'contacts') {
            console.log('Loading users for contacts tab');
            this.loadUsers();
        } else {
            console.log('Loading chats for chats tab');
            this.loadChats();
        }
    }

    async loadChats() {
        try {
            const response = await fetch('/api/chat', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.renderChats(data.chats);
            }
        } catch (error) {
            console.error('Error loading chats:', error);
        }
    }

    renderChats(chats) {
        const chatList = $('#chat-list');
        
        if (chats.length === 0) {
            chatList.html(`
                <div class="text-center py-5">
                    <i class="fas fa-comments text-muted fs-2x mb-3"></i>
                    <div class="text-muted">${window.chatTranslations.no_chats_yet}</div>
                </div>
            `);
            return;
        }

        let html = '';
        chats.forEach(chat => {
            const latestMessage = chat.latest_message;
            const unreadCount = chat.unread_count || 0;
            const isGroup = chat.type === 'group';
            
            html += `
                <div class="chat-item" data-chat-id="${chat.id}" data-chat-type="${chat.type}">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-40px me-3 position-relative">
                            ${isGroup ? 
                                `<div class="symbol-label bg-primary text-white">
                                    <i class="fas fa-users"></i>
                                </div>` :
                                `<img src="${this.getUserAvatar(chat.users)}" alt="" class="symbol-label">`
                            }
                            ${unreadCount > 0 ? `<span class="unread-badge">${unreadCount}</span>` : ''}
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">${isGroup ? chat.name : this.getOtherUserName(chat.users)}</h6>
                                    ${latestMessage ? 
                                        `<div class="text-muted small">${this.truncateMessage(latestMessage.message)}</div>` :
                                        `<div class="text-muted small">${window.chatTranslations.no_messages_yet}</div>`
                                    }
                                </div>
                                <div class="text-end">
                                    ${latestMessage ? 
                                        `<small class="text-muted">${this.formatTime(latestMessage.created_at)}</small>` :
                                        ''
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        chatList.html(html);

        // Bind chat item click events
        $('.chat-item').on('click', function() {
            const chatId = $(this).data('chat-id');
            this.openChat(chatId);
        }.bind(this));
    }

    async openChat(chatId) {
        this.currentChatId = chatId;
        
        try {
            const response = await fetch(`/api/chat/${chatId}`, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.renderChatWindow(data.chat, data.messages);
                $('#chat-sidebar').offcanvas('hide');
                $('#chat-window').offcanvas('show');
                
                // Subscribe to chat channel for real-time updates
                this.subscribeToChat(chatId);
            }
        } catch (error) {
            console.error('Error opening chat:', error);
        }
    }

    renderChatWindow(chat, messages) {
        // Update chat header
        $('#chat-title').text(chat.type === 'group' ? chat.name : this.getOtherUserName(chat.users));
        $('#chat-subtitle').text(chat.type === 'group' ? `${chat.users.length} ${window.chatTranslations.members}` : window.chatTranslations.online);
        
        if (chat.type === 'private') {
            $('#chat-avatar').attr('src', this.getUserAvatar(chat.users));
        } else {
            $('#chat-avatar').attr('src', '').replaceWith('<div class="symbol-label bg-primary text-white"><i class="fas fa-users"></i></div>');
        }

        // Render messages
        this.renderMessages(messages);
    }

    renderMessages(messages) {
        const container = $('#messages-container');
        
        if (messages.length === 0) {
            container.html(`
                <div class="text-center py-5">
                    <i class="fas fa-comment text-muted fs-2x mb-3"></i>
                    <div class="text-muted">${window.chatTranslations.start_conversation}</div>
                </div>
            `);
            return;
        }

        let html = '';
        messages.forEach(message => {
            const isOwnMessage = message.user_id == this.currentUserId;
            html += this.renderMessage(message, isOwnMessage);
        });

        container.html(html);
        container.scrollTop(container[0].scrollHeight);
    }

    renderMessage(message, isOwnMessage) {
        return `
            <div class="message ${isOwnMessage ? 'sent' : 'received'}">
                <div class="message-bubble">
                    ${message.message}
                </div>
                <div class="message-time">
                    ${this.formatTime(message.created_at)}
                    ${isOwnMessage && message.is_read ? '<i class="fas fa-check-double text-primary ms-1"></i>' : ''}
                </div>
            </div>
        `;
    }

    async sendMessage() {
        const messageInput = $('#message-input');
        const message = messageInput.val().trim();
        
        if (!message || !this.currentChatId) return;

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}/messages`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.getAuthToken(),
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    message: message
                })
            });

            const data = await response.json();
            
            if (data.success) {
                messageInput.val('');
                // Message will be added via real-time event
            }
        } catch (error) {
            console.error('Error sending message:', error);
        }
    }

    subscribeToChat(chatId) {
        if (!this.echo) return;

        // Unsubscribe from previous chat if exists
        if (this.chatChannels[this.currentChatId]) {
            this.echo.leave(`chat.${this.currentChatId}`);
            delete this.chatChannels[this.currentChatId];
        }

        // Subscribe to new chat
        this.chatChannels[chatId] = this.echo.private(`chat.${chatId}`)
            .listen('.message.sent', (e) => {
                this.handleNewMessage(e.message);
            });
    }

    handleNewMessage(message) {
        if (message.chat_id == this.currentChatId) {
            // Add message to current chat
            const isOwnMessage = message.user_id == this.currentUserId;
            const messageHtml = this.renderMessage(message, isOwnMessage);
            $('#messages-container').append(messageHtml);
            $('#messages-container').scrollTop($('#messages-container')[0].scrollHeight);
        }

        // Update chat list if sidebar is open
        this.loadChats();
        this.loadUnreadCount();
    }

    handleNewChat(chat) {
        // Reload chats when a new chat is created
        this.loadChats();
    }

    async loadUsers() {
        console.log('Loading users...');
        try {
            const response = await fetch('/api/chat/users', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success) {
                this.renderUsers(data.users);
            } else {
                console.error('API returned success: false', data);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    renderUsers(users) {
        console.log('Rendering users:', users);
        const userList = $('#user-list');
        
        if (users.length === 0) {
            console.log('No users found, showing empty state');
            userList.html(`
                <div class="text-center py-5">
                    <i class="fas fa-users text-muted fs-2x mb-3"></i>
                    <div class="text-muted">${window.chatTranslations.no_users_found}</div>
                </div>
            `);
            return;
        }

        console.log(`Rendering ${users.length} users`);

        let html = '';
        users.forEach(user => {
            html += `
                <div class="user-item" data-user-id="${user.id}">
                    <div class="d-flex align-items-center">
                        <div class="symbol symbol-40px me-3">
                            <img src="${user.image || '/images/logo.png'}" alt="" class="symbol-label">
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${user.name}</h6>
                            <div class="text-muted small">${user.email}</div>
                        </div>
                        <button class="btn btn-sm btn-primary" data-user-id="${user.id}">
                            <i class="fas fa-comment"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        userList.html(html);

        // Bind user click events
        $('.user-item .btn').on('click', (e) => {
            e.stopPropagation();
            const userId = $(e.currentTarget).data('user-id');
            console.log('User clicked:', userId);
            this.createPrivateChat(userId);
        });
    }

    async createPrivateChat(userId) {
        try {
            const response = await fetch('/api/chat/private', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.getAuthToken(),
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    user_id: userId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.openChat(data.chat.id);
            }
        } catch (error) {
            console.error('Error creating private chat:', error);
        }
    }

    async loadUsersForGroup() {
        try {
            const response = await fetch('/api/chat/users', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.renderUsersForGroup(data.users);
            }
        } catch (error) {
            console.error('Error loading users for group:', error);
        }
    }

    renderUsersForGroup(users) {
        const container = $('#group-members-list');
        let html = '';
        
        users.forEach(user => {
            html += `
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="${user.id}" id="user-${user.id}">
                    <label class="form-check-label" for="user-${user.id}">
                        ${user.name} (${user.email})
                    </label>
                </div>
            `;
        });

        container.html(html);
    }

    async createGroup() {
        const name = $('#group-name').val().trim();
        const description = $('#group-description').val().trim();
        const selectedUsers = [];

        $('#group-members-list input[type="checkbox"]:checked').each(function() {
            selectedUsers.push($(this).val());
        });

        if (!name || selectedUsers.length === 0) {
            alert(window.chatTranslations.group_name_required || 'Please provide a group name and select at least one member.');
            return;
        }

        try {
            const response = await fetch('/api/chat', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + this.getAuthToken(),
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    name: name,
                    description: description,
                    user_ids: selectedUsers
                })
            });

            const data = await response.json();
            
            if (data.success) {
                $('#createGroupModal').modal('hide');
                $('#group-name').val('');
                $('#group-description').val('');
                $('#group-members-list input[type="checkbox"]').prop('checked', false);
                this.openChat(data.chat.id);
            }
        } catch (error) {
            console.error('Error creating group:', error);
        }
    }

    async loadUnreadCount() {
        try {
            const response = await fetch('/api/chat/unread-count', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const data = await response.json();
            
            if (data.success) {
                const badge = $('#chat-notification-badge');
                if (data.unread_count > 0) {
                    badge.text(data.unread_count).show();
                } else {
                    badge.hide();
                }
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }

    filterChats(query) {
        $('.chat-item').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    }

    filterUsers(query) {
        $('.user-item').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(query));
        });
    }

    // Utility functions
    getAuthToken() {
        // Get authentication token from window object or localStorage
        return window.authToken || localStorage.getItem('auth_token');
    }

    getUserAvatar(users) {
        const otherUser = users.find(user => user.id != this.currentUserId);
        return otherUser?.image || '/images/logo.png';
    }

    getOtherUserName(users) {
        const otherUser = users.find(user => user.id != this.currentUserId);
        return otherUser?.name || (window.chatTranslations.unknown_user || 'Unknown User');
    }

    truncateMessage(message, length = 50) {
        return message.length > length ? message.substring(0, length) + '...' : message;
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diffInHours = (now - date) / (1000 * 60 * 60);

        if (diffInHours < 24) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        } else if (diffInHours < 48) {
            return window.chatTranslations.yesterday || 'Yesterday';
        } else {
            return date.toLocaleDateString();
        }
    }
}

// Initialize chat system when document is ready
$(document).ready(function() {
    if (typeof Echo !== 'undefined') {
        window.chatSystem = new ChatSystem();
    }
});
