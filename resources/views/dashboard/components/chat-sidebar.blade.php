<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="fas fa-comments text-primary me-2"></i>
                    <span id="chat-modal-title">@lang('dashboard.chat')</span>
                    <span class="badge bg-primary ms-2" id="unread-count">0</span>
                </h5>
                <div class="d-flex align-items-center">
                    <button type="button" 
                            class="btn btn-link text-muted me-2 toggle-fullscreen" 
                            data-bs-toggle="tooltip" 
                            title="Toggle Fullscreen"
                            data-fullscreen="false">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0" style="min-height: 70vh;">
                    <!-- Chat Sidebar -->
                    <div class="col-md-4 border-end" id="chat-sidebar">
                        <!-- Search and New Chat -->
                        <div class="p-3 border-bottom">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" class="form-control" placeholder="@lang('dashboard.search_chats')" id="chat-search">
                                <button class="btn btn-primary btn-sm" id="new-chat-btn" title="New Chat">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Chat Type Tabs -->
                        <ul class="nav nav-tabs nav-tabs-line border-bottom" id="chatTypeTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" 
                                        id="private-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#private-chats" 
                                        type="button"
                                        role="tab"
                                        aria-controls="private-chats"
                                        aria-selected="true">
                                    <i class="fas fa-user-friends me-1"></i> Private
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" 
                                        id="group-tab" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#group-chats" 
                                        type="button"
                                        role="tab"
                                        aria-controls="group-chats"
                                        aria-selected="false">
                                    <i class="fas fa-users me-1"></i> Groups
                                </button>
                            </li>
                        </ul>

                        <!-- Chat List -->
                        <div class="tab-content h-100">
                            <div class="tab-pane fade show active h-100" id="private-chats" role="tabpanel" aria-labelledby="private-tab">
                                <div class="chat-list h-100" id="private-chat-list" style="overflow-y: auto;">
                                    <!-- Private chats will be loaded here -->
                                    <div class="d-flex flex-column h-100 justify-content-center align-items-center">
                                        <i class="fas fa-user-friends text-muted fs-2x mb-3"></i>
                                        <div class="text-muted">No private chats</div>
                                        <button class="btn btn-sm btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#newChatModal">
                                            <i class="fas fa-plus me-1"></i> New Chat
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade h-100" id="group-chats" role="tabpanel" aria-labelledby="group-tab">
                                <div class="chat-list h-100" id="group-chat-list" style="overflow-y: auto;">
                                    <!-- Group chats will be loaded here -->
                                    <div class="d-flex flex-column h-100 justify-content-center align-items-center">
                                        <i class="fas fa-users text-muted fs-2x mb-3"></i>
                                        <div class="text-muted">No group chats</div>
                                        <button class="btn btn-sm btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#newChatModal" data-bs-tab-target="#group-tab-pane">
                                            <i class="fas fa-plus me-1"></i> New Group
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8 d-flex flex-column" id="chat-window">
                        <div class="border-bottom p-3 bg-light">
                            <div class="d-flex align-items-center">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="position-relative me-3">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=random"
                                             class="rounded-circle" 
                                             width="45" 
                                             height="45"
                                             alt="{{ auth()->user()->name }}">
                                        <span class="position-absolute bottom-0 end-0 bg-success rounded-circle border border-2 border-white" 
                                              style="width: 12px; height: 12px;" 
                                              id="user-status"></span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0" id="chat-title">Select a chat</h6>
                                        <small class="text-muted" id="chat-subtitle">Start a conversation</small>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
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
                        
                        <!-- Messages -->
                        <div class="flex-grow-1 p-3" id="messages-container" style="overflow-y: auto; background-color: #f8f9fa;">
                            <div class="text-center py-5">
                                <i class="fas fa-comment text-muted fs-2x mb-3"></i>
                                <div class="text-muted">Select a chat to start messaging</div>
                            </div>
                        </div>
                        
                        <!-- Message Input -->
                        <div class="border-top p-3 bg-white">
                            <div class="d-flex align-items-center">
                                <button class="btn btn-link text-muted me-2" type="button">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <div class="flex-grow-1">
                                    <input type="text" 
                                           class="form-control" 
                                           placeholder="Type a message..." 
                                           id="message-input" 
                                           disabled
                                           style="border-radius: 20px;">
                                </div>
                                <button class="btn btn-primary ms-2 rounded-circle" 
                                        type="button" 
                                        id="send-message-btn"
                                        disabled
                                        style="width: 40px; height: 40px;">
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

<!-- New Chat Modal -->
<div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-pills mb-4 px-3" id="newChatTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" 
                                id="user-tab" 
                                data-bs-toggle="pill" 
                                data-bs-target="#user-tab-pane" 
                                type="button"
                                role="tab"
                                aria-controls="user-tab-pane"
                                aria-selected="true">
                            <i class="fas fa-user me-1"></i> New Chat
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" 
                                id="group-tab" 
                                data-bs-toggle="pill" 
                                data-bs-target="#group-tab-pane" 
                                type="button"
                                role="tab"
                                aria-controls="group-tab-pane"
                                aria-selected="false">
                            <i class="fas fa-users me-1"></i> New Group
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content px-3">
                    <!-- User Tab -->
                    <div class="tab-pane fade show active" id="user-tab-pane" role="tabpanel" aria-labelledby="user-tab">
                        <div class="input-group mb-3">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search users..." id="search-users">
                        </div>
                        <div class="user-list" id="new-chat-user-list" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-search fa-2x mb-2"></i>
                                <p>Search for users to start a chat</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Group Tab -->
                    <div class="tab-pane fade" id="group-tab-pane" role="tabpanel" aria-labelledby="group-tab">
                        <form id="create-group-form">
                            <div class="mb-3">
                                <label class="form-label">Group Name</label>
                                <input type="text" class="form-control" name="group_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Add Members</label>
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="search-group-members" placeholder="Search users to add...">
                                </div>
                                <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;" id="group-members-selection">
                                    <div class="text-center py-4 text-muted">
                                        <i class="fas fa-users fa-2x mb-2"></i>
                                        <p>Search and add members to your group</p>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Create Group
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize chat modals
    const chatModal = new bootstrap.Modal(document.getElementById('chatModal'));
    const newChatModal = new bootstrap.Modal(document.getElementById('newChatModal'));

    // Toggle fullscreen functionality
    document.querySelectorAll('.toggle-fullscreen').forEach(button => {
        button.addEventListener('click', function() {
            const isFullscreen = this.getAttribute('data-fullscreen') === 'true';
            const modal = this.closest('.modal-dialog');
            
            if (isFullscreen) {
                modal.classList.remove('modal-fullscreen');
                this.setAttribute('data-fullscreen', 'false');
                this.setAttribute('title', 'Enter Fullscreen');
                this.querySelector('i').classList.remove('fa-compress');
                this.querySelector('i').classList.add('fa-expand');
            } else {
                modal.classList.add('modal-fullscreen');
                this.setAttribute('data-fullscreen', 'true');
                this.setAttribute('title', 'Exit Fullscreen');
                this.querySelector('i').classList.remove('fa-expand');
                this.querySelector('i').classList.add('fa-compress');
            }
            
            // Update tooltip content
            const tooltip = bootstrap.Tooltip.getInstance(this);
            if (tooltip) {
                tooltip.hide();
                tooltip.setContent({'.tooltip-inner': this.getAttribute('title')});
                tooltip.show();
            }
        });
    });

    // Handle new chat button click
    document.getElementById('new-chat-btn').addEventListener('click', function() {
        // Show the new chat modal and activate the first tab
        const firstTab = document.querySelector('#newChatTabs .nav-link');
        if (firstTab) {
            const tab = new bootstrap.Tab(firstTab);
            tab.show();
        }
    });

    // Handle new group button click in the groups tab
    document.querySelector('[data-bs-tab-target="#group-tab-pane"]')?.addEventListener('click', function() {
        const groupTab = document.querySelector('#newChatTabs [data-bs-target="#group-tab-pane"]');
        if (groupTab) {
            const tab = new bootstrap.Tab(groupTab);
            tab.show();
        }
    });

    // Handle new chat modal show event
    document.getElementById('newChatModal').addEventListener('show.bs.modal', function() {
        // Reset form and search when modal is shown
        const form = document.getElementById('create-group-form');
        if (form) form.reset();
        
        const searchInput = document.getElementById('search-users');
        if (searchInput) searchInput.value = '';
        
        const userList = document.getElementById('new-chat-user-list');
        if (userList) {
            userList.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-search fa-2x mb-2"></i>
                    <p>Search for users to start a chat</p>
                </div>`;
        }
    });

    // Handle search for users
    const searchUsers = document.getElementById('search-users');
    if (searchUsers) {
        searchUsers.addEventListener('input', function(e) {
            const searchTerm = e.target.value.trim().toLowerCase();
            // Here you would typically make an AJAX request to search for users
            // For now, we'll just show a loading state
            const userList = document.getElementById('new-chat-user-list');
            if (userList) {
                userList.innerHTML = `
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Searching users...</p>
                    </div>`;
                
                // Simulate API call
                setTimeout(() => {
                    if (searchTerm.length > 0) {
                        // This would be replaced with actual user data from your backend
                        userList.innerHTML = `
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=User+1&background=random" 
                                             class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <h6 class="mb-0">User 1</h6>
                                            <small class="text-muted">user1@example.com</small>
                                        </div>
                                    </div>
                                </a>
                                <a href="#" class="list-group-item list-group-item-action">
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=User+2&background=random" 
                                             class="rounded-circle me-2" width="32" height="32">
                                        <div>
                                            <h6 class="mb-0">User 2</h6>
                                            <small class="text-muted">user2@example.com</small>
                                        </div>
                                    </div>
                                </a>
                            </div>`;
                    } else {
                        userList.innerHTML = `
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-search fa-2x mb-2"></i>
                                <p>Search for users to start a chat</p>
                            </div>`;
                    }
                }, 500);
            }
        });
    }

    // Handle form submission for creating a new group
    const createGroupForm = document.getElementById('create-group-form');
    if (createGroupForm) {
        createGroupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const groupName = formData.get('group_name');
            
            // Here you would typically make an AJAX request to create the group
            console.log('Creating group:', groupName);
            
            // Show success message and close modal
            const toast = new bootstrap.Toast(document.createElement('div'));
            toast._element.classList.add('toast', 'align-items-center', 'text-white', 'bg-success', 'border-0');
            toast._element.setAttribute('role', 'alert');
            toast._element.setAttribute('aria-live', 'assertive');
            toast._element.setAttribute('aria-atomic', 'true');
            toast._element.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>
                        Group "${groupName}" created successfully!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>`;
            
            document.body.appendChild(toast._element);
            toast.show();
            
            // Close the modal after a short delay
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('newChatModal'));
                if (modal) modal.hide();
                
                // Remove the toast after it's hidden
                setTimeout(() => {
                    toast.dispose();
                    toast._element.remove();
                }, 1000);
            }, 1500);
        });
    }
});
</script>
@endpush

<style>
/* Fullscreen modal styles */
.modal-fullscreen {
    width: 100vw;
    max-width: none;
    height: 100%;
    margin: 0;
}

.modal-fullscreen .modal-content {
    height: 100%;
    border: 0;
    border-radius: 0;
}

.modal-fullscreen .modal-body {
    overflow-y: auto;
}
/* Fullscreen chat styles */
.fullscreen-chat {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    height: 100vh !important;
    z-index: 9999 !important;
    margin: 0 !important;
    max-width: 100% !important;
    border-radius: 0 !important;
}

.chat-tab {
    border-radius: 0 !important;
    color: #7e8299;
    transition: all 0.3s ease;
}

.chat-tab.active {
    background-color: #f5f8fa;
    color: #009ef7;
    border-bottom: 2px solid #009ef7;
}

.chat-tab-content {
    display: none;
}

.chat-tab-content.active {
    display: block;
}

.chat-item {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f3f6;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.chat-item:hover {
    background-color: #f8f9fa;
}

.chat-item.active {
    background-color: #e8f4fd;
    border-left: 3px solid #009ef7;
}

.message {
    margin-bottom: 15px;
}

.message.sent {
    text-align: right;
}

.message.received {
    text-align: left;
}

.message-bubble {
    display: inline-block;
    max-width: 70%;
    padding: 8px 12px;
    border-radius: 18px;
    word-wrap: break-word;
}

.message.sent .message-bubble {
    background-color: #009ef7;
    color: white;
}

.message.received .message-bubble {
    background-color: #f1f3f6;
    color: #3f4254;
}

.message-time {
    font-size: 11px;
    color: #7e8299;
    margin-top: 4px;
}

.user-item {
    padding: 10px 16px;
    border-bottom: 1px solid #f1f3f6;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.user-item:hover {
    background-color: #f8f9fa;
}

.unread-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #f1416c;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
