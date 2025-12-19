@extends('layouts.dashboard')

@push('styles')
    <style>
        /* Chat Main Container */
        .chat-container {
            height: calc(100vh - 140px);
            display: flex;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        /* Sidebar (Contact List) */
        .chat-sidebar {
            width: 350px;
            border-right: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
            background: #fff;
            position: relative;
            z-index: 10;
        }

        .chat-search-area {
            padding: 16px;
        }

        .chat-search-input {
            width: 100%;
            padding: 10px 16px;
            background: #f5f6f8;
            border: none;
            border-radius: 20px;
            font-size: 14px;
            color: #333;
            outline: none;
        }

        .chat-search-input::placeholder {
            color: #999;
        }

        /* Horizontal Stories (All Contacts) */
        .stories-container {
            padding: 10px 16px;
            display: flex;
            gap: 15px;
            overflow-x: auto;
            border-bottom: 1px solid #f0f0f0;
            scrollbar-width: none;
            /* Firefox */
        }

        .stories-container::-webkit-scrollbar {
            display: none;
            /* Chrome/Safari */
        }

        .story-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            min-width: 60px;
        }

        .story-avatar-wrapper {
            position: relative;
            margin-bottom: 5px;
        }

        .story-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid transparent;
            /* Can add active border */
            padding: 2px;
        }

        .story-online-indicator {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 12px;
            height: 12px;
            background: #31a24c;
            border: 2px solid #fff;
            border-radius: 50%;
        }

        .story-name {
            font-size: 11px;
            color: #050505;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 60px;
        }

        /* Vertical Recent List */
        .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }

        .conversation-item {
            padding: 10px 16px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background 0.2s;
            border-radius: 8px;
            margin: 0 8px;
        }

        .conversation-item:hover,
        .conversation-item.active {
            background: #f5f5f5;
        }

        .conv-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 12px;
        }

        .conv-info {
            flex: 1;
            min-width: 0;
        }

        .conv-name {
            font-size: 15px;
            font-weight: 600;
            color: #050505;
            margin-bottom: 2px;
        }

        .conv-preview {
            font-size: 13px;
            color: #65676b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: flex;
            align-items: center;
        }

        .conv-preview.unread {
            color: #050505;
            font-weight: 600;
        }

        .conv-time {
            font-size: 11px;
            color: #65676b;
            margin-left: auto;
            white-space: nowrap;
        }

        /* Chat Main Window */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
            position: relative;
        }

        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            height: 65px;
        }

        .back-btn {
            display: none;
            /* Mobile only */
            margin-right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            color: #0084ff;
            cursor: pointer;
        }

        .chat-header-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .chat-header-info h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .message {
            display: flex;
            max-width: 75%;
            margin-bottom: 4px;
        }

        .message.sent {
            align-self: flex-end;
            justify-content: flex-end;
        }

        .message-bubble {
            padding: 10px 14px;
            border-radius: 18px;
            font-size: 15px;
            line-height: 1.4;
            position: relative;
        }

        .message.sent .message-bubble {
            background: #0084ff;
            /* Messenger Blue */
            color: #fff;
            border-bottom-right-radius: 4px;
        }

        .message.received .message-bubble {
            background: #e4e6eb;
            color: #050505;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 10px;
            color: #65676b;
            margin-top: 2px;
            padding: 0 5px;
        }

        .message.sent .message-time {
            text-align: right;
        }

        .chat-input-area {
            padding: 10px 16px;
            display: flex;
            align-items: center;
            border-top: 1px solid #f0f0f0;
        }

        .chat-input {
            flex: 1;
            background: #f0f2f5;
            border: none;
            border-radius: 20px;
            padding: 10px 15px;
            font-size: 15px;
            outline: none;
            margin-right: 10px;
        }

        .send-btn {
            background: none;
            border: none;
            color: #0084ff;
            font-size: 20px;
            cursor: pointer;
        }

        .empty-state {
            display: flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            color: #ccc;
            flex-direction: column;
        }

        .empty-state i {
            font-size: 50px;
            margin-bottom: 10px;
        }

        /* Mobile Responsiveness */
        @media screen and (max-width: 768px) {
            .chat-container {
                height: calc(100vh - 80px);
                /* Adjust based on header */
                margin: 0 -15px;
                /* Full width */
                border-radius: 0;
                border: none;
            }

            .chat-sidebar {
                width: 100%;
                border-right: none;
            }

            .chat-main {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                transform: translateX(100%);
                transition: transform 0.3s ease-in-out;
                z-index: 20;
                background: #fff;
            }

            .chat-container.mobile-chat-active .chat-main {
                transform: translateX(0);
            }

            display: block;
        }

        /* Dark Mode Support */
        body[data-background-color="dark"] .chat-container,
        body[data-theme="dark"] .chat-container {
            background: #1a2035;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        body[data-background-color="dark"] .chat-sidebar,
        body[data-theme="dark"] .chat-sidebar {
            background: #1a2035;
            border-right: 1px solid #2f374b;
        }

        body[data-background-color="dark"] .chat-search-input,
        body[data-theme="dark"] .chat-search-input {
            background: #151a2b;
            color: #fff;
        }

        body[data-background-color="dark"] .stories-container,
        body[data-background-color="dark"] .chat-header,
        body[data-background-color="dark"] .chat-input-area,
        body[data-theme="dark"] .stories-container,
        body[data-theme="dark"] .chat-header,
        body[data-theme="dark"] .chat-input-area {
            border-color: #2f374b;
        }

        body[data-background-color="dark"] .story-name,
        body[data-background-color="dark"] .conv-name,
        body[data-theme="dark"] .story-name,
        body[data-theme="dark"] .conv-name {
            color: #e5e5e5;
        }

        body[data-background-color="dark"] .conversation-item:hover,
        body[data-background-color="dark"] .conversation-item.active,
        body[data-theme="dark"] .conversation-item:hover,
        body[data-theme="dark"] .conversation-item.active {
            background: #151a2b;
        }

        body[data-background-color="dark"] .conv-preview,
        body[data-theme="dark"] .conv-preview {
            color: #b9babf;
        }

        body[data-background-color="dark"] .conv-preview.unread,
        body[data-theme="dark"] .conv-preview.unread {
            color: #fff;
        }

        body[data-background-color="dark"] .chat-main,
        body[data-theme="dark"] .chat-main {
            background: #1a2035;
        }

        body[data-background-color="dark"] .chat-input,
        body[data-theme="dark"] .chat-input {
            background: #151a2b;
            color: #fff;
        }

        body[data-background-color="dark"] .message.received .message-bubble,
        body[data-theme="dark"] .message.received .message-bubble {
            background: #2f374b;
            color: #fff;
        }
    </style>
@endpush

@section('content')
    <div class="chat-container" id="chatContainer">
        <!-- Contact List Sidebar -->
        <div class="chat-sidebar">
            <div class="chat-search-area">
                <input type="text" id="searchInput" class="chat-search-input" placeholder="Search">
            </div>

            <!-- Horizontal Stories (All Contacts) -->
            <div class="stories-container" id="allContactList">
                <!-- Loaded via JS -->
            </div>

            <!-- Vertical Recent List -->
            <div class="conversations-list" id="recentContactList">
                <!-- Loaded via JS -->
            </div>
        </div>

        <!-- Chat Main Area -->
        <div class="chat-main">
            <div class="chat-header" id="chatHeader" style="display: none;">
                <button class="back-btn" onclick="closeChat()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <img src="" id="chatHeaderAvatar" class="chat-header-avatar" alt="">
                <div class="chat-header-info">
                    <h4 id="chatHeaderName">User Name</h4>
                </div>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="empty-state">
                    <i class="fab fa-facebook-messenger"></i>
                    <p>Select a contact to start chatting</p>
                </div>
            </div>

            <div class="chat-input-area" id="chatInputArea" style="display: none;">
                <input type="text" class="chat-input" id="messageInput" placeholder="Type a message...">
                <button class="send-btn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentContactId = null;
        let currentContactType = null;
        let messageInterval = null;
        let allContactsData = [];
        let recentChatsData = [];
        let lastMessageCount = 0; // Track message count to detect new messages

        document.addEventListener('DOMContentLoaded', function () {
            loadContacts();

            // Search Listener
            document.getElementById('searchInput').addEventListener('input', function (e) {
                const term = e.target.value.toLowerCase();
                filterContacts(term);
            });

            document.getElementById('messageInput')?.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') sendMessage();
            });

            // Removed 10s polling - contacts update when messages arrive/sent
        });

        function loadContacts() {
            fetch('{{ route("chat.contacts") }}')
                .then(response => response.json())
                .then(data => {
                    allContactsData = data.all_contacts || [];
                    recentChatsData = data.recent_conversations || [];

                    renderAllContacts(allContactsData);
                    renderRecentChats(recentChatsData);
                })
                .catch(error => console.error('Error loading contacts:', error));
        }

        function renderAllContacts(contacts) {
            const container = document.getElementById('allContactList');
            container.innerHTML = '';

            contacts.forEach(contact => {
                const avatar = contact.profile_photo ? '{{ asset("uploads") }}/' + contact.profile_photo : '{{ asset("uploads/user.png") }}';
                const onlineIndicator = contact.is_online ? '<div class="story-online-indicator"></div>' : '';

                const item = document.createElement('div');
                item.className = 'story-item';
                item.onclick = () => openChat(contact);

                item.innerHTML = `
                        <div class="story-avatar-wrapper">
                            <img src="${avatar}" class="story-avatar">
                            ${onlineIndicator}
                        </div>
                        <div class="story-name">${contact.name.split(' ')[0]}</div>
                    `;
                container.appendChild(item);
            });
        }

        function renderRecentChats(chats) {
            const container = document.getElementById('recentContactList');
            container.innerHTML = '';

            chats.forEach(chat => {
                const avatar = chat.profile_photo ? '{{ asset("uploads") }}/' + chat.profile_photo : '{{ asset("uploads/user.png") }}';
                const isUnread = chat.unread_count > 0;

                const item = document.createElement('div');
                item.className = `conversation-item ${isUnread ? 'unread' : ''}`;
                item.onclick = () => openChat(chat);

                item.innerHTML = `
                        <img src="${avatar}" class="conv-avatar">
                        <div class="conv-info">
                            <div class="conv-name">${chat.name}</div>
                            <div class="conv-preview ${isUnread ? 'unread' : ''}">
                                ${chat.last_message || 'Start a conversation'}
                            </div>
                        </div>
                        <div class="conv-time">${chat.last_message_time || ''}</div>
                    `;
                container.appendChild(item);
            });
        }

        function filterContacts(term) {
            const filteredAll = allContactsData.filter(c => c.name.toLowerCase().includes(term));
            const filteredRecent = recentChatsData.filter(c => c.name.toLowerCase().includes(term));

            renderAllContacts(filteredAll);
            renderRecentChats(filteredRecent);
        }

        function openChat(contact) {
            currentContactId = contact.id;
            currentContactType = contact.role; // Assuming role is available on contact object

            // UI Updates
            document.getElementById('chatHeader').style.display = 'flex';
            document.getElementById('chatInputArea').style.display = 'flex';
            document.getElementById('chatHeaderName').textContent = contact.name;
            document.getElementById('chatHeaderAvatar').src = contact.profile_photo ? '{{ asset("uploads") }}/' + contact.profile_photo : '{{ asset("uploads/user.png") }}';

            // Mobile Toggle
            document.getElementById('chatContainer').classList.add('mobile-chat-active');

            // Mark active in list
            document.querySelectorAll('.conversation-item').forEach(el => el.classList.remove('active'));
            // Optionally find specific element to add active class

            loadMessages();
            if (messageInterval) clearInterval(messageInterval);
            messageInterval = setInterval(loadMessages, 3000);
        }

        function closeChat() {
            document.getElementById('chatContainer').classList.remove('mobile-chat-active');
        }

        function loadMessages() {
            if (!currentContactId) return;

            fetch(`{{ url('chat/messages') }}/${currentContactId}/${currentContactType}`)
                .then(response => response.json())
                .then(messages => {
                    const chatMessages = document.getElementById('chatMessages');

                    // Detect new messages and update contact list
                    if (messages.length > lastMessageCount) {
                        loadContacts(); // Update message preview when new message arrives
                    }
                    lastMessageCount = messages.length;

                    chatMessages.innerHTML = ''; // Optimize this later if needed

                    messages.forEach(msg => {
                        const isSent = msg.sender_id == {{ auth()->id() }};
                        const messageDiv = document.createElement('div');
                        messageDiv.className = isSent ? 'message sent' : 'message received';

                        messageDiv.innerHTML = `
                            <div class="message-bubble">
                                ${msg.message}
                                <div class="message-time">
                                    ${new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                </div>
                            </div>
                        `;
                        chatMessages.appendChild(messageDiv);
                    });

                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            if (!message || !currentContactId) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    receiver_id: currentContactId,
                    receiver_type: currentContactType,
                    message: message
                })
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    input.value = '';
                    loadMessages();
                    loadContacts(); // Update message preview after sending
                }
            });
        }
    </script>
@endpush