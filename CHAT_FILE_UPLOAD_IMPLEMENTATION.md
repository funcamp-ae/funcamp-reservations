# Chat File Upload & Group Management Implementation Guide

## Overview
This document outlines the implementation of file upload functionality (images/videos) and group management features for the chat system using laravel-plupload.

## Features Implemented

### 1. File Upload System
- ✅ Generic `FileUploadService` for reusable chunked uploads
- ✅ Support for images and videos (up to 100MB)
- ✅ Upload endpoint `/api/chat/upload`
- ✅ File message sending endpoint `/api/chat/{chat}/file-message`
- ✅ Database columns added to `messages` table (`file_path`, `file_url`, `file_type`)

### 2. Group Management
- ✅ Add members to group `/api/chat/{chat}/add-members`
- ✅ Remove members from group `/api/chat/{chat}/remove-member/{userId}`
- ✅ Update group details `/api/chat/{chat}/update`
- ✅ Promote member to admin `/api/chat/{chat}/promote/{userId}`
- ✅ Demote admin to member `/api/chat/{chat}/demote/{userId}`
- ✅ Permission system (only creator and super-admin can manage)

## Installation Steps

### Step 1: Run Migration
```bash
php artisan migrate
```

This will add the following columns to the `messages` table:
- `file_path` (nullable string)
- `file_url` (nullable string)
- `file_type` (nullable enum: image, video, file)

### Step 2: Publish Plupload Assets (if not already done)
```bash
php artisan vendor:publish --provider="JildertMiedema\LaravelPlupload\LaravelPluploadServiceProvider"
```

### Step 3: Ensure Storage Link Exists
```bash
php artisan storage:link
```

### Step 4: Set Permissions
Ensure the `storage/app/public/chat/uploads` directory is writable:
```bash
chmod -R 775 storage/app/public/chat
```

## Frontend Implementation Guide

### 1. Add Plupload CDN or Local Files

Add to `resources/views/dashboard/chat/index.blade.php` before `@endpush`:

```html
<!-- Plupload -->
<script src="https://cdn.jsdelivr.net/npm/plupload@2.3.9/js/plupload.full.min.js"></script>
```

### 2. Update Chat HTML - Add Upload Button

Replace the message input area (around line 87) with:

```html
<!-- Message Input -->
<div class="border-top p-3 " id="messageInputArea" style="display: none;">
    <div class="d-flex align-items-end gap-2">
        <!-- File Upload Button -->
        <button class="btn btn-outline-secondary" id="uploadFileBtn" type="button">
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
                            <button class="btn btn-sm btn-danger" id="cancelUpload">
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
```

### 3. Add File Upload JavaScript

Add this to the ChatSystem class (before the `init()` method ends):

```javascript
// Inside ChatSystem class, add these properties:
constructor() {
    // ... existing code ...
    this.uploadedFile = null;
    this.uploader = null;
}

// Add this method to setup plupload
setupFileUpload() {
    const self = this;
    const fileInput = document.getElementById('fileInput');
    const uploadBtn = document.getElementById('uploadFileBtn');
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
        chunk_size: '1mb',
        max_retries: 3,
        filters: {
            mime_types: [
                {title: "Image files", extensions: "jpg,jpeg,png,gif,webp"},
                {title: "Video files", extensions: "mp4,mov,avi,webm"}
            ],
            max_file_size: '100mb'
        },
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        init: {
            FilesAdded: function(up, files) {
                const file = files[0];
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
            },
            
            FileUploaded: function(up, file, response) {
                const result = JSON.parse(response.response);
                if (result.success) {
                    self.uploadedFile = {
                        file_path: result.file_path,
                        file_url: result.file_url,
                        file_type: result.file_type,
                        preview: previewContent.innerHTML
                    };
                    progressBar.classList.add('bg-success');
                } else {
                    alert('Upload failed: ' + result.message);
                    filePreview.style.display = 'none';
                }
            },
            
            Error: function(up, err) {
                alert('Upload error: ' + err.message);
                filePreview.style.display = 'none';
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

// Update sendMessage method to handle files
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
    
    // ... rest of existing sendMessage code ...
}

// New method to send file messages
async sendFileMessage(caption = '') {
    if (!this.uploadedFile || !this.currentChatId) return;
    
    const input = document.getElementById('messageInput');
    const filePreview = document.getElementById('filePreview');
    
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
        }
    } catch (error) {
        // Show error
        this.currentMessages = this.currentMessages.filter(msg => !msg.is_temp);
        tempMessage.upload_failed = true;
        this.currentMessages.push(tempMessage);
        this.renderMessages(this.currentMessages);
    }
}
```

### 4. Update renderMessages to Display Files

Replace the `renderMessages` method with this updated version:

```javascript
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
                statusIcon = '<i class="fas fa-check-double text-primary ms-1" title="Read"></i>';
            } else {
                statusIcon = '<i class="fas fa-check text-muted ms-1" title="Sent"></i>';
            }
        }
        
        // Render file content
        let fileContent = '';
        if (message.file_url && (message.type === 'image' || message.file_type === 'image')) {
            fileContent = `
                <div class="mb-2">
                    <img src="${message.file_url}" style="max-width: 300px; max-height: 300px; border-radius: 8px; cursor: pointer;" 
                         onclick="window.open('${message.file_url}', '_blank')">
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
                    <div class="d-inline-block p-3 rounded ${isOwn ? 'bg-primary text-white' : 'rounded btn-secondary shadow-sm'}" style="max-width: 100%;">
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
```

### 5. Call setupFileUpload in init()

Add this line in the `init()` method:

```javascript
init() {
    this.bindEvents();
    this.loadUsers();
    this.loadChats();
    this.setupGlobalMessageListener();
    this.setupFileUpload(); // Add this line
    if (this.currentChatId) {
        this.setupChatChannel(this.currentChatId);
    }
}
```

## Group Management UI (Optional - To Be Implemented)

### Add Group Settings Modal

Add this modal HTML before `@endsection`:

```html
<!-- Group Settings Modal -->
<div class="modal fade" id="groupSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('dashboard.group_settings') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#members">{{ __('dashboard.members') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#settings">{{ __('dashboard.settings') }}</a>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <!-- Members Tab -->
                    <div class="tab-pane fade show active" id="members">
                        <button class="btn btn-sm btn-primary mb-3" id="addMembersBtn">
                            <i class="fas fa-plus me-1"></i> {{ __('dashboard.add_members') }}
                        </button>
                        <div id="membersList"></div>
                    </div>
                    
                    <!-- Settings Tab -->
                    <div class="tab-pane fade" id="settings">
                        <div class="mb-3">
                            <label class="form-label">{{ __('dashboard.group_name') }}</label>
                            <input type="text" class="form-control" id="editGroupName">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('dashboard.group_description') }}</label>
                            <textarea class="form-control" id="editGroupDescription" rows="3"></textarea>
                        </div>
                        <button class="btn btn-primary" id="saveGroupSettings">
                            {{ __('dashboard.save_changes') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

## Testing

### Test File Upload
1. Open chat interface
2. Click the paperclip icon
3. Select an image or video
4. Add optional caption
5. Click send
6. Verify file appears immediately in chat
7. Verify file is broadcasted to other participants

### Test Group Management
1. Create a group chat
2. Try adding members (should work if creator or super-admin)
3. Try removing members
4. Try promoting to admin
5. Verify permissions work correctly

## Git Commands to Commit Changes

```bash
# Add all files
git add app/Services/FileUploadService.php
git add app/Models/Message.php
git add app/Http/Controllers/ChatController.php
git add routes/web.php
git add database/migrations/*add_file_columns_to_messages_table.php
git add CHAT_FILE_UPLOAD_IMPLEMENTATION.md

# Commit
git commit -m "Implement chat file upload and group management features

- Add FileUploadService for reusable chunked uploads using plupload
- Add file upload support to messages (file_path, file_url, file_type)
- Add group management endpoints (add/remove members, promote/demote admins)
- Add permission system for group management
- Support for images and videos up to 100MB
- Immediate file display in chat with error handling"

# Push to GitHub
git push origin master
```

## Next Steps (Frontend)

The backend is complete. To finish the implementation:

1. ✅ Backend file upload service created
2. ✅ Backend routes and controllers added
3. ✅ Database migration created
4. ✅ Group management API endpoints created
5. ⏳ Frontend file upload UI needs to be added to `index.blade.php`
6. ⏳ Frontend file rendering needs to be updated
7. ⏳ Group settings modal UI needs to be added
8. ⏳ Frontend JavaScript for group management needs to be implemented

## API Endpoints Summary

### File Upload
- `POST /api/chat/upload` - Upload file chunks
- `POST /api/chat/{chat}/file-message` - Send file message

### Group Management  
- `POST /api/chat/{chat}/add-members` - Add members
- `DELETE /api/chat/{chat}/remove-member/{userId}` - Remove member
- `PUT /api/chat/{chat}/update` - Update group details
- `POST /api/chat/{chat}/promote/{userId}` - Promote to admin
- `POST /api/chat/{chat}/demote/{userId}` - Demote to member

## Notes

- File uploads are chunked for better performance with large files
- Files are stored in `storage/app/public/chat/uploads`
- Only group creators and super-admins can manage groups
- Group creators cannot be removed from groups
- File messages support optional captions
- Failed uploads show error icon in chat
