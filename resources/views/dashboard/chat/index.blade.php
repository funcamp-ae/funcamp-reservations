@extends('dashboard.layouts.app')

@section('title', __('dashboard.chat'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="row g-0 h-100 position-relative">
                        <!-- Toggle Button (Visible on mobile) -->
                        <button class="btn btn-sm btn-primary d-md-none position-absolute" id="sidebarToggle" style="z-index: 50; @if(session('lang') == 'ar')right: 10px;@else left: 10px; @endif top: 10px;width: fit-content;">
                            <i class="fas fa-bars"></i>
                        </button>

                        <!-- Chat List Sidebar -->
                        <div class="col-md-4 border-end @if(session('theme') == 'dark') bg-dark @else bg-white @endif" id="chatSidebar" style="overflow-y: auto;">
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">
                                        <i class="fas fa-comments text-primary me-2"></i>
                                        {{ __('dashboard.messages') }}
                                    </h5>
                                    <div>
                                        <button class="btn btn-sm btn-primary me-2" id="newChatBtn">
                                            {{ __('dashboard.start_new_chat') }}
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary d-md-none" id="closeSidebar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" class="form-control" placeholder="{{ __('dashboard.search_chats') }}" id="chatSearch">
                                </div>
                            </div>
                            <div class="chat-list" id="chatList">
                                <div class="text-center py-4">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    <p class="mt-2 text-muted">{{ __('dashboard.loading_chats') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chat Content -->
                        <div class="col-md-8 d-flex flex-column" id="chatContent" style="height: calc(100vh - 60px);">
                        <!-- Chat Window -->
                            <div class="d-flex flex-column h-100">
                                <!-- Chat Header -->
                                <div class="border-bottom p-3" id="chatHeader" style="display: none;">
                                    <div class="d-flex align-items-center justify-content-center">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;" id="chatAvatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-0" id="chatTitle">{{ __('dashboard.select_chat') }}</h5>
                                        <small class="text-muted" id="chatSubtitle">{{ __('dashboard.start_conversation') }}</small>
                                    </div>
                                </div>
                                <!-- Group Settings Button (Only visible for group chats) -->
                                <div id="groupSettingsBtn" style="display: none;">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="#" id="manageMembersBtn"><i class="fas fa-users me-2"></i>Manage Members</a></li>
                                        <li><a class="dropdown-item" href="#" id="editGroupBtn"><i class="fas fa-edit me-2"></i>Edit Group</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" id="leaveGroupBtn"><i class="fas fa-sign-out-alt me-2"></i>Leave Group</a></li>
                                    </ul>
                                </div>
                            </div>                                    </div>
                                </div>
                                
                                <!-- Messages Area -->
                                <div class="flex-grow-1 p-3" id="messagesArea" style="overflow-y: auto; ">
                                    <div class="text-center py-5">
                                        <i class="fas fa-comment text-muted" style="font-size: 4rem;"></i>
                                        <h5 class="text-muted mt-3">{{ __('dashboard.welcome_to_chat') }}</h5>
                                        <p class="text-muted">{{ __('dashboard.select_chat_to_start') }}</p>
                                    </div>
                                </div>
                                
                                <!-- Message Input -->
                                <div class="border-top p-3 " id="messageInputArea" style="display: none;">
                                    <div class="d-flex align-items-end gap-2">
                                        <!-- File Upload Button -->
                                        <button class="btn btn-outline-secondary" id="uploadFileBtn" type="button" title="{{ __('dashboard.attach_file') }}">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                        
                                        <!-- Hidden file input for plupload -->
                                        <input type="file" id="fileInput" style="display: none;" accept="image/*,video/*">
                                        
                                        <!-- Message Input -->
                                        <div class="flex-grow-1">
                                            <input type="text" class="form-control" placeholder="{{ __('dashboard.type_message') }}..." id="messageInput">
                                            <!-- File preview area -->
                                            <div id="filePreview" class="mt-2" style="display: none;">
                                                <div class="card">
                                                    <div class="card-body p-2">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="d-flex align-items-center">
                                                                <div id="previewContent"></div>
                                                                <span id="fileName" class="ms-2 small"></span>
                                                            </div>
                                                            <button class="btn btn-sm btn-danger" id="cancelUpload" type="button">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </div>
                                                        <div class="progress mt-2" style="height: 5px;">
                                                            <div class="progress-bar" id="uploadProgress" role="progressbar" style="width: 0%"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Send Button -->
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

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('dashboard.start_new_chat') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ __('dashboard.chat_type') }}</label>
                    <select class="form-select" id="chatType">
                        <option value="private">{{ __('dashboard.private_chat') }}</option>
                        <option value="group">{{ __('dashboard.group_chat') }}</option>
                    </select>
                </div>
                
                <!-- Private Chat Section -->
                <div id="privateChatSection">
                    <div class="mb-3">
                        <label class="form-label">{{ __('dashboard.select_user') }}</label>
                        <select class="form-select" id="selectedUser">
                            <option value="">{{ __('dashboard.choose_user') }}</option>
                        </select>
                    </div>
                </div>
                
                <!-- Group Chat Section -->
                <div id="groupChatSection" style="display: none;">
                    <div class="mb-3">
                        <label class="form-label">{{ __('dashboard.group_name') }}</label>
                        <input type="text" class="form-control" id="groupName" placeholder="{{ __('dashboard.enter_group_name') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('dashboard.group_description') }}</label>
                        <textarea class="form-control" id="groupDescription" rows="2" placeholder="{{ __('dashboard.enter_group_description') }}"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">{{ __('dashboard.select_members') }}</label>
                        <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;" id="groupMembers">
                            <!-- Group members will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('dashboard.cancel') }}</button>
                <button type="button" class="btn btn-primary" id="createChatBtn">{{ __('dashboard.create_chat') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Members Modal -->
<div class="modal fade" id="manageMembersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Manage Members</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add Members Section -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Add Members</h6>
                    <div class="input-group">
                        <select class="form-select" id="newMemberSelect">
                            <option value="">Select user to add...</option>
                        </select>
                        <button class="btn btn-primary" type="button" id="addMemberBtn">
                            <i class="fas fa-user-plus"></i> Add
                        </button>
                    </div>
                </div>

                <!-- Current Members Section -->
                <div>
                    <h6 class="text-muted mb-3">Current Members</h6>
                    <div class="list-group" id="membersList">
                        <!-- Members will be loaded here -->
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Group Modal -->
<div class="modal fade" id="editGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Group Name</label>
                    <input type="text" class="form-control" id="editGroupName" placeholder="Enter group name">
                </div>
                <div class="mb-3">
                    <label class="form-label">Group Description</label>
                    <textarea class="form-control" id="editGroupDescription" rows="3" placeholder="Enter group description"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveGroupBtn">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Plupload CDN -->
<script src="https://cdn.jsdelivr.net/npm/plupload@2.3.9/js/plupload.full.min.js"></script>

<script>
class ChatSystem {
    constructor() {
        this.currentChatId = null;
        this.currentUserId = {{ auth()->id() }};
        this.chats = [];
        this.users = [];
        this.activeChannels = new Map(); // Track active Echo channels
        this.uploadedFile = null;
        this.uploader = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadUsers();
        this.loadChats();
        this.setupGlobalMessageListener();
        this.setupFileUpload();
        if (this.currentChatId) {
            this.setupChatChannel(this.currentChatId);
        }
    }

    // Calculate total unread messages across all chats
    getTotalUnreadCount() {
        return this.chats.reduce((total, chat) => {
            return total + (chat.unread_count || 0);
        }, 0);
    }

    // Update the unread badge in the header
    updateUnreadBadge(totalUnread) {
        const chatButton = document.querySelector('a[title="Open Chat"]');
        
        if (!chatButton) return;

        let badge = chatButton.querySelector('.unread-badge');
        
        // Create badge if it doesn't exist
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'unread-badge position-absolute end-0  unread-badge badge rounded-pill bg-danger';
            badge.style.fontSize = '0.6rem';
            badge.style.padding = '0.25em 0.4em';
            badge.style.display = 'none';
            badge.style.right = '30px !important';
            chatButton.appendChild(badge);
        }

        // Update badge content and visibility
        if (totalUnread > 0) {
            badge.textContent = totalUnread > 99 ? '99+' : totalUnread;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    // Set up global listener for all user's messages and unread counts
    setupGlobalMessageListener() {
        if (typeof Echo === 'undefined') return;

        // Channel for unread count updates
        const unreadCountChannel = `unread_message_count_for_user.${this.currentUserId}`;
        
        // Channel for new messages
        const messageChannel = `user.${this.currentUserId}`;

        // Leave previous channels if they exist
        if (this.unreadCountChannel) {
            Echo.leave(this.unreadCountChannel);
        }
        if (this.messageChannel) {
            Echo.leave(this.messageChannel);
        }

        // Store channel references
        this.unreadCountChannel = unreadCountChannel;
        this.messageChannel = messageChannel;

        // Listen for unread count updates
        Echo.private(unreadCountChannel)
            .listen('.unread_message_count_for_user.updated', (e) => {
                this.updateUnreadBadge(e.totalUnread);
                
                // Update the chat in the list
                const chat = this.chats.find(c => c.id == e.chatId);
                if (chat) {
                    // Update unread count for this chat
                    chat.unread_count = e.unreadCount;
                    chat.total_unread = e.totalUnread;
                    // this.renderChats();
                    this.updateChatInList(e.chatId, e.message , true);
                } else {
                    // Chat not in list - user might have been added to a new group
                    // Reload all chats to include the new one
                    console.log('Received message for unknown chat, reloading chats...');
                    this.loadChats();
                }
                
                // If this is for the current chat and we have a message, update read status
                if (e.chatId == this.currentChatId && e.message && this.currentMessages) {
                    // Find the message and update its read status
                    const message = this.currentMessages.find(m => m.id === e.message.id);
                    if (message && message.user_id == this.currentUserId) {
                        message.is_read = true;
                        message.read_at = e.message.read_at;
                        
                        // Update just the status icon for this message without re-rendering everything
                        this.updateMessageReadStatusIcon(e.message.id);
                    }
                }
            })
    }

    // Update read status icon for a specific message
    updateMessageReadStatusIcon(messageId) {
        const messageElement = document.querySelector(`[data-message-id="${messageId}"]`);
        if (messageElement) {
            const metaElement = messageElement.querySelector('.message-meta');
            if (metaElement) {
                // Find and update the status icon
                const oldIcon = metaElement.querySelector('.fa-check, .fa-check-double');
                if (oldIcon) {
                    // Replace with double checkmark
                    oldIcon.className = 'fas fa-check-double text-primary ms-1';
                    oldIcon.setAttribute('title', 'Read');
                }
            }
        }
    }

    setupChatListener() {
        // Check if we have a chat ID
        if (!this.currentChatId) {
            return;
        }

        // Unsubscribe from any existing channel first
        if (this.currentChannel) {
            Echo.leave(this.currentChannel);
        }

        // Store the current channel name
        this.currentChannel = `chat.${this.currentChatId}`;                
        // Subscribe to the private channel
        Echo.private(this.currentChannel)
            .listen('.message.sent', (e) => {
            this.handleNewMessage(e);
        })

}
    setupChatChannel(chatId) {
        // Unsubscribe from previous chat channel if exists
        if (this.activeChannels.has('chat')) {
            const oldChannel = this.activeChannels.get('chat');
            Echo.leave(oldChannel);
            this.activeChannels.delete('chat');
        }

        // Subscribe to the new chat channel
        if (typeof Echo !== 'undefined') {
            const channelName = `chat.${chatId}`;
            Echo.private(channelName)
            .listen('.message.sent', (e) => {
              this.handleChatMessage(e);
                // Update the chat in the list with the new message and increment unread count
              const chat = this.chats.find(c => c.id == e.chat_id);
              if (chat) {
                chat.latest_message = e.message;
                if (this.currentChatId != e.chat_id && e.message.user_id != this.currentUserId) {
                  chat.unread_count = (chat.unread_count || 0) + 1;
                }
                this.renderChats();
              }
            })
            .listen('.message.read', (e) => {
            this.updateMessageReadStatus(e);
            });           
            this.activeChannels.set('chat', channelName);
        
    }
}

    handleNewMessage(event) {
        const { fullMessageDetails , chat_id, user } = event;
        let message = fullMessageDetails.message;
        // Update chat list - move chat to top and update latest message
        this.updateChatInList(chat_id, message);
        
        // If the message is for the currently open chat and not sent by current user
        if (this.currentChatId == chat_id && message.user_id != this.currentUserId) {
            // Add message to current view
            if (!this.currentMessages) {
                this.currentMessages = [];
            }
            this.currentMessages.push(message);
            this.renderMessages(this.currentMessages);
            
            // Mark message as read
            this.markMessageAsRead(message.id);
        } else if (message.user_id != this.currentUserId) {
            // Show notification for messages in other chats
            this.showNotification(user.name, fullMessageDetails);
        }
    }

    handleChatMessage(event) {
        const { message, chat_id, user } = event;
        
        // Only handle if it's the current chat and not from current user
        if (this.currentChatId == chat_id && message.user_id != this.currentUserId) {
            if (!this.currentMessages) {
                this.currentMessages = [];
            }
            this.currentMessages.push(message);
            this.renderMessages(this.currentMessages);
            
            // Mark as read
            this.markMessageAsRead(message.id);
        }
    }

    updateMessageReadStatus(event) {
        const { message_id, user_id } = event;
        
        if (this.currentMessages) {
            const message = this.currentMessages.find(m => m.id === message_id);
            if (message) {
                message.is_read = true;
                message.read_at = new Date().toISOString();
                this.renderMessages(this.currentMessages);
            }
        }
    }

    updateChatInList(chatId, latestMessage , isFromSetupGlobalMessageListener = false) {
        // Find and update the chat in the list
        const chatIndex = this.chats.findIndex(c => c.id == chatId);
        if (chatIndex !== -1) {
            this.chats[chatIndex].latest_message = latestMessage;
            
            // Increment unread count if not current chat
            if(!isFromSetupGlobalMessageListener){
                if (this.currentChatId != chatId && latestMessage.user_id != this.currentUserId) {
                    this.chats[chatIndex].unread_count = (this.chats[chatIndex].unread_count || 0) + 1;
                }
            }
            
            // Move chat to top
            const chat = this.chats.splice(chatIndex, 1)[0];
            this.chats.unshift(chat);
            
            // Re-render chat list
            this.renderChats();
        } else {
            // If chat not in list, reload all chats
            this.loadChats();
        }
    }

    async markMessageAsRead(messageId) {
        try {
            await fetch(`/api/chat/messages/${messageId}/mark-message-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
        } catch (error) {
            // Error handling without console.log
        }
    }

    showNotification(senderName, message) {
        // Check if browser supports notifications
        if ('Notification' in window && Notification.permission === 'granted') {
            let notificationBody = '';
            
            // Handle different message types
            if (message.file_url && (message.type === 'image' || message.file_type === 'image')) {
                notificationBody = message.message ? message.message : '📷 Sent an image';
            } else if (message.file_url && (message.type === 'video' || message.file_type === 'video')) {
                notificationBody = message.message ? message.message : '🎥 Sent a video';
            } else if (message.file_url && (message.type === 'file' || message.file_type === 'file')) {
                notificationBody = message.message ? message.message : '📎 Sent a file';
            } else {
                notificationBody = this.truncateMessage(message.message || 'New message', 50);
            }
            
            new Notification(`New message from ${senderName}`, {
                body: notificationBody,
                icon: '/path/to/notification-icon.png'
            });
        }
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

        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }

        // Group management events
        document.getElementById('manageMembersBtn').addEventListener('click', (e) => {
            e.preventDefault();
            this.openManageMembersModal();
        });

        document.getElementById('editGroupBtn').addEventListener('click', (e) => {
            e.preventDefault();
            this.openEditGroupModal();
        });

        document.getElementById('leaveGroupBtn').addEventListener('click', (e) => {
            e.preventDefault();
            this.leaveGroup();
        });

        document.getElementById('addMemberBtn').addEventListener('click', () => {
            this.addMemberToGroup();
        });

        document.getElementById('saveGroupBtn').addEventListener('click', () => {
            this.updateGroupInfo();
        });
    }

    async loadChats() {
        try {
            const response = await fetch('/api/chat', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            const data = await response.json();

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
        select.innerHTML = '<option value="">{{__("dashboard.choose_user")}}</option>';
        
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
                    <h6 class="text-muted mt-3">@lang('dashboard.no_chats')</h6>
                    <p class="text-muted">@lang('dashboard.start_conversation')</p>
                    <button class="btn btn-primary" onclick="document.getElementById('newChatBtn').click()">
                        <i class="fas fa-plus me-1"></i> @lang('dashboard.start_new_chat')
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
                                        ${latestMessage ? this.getMessagePreview(latestMessage) : '@lang('dashboard.no_messages_yet')'}
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
        });
    }

    async openChat(chatId) {
        try {
            this.currentChatId = chatId;
            
            // Clear any pending file upload when switching chats
            this.clearFileUpload();
            
            // Setup real-time channel for this chat
            this.setupChatChannel(chatId);
            
            // Mark as active and reset unread count
            document.querySelectorAll('.chat-item').forEach(item => {
                item.style.backgroundColor = '';
            });
            
            const chatItem = document.querySelector(`[data-chat-id="${chatId}"]`);
            if (chatItem) {
                chatItem.style.backgroundColor = 'rgb(231 231 231 / 32%)';
                
                // Reset unread count for this chat
                const chat = this.chats.find(c => c.id == chatId);
                if (chat && chat.unread_count > 0) {
                    chat.unread_count = 0;
                    this.updateUnreadBadge();
                    this.renderChats();
                }
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
                
                // Reset unread count for this chat
                const chat = this.chats.find(c => c.id == chatId);
                if (chat) {
                    chat.unread_count = 0;
                    this.renderChats();
                }
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
        document.getElementById('chatHeader').style.display = 'block';
        document.getElementById('messageInputArea').style.display = 'block';
        
        // Show/hide group settings button
        const groupSettingsBtn = document.getElementById('groupSettingsBtn');
        if (groupSettingsBtn) {
            groupSettingsBtn.style.display = isGroup ? 'block' : 'none';
        }
        
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

    displayChatHeader(chat) {
        const isGroup = chat.type === 'group';
        const chatName = isGroup ? chat.name : this.getOtherUserName(chat.users);
        
        // Update header
        document.getElementById('chatTitle').textContent = chatName;
        
        // Update subtitle
        const subtitle = document.getElementById('chatSubtitle');
        if (isGroup) {
            const memberCount = chat.members ? chat.members.length : 0;
            subtitle.textContent = `${memberCount} members`;
        } else {
            subtitle.textContent = 'Private chat';
        }
        
        // Show/hide group settings button
        const groupSettingsBtn = document.getElementById('groupSettingsBtn');
        if (groupSettingsBtn) {
            groupSettingsBtn.style.display = isGroup ? 'block' : 'none';
        }
        
        // Update avatar
        const avatar = document.getElementById('chatAvatar');
        if (isGroup) {
            avatar.innerHTML = '<i class="fas fa-users"></i>';
            avatar.className = 'bg-primary text-white rounded-circle d-flex align-items-center justify-content-center';
        } else {
            avatar.innerHTML = '<i class="fas fa-user"></i>';
            avatar.className = 'bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center';
        }
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
            
            // Determine message status icon
            let statusIcon = '';
            if (isOwn) {
                if (message.upload_failed) {
                    statusIcon = '<i class="fas fa-exclamation-circle text-danger ms-1" title="Failed to send"></i>';
                } else if (message.is_read) {
                    // Message has been read - show double checkmark in blue/primary color
                    statusIcon = '<i class="fas fa-check-double text-primary ms-1" title="Read"></i>';
                } else {
                    // Message sent but not read - show single checkmark in gray
                    statusIcon = '<i class="fas fa-check text-muted ms-1" title="Sent"></i>';
                }
            }
            
            // Render file content
            let fileContent = '';
            if (message.file_url && (message.type === 'image' || message.file_type === 'image')) {
                fileContent = `
                    <div class="mb-2">
                        <img src="${message.file_url}" style="max-width: 300px; max-height: 300px; border-radius: 8px; cursor: pointer;" 
                             onclick="window.open('${message.file_url}', '_blank')" 
                             alt="Image" loading="lazy">
                    </div>
                `;
            } else if (message.file_url && (message.type === 'video' || message.file_type === 'video')) {
                fileContent = `
                    <div class="mb-2">
                        <video controls style="max-width: 300px; max-height: 300px; border-radius: 8px;">
                            <source src="${message.file_url}" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                `;
            }
            
            html += `
                <div class="message mb-3 d-flex ${isOwn ? 'justify-content-start' : 'justify-content-end'}" data-message-id="${message.id}">
                    <div class="message-content ${isOwn ? 'text-end' : 'text-start'}" style="max-width: 70%;">
                        <div class="d-inline-block p-3 rounded ${isOwn ? 'bg-primary text-white' : 'rounded btn-secondary shadow-sm'}" style="max-width: fit-content;">
                            ${fileContent}
                            ${message.message ? this.escapeHtml(message.message) : ''}
                        </div>
                        <div class="small text-muted mt-1 message-meta">
                            ${this.formatTime(message.created_at)}
                            ${statusIcon}
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
        
        // Check if we have a file or message
        if (!message && !this.uploadedFile) return;
        if (!this.currentChatId) return;

        // If file is being uploaded, send file message
        if (this.uploadedFile) {
            await this.sendFileMessage(message);
            return;
        }
        
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
                
                // Update chat list
                this.updateChatInList(this.currentChatId, data.message);
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

    // Setup file upload with Plupload
    setupFileUpload() {
        const self = this;
        const filePreview = document.getElementById('filePreview');
        const cancelUploadBtn = document.getElementById('cancelUpload');
        const previewContent = document.getElementById('previewContent');
        const fileName = document.getElementById('fileName');
        const progressBar = document.getElementById('uploadProgress');
        
        // Initialize Plupload
        this.uploader = new plupload.Uploader({
            runtimes: 'html5',
            browse_button: 'uploadFileBtn',
            url: '/api/chat/upload',
            chunk_size: '512kb', // Smaller chunks for better reliability
            max_retries: 5, // More retries
            multipart: true,
            multipart_params: {
                '_token': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'upload_id': '' // Will be set dynamically
            },
            filters: {
                mime_types: [
                    {title: "Image files", extensions: "jpg,jpeg,png,gif,webp"},
                    {title: "Video files", extensions: "mp4,mov,avi,webm"}
                ],
                max_file_size: '1024mb'
            },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            http_method: 'POST',
            send_chunk_number: true,
            init: {
                FilesAdded: function(up, files) {
                    const file = files[0];
                    
                    // Generate unique upload ID for this session
                    const uploadId = 'upload_' + new Date().getTime() + '_' + Math.random().toString(36).substr(2, 9);
                    up.settings.multipart_params.upload_id = uploadId;
                    
                    fileName.textContent = file.name;
                    filePreview.style.display = 'block';
                    
                    // Show preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (file.type.startsWith('image/')) {
                            previewContent.innerHTML = `<img src="${e.target.result}" style="max-width: 50px; max-height: 50px; border-radius: 4px;">`;
                        } else if (file.type.startsWith('video/')) {
                            previewContent.innerHTML = `<i class="fas fa-video fa-2x text-primary"></i>`;
                        }
                    };
                    reader.readAsDataURL(file.getNative());
                    
                    // Start upload automatically
                    up.start();
                },
                
                UploadProgress: function(up, file) {
                    progressBar.style.width = file.percent + '%';
                    // Show chunk progress in file name
                    if (file.total && file.loaded) {
                        const mbLoaded = (file.loaded / (1024 * 1024)).toFixed(2);
                        const mbTotal = (file.size / (1024 * 1024)).toFixed(2);
                        fileName.textContent = `${file.name} (${mbLoaded}MB / ${mbTotal}MB)`;
                    }
                },
                
                ChunkUploaded: function(up, file, info) {
                    // Log successful chunk uploads
                    const response = JSON.parse(info.response);
                    if (!response.success) {
                        console.error('Chunk upload failed:', response);
                    }
                },
                
                FileUploaded: function(up, file, response) {
                    try {
                        const result = JSON.parse(response.response);
                        
                        // Check if upload is complete (has file data)
                        if (result.success && result.file_url) {
                            self.uploadedFile = {
                                file_path: result.file_path,
                                file_url: result.file_url,
                                file_type: result.file_type,
                                preview: previewContent.innerHTML
                            };
                            progressBar.classList.add('bg-success');
                            fileName.textContent = file.name + ' ✓';
                        } else if (result.error) {
                            throw new Error(result.error.message || 'Upload failed');
                        }
                    } catch (error) {
                        console.error('Upload processing error:', error);
                        alert('Upload failed: ' + error.message);
                        filePreview.style.display = 'none';
                        self.uploader.splice();
                    }
                },
                
                Error: function(up, err) {
                    console.error('Plupload error:', err);
                    let errorMsg = 'Upload error: ' + err.message;
                    
                    // Provide more specific error messages
                    if (err.code === plupload.HTTP_ERROR) {
                        errorMsg = 'Network error: Connection timed out or server unreachable. Please check your connection and try again.';
                    } else if (err.code === plupload.FILE_SIZE_ERROR) {
                        errorMsg = 'File is too large. Maximum size is 1GB.';
                    } else if (err.code === plupload.FILE_EXTENSION_ERROR) {
                        errorMsg = 'File type not allowed. Only images and videos are supported.';
                    }
                    
                    alert(errorMsg);
                    filePreview.style.display = 'none';
                    self.uploader.splice();
                }
            }
        });
        
        this.uploader.init();
        
        // Cancel upload
        cancelUploadBtn.addEventListener('click', () => {
            this.uploader.splice();
            this.uploadedFile = null;
            filePreview.style.display = 'none';
            progressBar.style.width = '0%';
            progressBar.classList.remove('bg-success');
        });
    }

    // Send file message
    async sendFileMessage(caption = '') {
        if (!this.uploadedFile || !this.currentChatId) return;
        
        const input = document.getElementById('messageInput');
        const filePreview = document.getElementById('filePreview');
        const progressBar = document.getElementById('uploadProgress');
        
        // Create temporary message with file preview
        const tempMessage = {
            id: 'temp_' + Date.now(),
            user_id: this.currentUserId,
            message: caption,
            type: this.uploadedFile.file_type,
            file_url: this.uploadedFile.file_url,
            file_type: this.uploadedFile.file_type,
            created_at: new Date().toISOString(),
            is_read: false,
            is_temp: true
        };

        // Add and display immediately
        if (!this.currentMessages) {
            this.currentMessages = [];
        }
        this.currentMessages.push(tempMessage);
        this.renderMessages(this.currentMessages);

        // Clear input and file
        input.value = '';
        filePreview.style.display = 'none';
        progressBar.style.width = '0%';
        progressBar.classList.remove('bg-success');
        
        const uploadData = {...this.uploadedFile};
        this.uploadedFile = null;

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}/file-message`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    file_path: uploadData.file_path,
                    file_url: uploadData.file_url,
                    file_type: uploadData.file_type,
                    message: caption
                })
            });

            const data = await response.json();
            
            if (data.success) {
                // Replace temp message with real message
                this.currentMessages = this.currentMessages.filter(msg => !msg.is_temp);
                this.currentMessages.push(data.message);
                this.renderMessages(this.currentMessages);
                this.updateChatInList(this.currentChatId, data.message);
            } else {
                // Show error on temp message
                this.currentMessages = this.currentMessages.filter(msg => !msg.is_temp);
                tempMessage.upload_failed = true;
                this.currentMessages.push(tempMessage);
                this.renderMessages(this.currentMessages);
                alert('Failed to send file: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error sending file:', error);
            // Show error
            this.currentMessages = this.currentMessages.filter(msg => !msg.is_temp);
            tempMessage.upload_failed = true;
            this.currentMessages.push(tempMessage);
            this.renderMessages(this.currentMessages);
            alert('Failed to send file: ' + error.message);
        }
    }

    clearFileUpload() {
        // Clear uploaded file data
        this.uploadedFile = null;
        
        // Reset file preview UI
        const filePreview = document.getElementById('filePreview');
        const progressBar = document.getElementById('uploadProgress');
        const previewContent = document.getElementById('previewContent');
        const fileName = document.getElementById('fileName');
        
        if (filePreview) {
            filePreview.style.display = 'none';
        }
        
        if (progressBar) {
            progressBar.style.width = '0%';
            progressBar.classList.remove('bg-success');
        }
        
        if (previewContent) {
            previewContent.innerHTML = '';
        }
        
        if (fileName) {
            fileName.textContent = '';
        }
        
        // Reset file input
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.value = '';
        }
        
        // Stop any ongoing upload
        if (this.uploader) {
            this.uploader.splice();
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

    getMessagePreview(message) {
        // Handle file messages with special preview text
        if (message.file_url && (message.type === 'image' || message.file_type === 'image')) {
            return message.message ? this.truncateMessage(message.message, 40) : '📷 Image';
        } else if (message.file_url && (message.type === 'video' || message.file_type === 'video')) {
            return message.message ? this.truncateMessage(message.message, 40) : '🎥 Video';
        } else if (message.file_url && (message.type === 'file' || message.file_type === 'file')) {
            return message.message ? this.truncateMessage(message.message, 40) : '📎 File';
        }
        
        // Regular text message
        return this.truncateMessage(message.message || 'Message', 40);
    }

    // Group Management Methods
    async openManageMembersModal() {
        if (!this.currentChatId) return;

        const chat = this.chats.find(c => c.id === this.currentChatId);
        if (!chat || !chat.is_group) return;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('manageMembersModal'));
        modal.show();

        // Load available users for adding
        await this.loadAvailableUsersForGroup();
        
        // Load current members
        await this.loadGroupMembers();
    }

    async loadAvailableUsersForGroup() {
        try {
            // First, get all users
            const usersResponse = await fetch('/api/chat/users', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            // Then, get current chat members
            const chatResponse = await fetch(`/api/chat/${this.currentChatId}`, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (usersResponse.ok && chatResponse.ok) {
                const users = await usersResponse.json();
                const chatData = await chatResponse.json();
                const members = chatData.chat?.members || chatData.members || [];
                
                const select = document.getElementById('newMemberSelect');
                select.innerHTML = '<option value="">Select user to add...</option>';
                
                // Get member IDs
                const memberIds = members.map(m => m.id);
                
                // Filter out current user and existing members
                users.forEach(user => {
                    if (user.id !== this.currentUserId && !memberIds.includes(user.id)) {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        select.appendChild(option);
                    }
                });
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }

    async loadGroupMembers() {
        if (!this.currentChatId) return;

        const membersList = document.getElementById('membersList');
        membersList.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div></div>';

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}`, {
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                // Handle both response structures
                const members = data.chat?.members || data.members || [];
                this.renderMembersList(members);
            }
        } catch (error) {
            console.error('Error loading members:', error);
            membersList.innerHTML = '<div class="alert alert-danger">Failed to load members</div>';
        }
    }

    renderMembersList(members) {
        const membersList = document.getElementById('membersList');
        const chat = this.chats.find(c => c.id === this.currentChatId);
        const isCreator = chat && chat.created_by === this.currentUserId;
        const currentMember = members.find(m => m.id === this.currentUserId);
        const isAdmin = currentMember && currentMember.pivot && currentMember.pivot.is_admin;

        if (!members || members.length === 0) {
            membersList.innerHTML = '<div class="text-center text-muted py-3">No members</div>';
            return;
        }

        membersList.innerHTML = members.map(member => {
            const memberIsAdmin = member.pivot && member.pivot.is_admin;
            const canManage = isCreator || isAdmin;
            const canRemove = canManage && member.id !== this.currentUserId && member.id !== chat.created_by;
            const canPromote = isCreator && !memberIsAdmin && member.id !== this.currentUserId;
            const canDemote = isCreator && memberIsAdmin && member.id !== chat.created_by;

            return `
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                             style="width: 40px; height: 40px; font-size: 0.9rem;">
                            ${member.name.charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <div class="fw-bold">
                                ${member.name}
                                ${member.id === chat.created_by ? '<span class="badge bg-primary ms-2">Creator</span>' : ''}
                                ${memberIsAdmin ? '<span class="badge bg-success ms-2">Admin</span>' : ''}
                                ${member.id === this.currentUserId ? '<span class="badge bg-info ms-2">You</span>' : ''}
                            </div>
                            <small class="text-muted">${member.email || ''}</small>
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        ${canPromote ? `<button class="btn btn-outline-success btn-sm" onclick="chatSystem.promoteToAdmin(${member.id})" title="Promote to Admin">
                            <i class="fas fa-arrow-up"></i>
                        </button>` : ''}
                        ${canDemote ? `<button class="btn btn-outline-warning btn-sm" onclick="chatSystem.demoteFromAdmin(${member.id})" title="Demote from Admin">
                            <i class="fas fa-arrow-down"></i>
                        </button>` : ''}
                        ${canRemove ? `<button class="btn btn-outline-danger btn-sm" onclick="chatSystem.removeMemberFromGroup(${member.id})" title="Remove">
                            <i class="fas fa-user-times"></i>
                        </button>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    async addMemberToGroup() {
        const userId = document.getElementById('newMemberSelect').value;
        if (!userId || !this.currentChatId) return;

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}/members`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ user_ids: [userId] })
            });

            if (response.ok) {
                this.showToast('Member added successfully', 'success');
                document.getElementById('newMemberSelect').value = '';
                await this.loadGroupMembers();
                await this.loadAvailableUsersForGroup();
                await this.loadChats(); // Refresh chat list
            } else {
                const error = await response.json();
                this.showToast(error.message || 'Failed to add member', 'error');
            }
        } catch (error) {
            console.error('Error adding member:', error);
            this.showToast('Failed to add member', 'error');
        }
    }

    async removeMemberFromGroup(userId) {
        if (!confirm('Are you sure you want to remove this member?')) return;

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}/members/${userId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                this.showToast('Member removed successfully', 'success');
                await this.loadGroupMembers();
                await this.loadAvailableUsersForGroup();
                await this.loadChats(); // Refresh chat list
            } else {
                const error = await response.json();
                this.showToast(error.message || 'Failed to remove member', 'error');
            }
        } catch (error) {
            console.error('Error removing member:', error);
            this.showToast('Failed to remove member', 'error');
        }
    }

    async promoteToAdmin(userId) {
        try {
            const response = await fetch(`/api/chat/${this.currentChatId}/members/${userId}/promote`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                this.showToast('Member promoted to admin', 'success');
                await this.loadGroupMembers();
            } else {
                const error = await response.json();
                this.showToast(error.message || 'Failed to promote member', 'error');
            }
        } catch (error) {
            console.error('Error promoting member:', error);
            this.showToast('Failed to promote member', 'error');
        }
    }

    async demoteFromAdmin(userId) {
        if (!confirm('Are you sure you want to remove admin privileges?')) return;

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}/members/${userId}/demote`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                this.showToast('Admin privileges removed', 'success');
                await this.loadGroupMembers();
            } else {
                const error = await response.json();
                this.showToast(error.message || 'Failed to demote member', 'error');
            }
        } catch (error) {
            console.error('Error demoting member:', error);
            this.showToast('Failed to demote member', 'error');
        }
    }

    async openEditGroupModal() {
        if (!this.currentChatId) return;

        const chat = this.chats.find(c => c.id === this.currentChatId);
        if (!chat || !chat.is_group) return;

        // Populate form with current values
        document.getElementById('editGroupName').value = chat.name || '';
        document.getElementById('editGroupDescription').value = chat.description || '';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('editGroupModal'));
        modal.show();
    }

    async updateGroupInfo() {
        const name = document.getElementById('editGroupName').value.trim();
        const description = document.getElementById('editGroupDescription').value.trim();

        if (!name) {
            this.showToast('Group name is required', 'error');
            return;
        }

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name, description })
            });

            if (response.ok) {
                this.showToast('Group updated successfully', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editGroupModal')).hide();
                await this.loadChats(); // Refresh chat list
                
                // Update current chat header
                const chat = this.chats.find(c => c.id === this.currentChatId);
                if (chat) {
                    this.displayChatHeader(chat);
                }
            } else {
                const error = await response.json();
                this.showToast(error.message || 'Failed to update group', 'error');
            }
        } catch (error) {
            console.error('Error updating group:', error);
            this.showToast('Failed to update group', 'error');
        }
    }

    async leaveGroup() {
        if (!confirm('Are you sure you want to leave this group?')) return;

        try {
            const response = await fetch(`/api/chat/${this.currentChatId}/members/${this.currentUserId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                this.showToast('You have left the group', 'success');
                this.currentChatId = null;
                await this.loadChats();
                
                // Clear chat area
                document.getElementById('messageInputArea').style.display = 'none';
                document.getElementById('messagesArea').innerHTML = `
                    <div class="text-center py-5">
                        <i class="fas fa-comment text-muted" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">{{ __('dashboard.welcome_to_chat') }}</h5>
                        <p class="text-muted">{{ __('dashboard.select_chat_to_start') }}</p>
                    </div>
                `;
            } else {
                const error = await response.json();
                this.showToast(error.message || 'Failed to leave group', 'error');
            }
        } catch (error) {
            console.error('Error leaving group:', error);
            this.showToast('Failed to leave group', 'error');
        }
    }

    showToast(message, type = 'info') {
        // Use browser notification if available, otherwise alert
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Chat', { body: message });
        } else {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : 'success'} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 250px;';
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    window.chatSystem = new ChatSystem();
    
    // Toggle sidebar on mobile
    const sidebar = document.getElementById('chatSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const closeSidebar = document.getElementById('closeSidebar');
    const chatContent = document.getElementById('chatContent');
    
    // Toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('show-sidebar');
        chatContent.classList.toggle('hide-content');
    }
    
    // Event listeners
    sidebarToggle.addEventListener('click', toggleSidebar);
    closeSidebar.addEventListener('click', toggleSidebar);
    
    // Close sidebar when clicking on a chat item on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 768 && 
            e.target.closest('.chat-item') && 
            !e.target.closest('#chatSidebar > .p-3')) {
            toggleSidebar();
        }
    });
});
</script>
<style>
    @media (max-width: 767.98px) {
        #chatSidebar {
            position: fixed;
            top: 0;
            @if(session('lang') == 'ar')
            right: 0;
            transform: translateX(100%);
            @else
            left: 0;
            transform: translateX(-100%);
            @endif
            bottom: 0;
            z-index: 100;
            width: 280px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease-in-out;
        }
        
        #chatSidebar.show-sidebar {
            transform: translateX(0) !important;
        }
        
        #chatContent {
            width: 100%;
            display: none;
        }
        
        #chatContent.hide-content {
            display: flex !important;
        }
    }
    
    @media (min-width: 768px) {
        #chatSidebar {
            transform: none !important;
        }
        
        #chatContent {
            display: flex !important;
        }
    }
</style>
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
