// Initialize chat functionality
document.addEventListener('DOMContentLoaded', function() {
    // Get the chat toggle button and modal
    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const chatModal = new bootstrap.Modal(document.getElementById('chatModal'));

    // Add click event listener to the chat toggle button
    if (chatToggleBtn) {
        chatToggleBtn.addEventListener('click', function() {
            chatModal.show();
        });
    }

    // Toggle fullscreen
    const toggleFullscreenBtn = document.getElementById('toggle-fullscreen');
    if (toggleFullscreenBtn) {
        toggleFullscreenBtn.addEventListener('click', function() {
            const chatWindow = document.getElementById('chat-window');
            chatWindow.classList.toggle('fullscreen-chat');
            this.querySelector('i').classList.toggle('fa-compress');
            this.querySelector('i').classList.toggle('fa-expand');
        });
    }

    // Initialize new chat button
    const newChatBtn = document.getElementById('new-chat-btn');
    const newChatModal = new bootstrap.Modal(document.getElementById('newChatModal'));
    
    if (newChatBtn) {
        newChatBtn.addEventListener('click', function() {
            newChatModal.show();
        });
    }
});
