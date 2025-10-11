// /**
//  * Chat System JavaScript
//  * Handles real-time chat functionality with Pusher and Echo
//  */

// class ChatSystem {
//     constructor() {
//         this.currentChatId = null;
//         this.currentUserId = window.currentUserId || null;
//         this.echo = null;
//         this.chatChannels = {};
//         this.init();
//     }

//     init() {
//         console.log('Initializing Chat System...');
//         this.initializePusher();
//         this.bindEvents();
//         this.loadUnreadCount();
//     }

//     initializePusher() {
//         // Initialize Pusher and Echo
//         if (typeof window.Echo !== 'undefined' && this.currentUserId) {
//             this.echo = window.Echo.private('user.' + this.currentUserId);
            
//             // Listen for new chats
//             this.echo.listen('.chat.created', (e) => {
//                 this.handleNewChat(e.chat);
//             });
//         }
//     }

//     bindEvents() {
//         // Load chats when modal is opened
//         $('#chatModal').on('shown.bs.modal', () => {
//             this.loadChats();
//         });

//         // Chat type tabs
//         $('#private-tab').on('click', () => {
//             this.loadChats('private');
//         });

//         $('#group-tab').on('click', () => {
//             this.loadChats('group');
//         });

//         // Send message button
//         $('#send-message-btn').on('click', () => {
//             this.sendMessage();
//         });

//         // Send message on Enter key
//         $('#message-input').on('keypress', (e) => {
//             if (e.which === 13 && !e.shiftKey) {
//                 e.preventDefault();
//                 this.sendMessage();
//             }
//         });

//         // Search functionality
//         $('#chat-search').on('input', (e) => {
//             const query = $(e.target).val().toLowerCase();
//             this.filterChats(query);
//         });
//     }

//     async loadChats(type = null) {
//         console.log('Loading chats...', type);
        
//         try {
//             const response = await fetch('/api/chat', {
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                 }
//             });

//             if (!response.ok) {
//                 throw new Error(`HTTP error! status: ${response.status}`);
//             }

//             const data = await response.json();
//             console.log('Chats response:', data);
            
//             // Handle both array response and object with success property
//             let chats = [];
//             if (Array.isArray(data)) {
//                 chats = data;
//             } else if (data.success && data.chats) {
//                 chats = data.chats;
//             } else if (data.data) {
//                 chats = data.data;
//             }
            
//             console.log('Processed chats:', chats);
            
//             // Filter chats by type
//             const privateChats = chats.filter(chat => chat.type === 'private');
//             const groupChats = chats.filter(chat => chat.type === 'group');
            
//             console.log('Private chats:', privateChats.length);
//             console.log('Group chats:', groupChats.length);
            
//             // Render appropriate chat list
//             this.renderChats(privateChats, '#private-chat-list');
//             this.renderChats(groupChats, '#group-chat-list');
            
//         } catch (error) {
//             console.error('Error loading chats:', error);
//             this.renderChats([], '#private-chat-list');
//             this.renderChats([], '#group-chat-list');
//         }
//     }

//     renderChats(chats, containerSelector) {
//         const chatList = $(containerSelector);
//         const chatType = containerSelector.includes('private') ? 'private' : 'group';
        
//         if (chats.length === 0) {
//             const icon = chatType === 'private' ? 'fa-user-friends' : 'fa-users';
//             const message = chatType === 'private' ? 'No private chats' : 'No group chats';
//             const buttonText = chatType === 'private' ? 'New Chat' : 'New Group';
            
//             chatList.html(`
//                 <div class="d-flex flex-column h-100 justify-content-center align-items-center p-4">
//                     <i class="fas ${icon} text-muted" style="font-size: 3rem;"></i>
//                     <div class="text-muted mt-3">${message}</div>
//                     <button class="btn btn-sm btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#newChatModal" data-chat-type="${chatType}">
//                         <i class="fas fa-plus me-1"></i> ${buttonText}
//                     </button>
//                 </div>
//             `);
//             return;
//         }

//         let html = '';
//         chats.forEach(chat => {
//             const latestMessage = chat.latest_message;
//             const unreadCount = chat.unread_count || 0;
//             const isGroup = chat.type === 'group';
            
//             html += `
//                 <div class="chat-item" data-chat-id="${chat.id}" data-chat-type="${chat.type}">
//                     <div class="d-flex align-items-center">
//                         <div class="position-relative me-3">
//                             ${isGroup ? 
//                                 `<div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
//                                     <i class="fas fa-users"></i>
//                                 </div>` :
//                                 `<img src="${this.getUserAvatar(chat.users)}" alt="" class="rounded-circle" style="width: 45px; height: 45px;">`
//                             }
//                             ${unreadCount > 0 ? `<span class="unread-badge">${unreadCount}</span>` : ''}
//                         </div>
//                         <div class="flex-grow-1 overflow-hidden">
//                             <div class="d-flex justify-content-between align-items-start">
//                                 <div class="flex-grow-1 overflow-hidden">
//                                     <h6 class="mb-1 text-truncate">${isGroup ? chat.name : this.getOtherUserName(chat.users)}</h6>
//                                     ${latestMessage ? 
//                                         `<div class="text-muted small text-truncate">${this.truncateMessage(latestMessage.message, 40)}</div>` :
//                                         `<div class="text-muted small">No messages yet</div>`
//                                     }
//                                 </div>
//                                 <div class="text-end ms-2" style="min-width: 60px;">
//                                     ${latestMessage ? 
//                                         `<small class="text-muted">${this.formatTime(latestMessage.created_at)}</small>` :
//                                         ''
//                                     }
//                                 </div>
//                             </div>
//                         </div>
//                     </div>
//                 </div>
//             `;
//         });

//         chatList.html(html);

//         // Bind chat item click events
//         chatList.find('.chat-item').on('click', (e) => {
//             const chatId = $(e.currentTarget).data('chat-id');
//             this.openChat(chatId);
//         });
//     }

//     async openChat(chatId) {
//         console.log('Opening chat:', chatId);
//         this.currentChatId = chatId;
        
//         // Mark active chat
//         $('.chat-item').removeClass('active');
//         $(`.chat-item[data-chat-id="${chatId}"]`).addClass('active');
        
//         try {
//             const response = await fetch(`/api/chat/${chatId}`, {
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                 }
//             });

//             if (!response.ok) {
//                 throw new Error(`HTTP error! status: ${response.status}`);
//             }

//             const data = await response.json();
//             console.log('Chat details response:', data);
            
//             // Handle different response structures
//             let chat, messages;
//             if (data.success) {
//                 chat = data.chat;
//                 messages = data.messages || [];
//             } else if (data.data) {
//                 chat = data.data.chat || data.data;
//                 messages = data.data.messages || [];
//             } else {
//                 chat = data;
//                 messages = [];
//             }
            
//             if (chat) {
//                 this.renderChatWindow(chat, messages);
                
//                 // Enable message input
//                 $('#message-input').prop('disabled', false).focus();
//                 $('#send-message-btn').prop('disabled', false);
                
//                 // Subscribe to chat channel for real-time updates
//                 this.subscribeToChat(chatId);
                
//                 // Update unread count
//                 this.loadUnreadCount();
//             }
//         } catch (error) {
//             console.error('Error opening chat:', error);
//             alert('Failed to open chat. Please try again.');
//         }
//     }

//     renderChatWindow(chat, messages) {
//         // Update chat header
//         const isGroup = chat.type === 'group';
//         const chatName = isGroup ? chat.name : this.getOtherUserName(chat.users);
//         const chatSubtitle = isGroup ? `${chat.users.length} members` : 'Online';
        
//         $('#chat-title').text(chatName);
//         $('#chat-subtitle').text(chatSubtitle);
        
//         // Update avatar
//         if (isGroup) {
//             $('#chat-avatar').replaceWith(`
//                 <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" 
//                      style="width: 45px; height: 45px;" id="chat-avatar">
//                     <i class="fas fa-users"></i>
//                 </div>
//             `);
//         } else {
//             const avatarUrl = this.getUserAvatar(chat.users);
//             if ($('#chat-avatar').is('img')) {
//                 $('#chat-avatar').attr('src', avatarUrl);
//             } else {
//                 $('#chat-avatar').replaceWith(`
//                     <img src="${avatarUrl}" class="rounded-circle" width="45" height="45" alt="Chat" id="chat-avatar">
//                 `);
//             }
//         }

//         // Render messages
//         this.renderMessages(messages);
//     }

//     renderMessages(messages) {
//         const container = $('#messages-container');
        
//         if (messages.length === 0) {
//             container.html(`
//                 <div class="text-center py-5">
//                     <i class="fas fa-comment text-muted" style="font-size: 3rem;"></i>
//                     <div class="text-muted mt-3">No messages yet. Start the conversation!</div>
//                 </div>
//             `);
//             return;
//         }

//         let html = '';
//         messages.forEach(message => {
//             const isOwnMessage = message.user_id == this.currentUserId;
//             html += this.renderMessage(message, isOwnMessage);
//         });

//         container.html(html);
//         this.scrollToBottom();
//     }

//     renderMessage(message, isOwnMessage) {
//         return `
//             <div class="message ${isOwnMessage ? 'sent' : 'received'}">
//                 <div class="message-bubble">
//                     ${this.escapeHtml(message.message)}
//                 </div>
//                 <div class="message-time">
//                     ${this.formatTime(message.created_at)}
//                     ${isOwnMessage && message.is_read ? '<i class="fas fa-check-double text-primary ms-1"></i>' : ''}
//                 </div>
//             </div>
//         `;
//     }

//     async sendMessage() {
//         const messageInput = $('#message-input');
//         const message = messageInput.val().trim();
        
//         if (!message || !this.currentChatId) return;

//         try {
//             const response = await fetch(`/api/chat/${this.currentChatId}/messages`, {
//                 method: 'POST',
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                 },
//                 body: JSON.stringify({
//                     message: message
//                 })
//             });

//             const data = await response.json();
            
//             if (data.success) {
//                 messageInput.val('');
//                 // Message will be added via real-time event or we can add it directly
//                 const isOwnMessage = true;
//                 const messageHtml = this.renderMessage(data.message, isOwnMessage);
//                 $('#messages-container').append(messageHtml);
//                 this.scrollToBottom();
                
//                 // Update chat list
//                 this.loadChats();
//             }
//         } catch (error) {
//             console.error('Error sending message:', error);
//             alert('Failed to send message. Please try again.');
//         }
//     }

//     subscribeToChat(chatId) {
//         if (!this.echo) return;

//         // Unsubscribe from previous chat if exists
//         if (this.chatChannels[this.currentChatId]) {
//             this.echo.leave(`chat.${this.currentChatId}`);
//             delete this.chatChannels[this.currentChatId];
//         }

//         // Subscribe to new chat
//         this.chatChannels[chatId] = this.echo.private(`chat.${chatId}`)
//             .listen('.message.sent', (e) => {
//                 this.handleNewMessage(e.message);
//             });
//     }

//     handleNewMessage(message) {
//         if (message.chat_id == this.currentChatId) {
//             // Add message to current chat
//             const isOwnMessage = message.user_id == this.currentUserId;
//             const messageHtml = this.renderMessage(message, isOwnMessage);
//             $('#messages-container').append(messageHtml);
//             this.scrollToBottom();
//         }

//         // Update chat list and unread count
//         this.loadChats();
//         this.loadUnreadCount();
//     }

//     handleNewChat(chat) {
//         // Reload chats when a new chat is created
//         this.loadChats();
//     }

//     async loadUnreadCount() {
//         try {
//             const response = await fetch('/api/chat/unread-count', {
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//                 }
//             });

//             const data = await response.json();
            
//             if (data.success) {
//                 const badge = $('#unread-count');
//                 if (data.unread_count > 0) {
//                     badge.text(data.unread_count).show();
//                 } else {
//                     badge.text('0');
//                 }
//             }
//         } catch (error) {
//             console.error('Error loading unread count:', error);
//         }
//     }

//     filterChats(query) {
//         $('.chat-item').each(function() {
//             const text = $(this).text().toLowerCase();
//             $(this).toggle(text.includes(query));
//         });
//     }

//     scrollToBottom() {
//         const container = $('#messages-container');
//         container.scrollTop(container[0].scrollHeight);
//     }

//     // Utility functions
//     getUserAvatar(users) {
//         const otherUser = users.find(user => user.id != this.currentUserId);
//         if (otherUser && otherUser.image) {
//             return otherUser.image;
//         }
//         const userName = otherUser ? otherUser.name : 'User';
//         return `https://ui-avatars.com/api/?name=${encodeURIComponent(userName)}&background=random`;
//     }

//     getOtherUserName(users) {
//         const otherUser = users.find(user => user.id != this.currentUserId);
//         return otherUser?.name || 'Unknown User';
//     }

//     truncateMessage(message, length = 50) {
//         return message.length > length ? message.substring(0, length) + '...' : message;
//     }

//     formatTime(timestamp) {
//         const date = new Date(timestamp);
//         const now = new Date();
//         const diffInHours = (now - date) / (1000 * 60 * 60);

//         if (diffInHours < 1) {
//             const minutes = Math.floor((now - date) / (1000 * 60));
//             return minutes === 0 ? 'Just now' : `${minutes}m ago`;
//         } else if (diffInHours < 24) {
//             return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
//         } else if (diffInHours < 48) {
//             return 'Yesterday';
//         } else if (diffInHours < 168) {
//             return date.toLocaleDateString([], { weekday: 'short' });
//         } else {
//             return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
//         }
//     }

//     escapeHtml(text) {
//         const map = {
//             '&': '&amp;',
//             '<': '&lt;',
//             '>': '&gt;',
//             '"': '&quot;',
//             "'": '&#039;'
//         };
//         return text.replace(/[&<>"']/g, m => map[m]);
//     }
// }

// // Initialize chat system when document is ready
// $(document).ready(function() {
//     console.log('Document ready, initializing chat system...');
//     window.chatSystem = new ChatSystem();
// });