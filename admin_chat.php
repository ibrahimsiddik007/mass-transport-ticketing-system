<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Chat - Mass Transport Ticketing System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --background-color: #f8f9fa;
            --text-color: #2c3e50;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --dark-bg: #121212;
            --dark-card-bg: #1e1e1e;
            --dark-text: #ffffff;
            --dark-border: #333;
            --chat-bg: #ffffff;
            --user-list-bg: #f8f9fa;
            --admin-message-bg: #e3f2fd;
            --user-message-bg: #f5f5f5;
            --scrollbar-thumb: #c1c1c1;
            --scrollbar-track: #f1f1f1;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .chat-container {
            background: var(--chat-bg);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            animation: slideIn 0.6s ease-out forwards;
            position: relative;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .user-list {
            background: var(--user-list-bg);
            border-radius: 12px;
            padding: 1rem;
            margin: 1rem;
            box-shadow: var(--card-shadow);
            max-height: 300px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track);
        }

        .user-list::-webkit-scrollbar {
            width: 6px;
        }

        .user-list::-webkit-scrollbar-track {
            background: var(--scrollbar-track);
            border-radius: 3px;
        }

        .user-list::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 3px;
        }

        .user-item {
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            transition: all var(--transition-speed);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .user-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .user-item.active {
            background: var(--primary-color);
            color: white;
            transform: translateX(5px);
        }

        .user-item.unread {
            position: relative;
            font-weight: 600;
        }

        .user-item.unread::after {
            content: '';
            position: absolute;
            right: 1rem;
            width: 8px;
            height: 8px;
            background: var(--accent-color);
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }

        .chat-messages {
            height: 500px;
            padding: 1rem;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: var(--scrollbar-thumb) var(--scrollbar-track);
            background: var(--chat-bg);
            border-radius: 12px;
            margin: 1rem;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: var(--scrollbar-track);
            border-radius: 3px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb);
            border-radius: 3px;
        }

        .chat-message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 12px;
            max-width: 80%;
            position: relative;
            animation: messageIn 0.3s ease-out forwards;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        @keyframes messageIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-message.admin-message {
            background: var(--admin-message-bg);
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .chat-message.user-message {
            background: var(--user-message-bg);
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }

        .chat-message .username {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .chat-message .message {
            line-height: 1.5;
            word-wrap: break-word;
        }

        .chat-message .timestamp {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.5rem;
            text-align: right;
        }

        .input-group {
            margin: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .form-control {
            border: none;
            padding: 1rem;
            font-size: 1rem;
            border-radius: 8px 8px 0 0;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: var(--primary-color);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(74, 144, 226, 0.3);
        }

        .btn-primary:disabled {
            background: #6c757d;
            transform: none;
            box-shadow: none;
        }

        .back-link, .logout-link {
            position: absolute;
            top: 1rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all var(--transition-speed);
            z-index: 10;
        }

        .back-link {
            left: 1rem;
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .logout-link {
            right: 1rem;
            background: linear-gradient(135deg, var(--accent-color), #d93025);
            color: white;
        }

        .back-link:hover, .logout-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .no-messages {
            text-align: center;
            color: #6c757d;
            padding: 2rem;
            font-size: 1.1rem;
            animation: fadeIn 0.8s ease-out;
        }

        /* Dark Mode Support */
        body.dark-mode {
            background-color: var(--dark-bg);
            color: var(--dark-text);
        }

        body.dark-mode .chat-container {
            background-color: var(--dark-card-bg);
        }

        body.dark-mode .user-list {
            background-color: #2d2d2d;
        }

        body.dark-mode .user-item {
            background-color: #333;
            color: var(--dark-text);
        }

        body.dark-mode .user-item.active {
            background-color: var(--primary-color);
        }

        body.dark-mode .chat-messages {
            background-color: var(--dark-card-bg);
        }

        body.dark-mode .chat-message.admin-message {
            background-color: #1a237e;
            color: var(--dark-text);
        }

        body.dark-mode .chat-message.user-message {
            background-color: #333;
            color: var(--dark-text);
        }

        body.dark-mode .form-control {
            background-color: #333;
            color: var(--dark-text);
        }

        body.dark-mode .form-control:focus {
            background-color: #333;
            color: var(--dark-text);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .chat-messages {
                height: 400px;
            }
            
            .chat-message {
                max-width: 90%;
            }
            
            .back-link, .logout-link {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-container">
            <a href="admin_dashboard.php" class="btn btn-primary back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <a href="admin_dashboard.php?logout=true" class="btn btn-danger logout-link">Logout</a>
            <h3 class="text-center mb-4">Admin Chat</h3>
            <div class="user-list" id="userList"></div>
            <div class="chat-messages" id="chatMessages">
                <div class="no-messages">Select a user to view conversation</div>
            </div>
            <form id="chatForm">
                <div class="input-group">
                    <input type="text" class="form-control" id="messageInput" placeholder="Type a message..." required disabled>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit" disabled><i class="fas fa-paper-plane"></i> Send</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        let selectedUserId = null;
        let userHasMessages = false;

        function fetchUsers() {
            console.log('Fetching users...');
            $.ajax({
                url: 'chat_fetch_users.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Users fetched:', data);
                    $('#userList').empty();
                    if (data.length === 0) {
                        $('#userList').append('<div class="user-item">No users have sent messages.</div>');
                    } else {
                        data.forEach(function(user) {
                            const userElement = `<div class="user-item ${user.unread_count > 0 ? 'unread' : ''} ${user.id == selectedUserId ? 'active' : ''}" data-user-id="${user.id}">${user.name} ${user.unread_count > 0 ? '(' + user.unread_count + ' unread)' : ''}</div>`;
                            $('#userList').append(userElement);
                        });

                        // Remove existing click handlers before adding new ones to prevent duplicates
                        $('.user-item').off('click').on('click', function() {
                            $('.user-item').removeClass('active');
                            $(this).addClass('active');
                            selectedUserId = $(this).data('user-id');
                            console.log('Selected user ID:', selectedUserId); // Debug log
                            
                            // Keep input disabled until we confirm user has messages
                            $('#messageInput').prop('disabled', true);
                            $('button[type="submit"]').prop('disabled', true);
                            
                            fetchMessages();
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching users:', error);
                }
            });
        }

        function fetchMessages() {
            if (!selectedUserId) return;

            console.log('Fetching messages for user ID:', selectedUserId);
            $.ajax({
                url: 'chat_fetch_message.php',
                method: 'GET',
                data: { 
                    user_id: selectedUserId,
                    fetch_all: true,
                    // Add a cache-busting parameter
                    _: new Date().getTime()
                },
                dataType: 'json',
                success: function(data) {
                    console.log('Messages fetched:', data);
                    $('#chatMessages').empty();
                    
                    if (data.length === 0) {
                        $('#chatMessages').html('<div class="no-messages">No messages in this conversation yet</div>');
                        userHasMessages = false;
                        
                        // Keep input disabled as user hasn't initiated conversation
                        $('#messageInput').prop('disabled', true);
                        $('button[type="submit"]').prop('disabled', true);
                    } else {
                        // Check if user has sent at least one message (non-admin message)
                        const userSentMessage = data.some(message => message.is_admin == 0);
                        userHasMessages = userSentMessage;
                        
                        // Only enable input if user has sent at least one message
                        $('#messageInput').prop('disabled', !userSentMessage);
                        $('button[type="submit"]').prop('disabled', !userSentMessage);
                        
                        if (!userSentMessage) {
                            $('#chatMessages').html('<div class="no-messages">Waiting for user to initiate conversation</div>');
                        } else {
                            // Display each message with detailed debug info
                            data.forEach(function(message) {
                                const messageTypeClass = message.is_admin == 1 ? 'admin-message' : 'user-message';
                                const senderName = message.is_admin == 1 ? 'Admin' : message.name;
                                
                                const messageElement = `
                                    <div class="chat-message ${messageTypeClass}">
                                        <div class="username">${senderName}</div>
                                        <div class="message">${message.message}</div>
                                        <div class="timestamp">${new Date(message.created_at).toLocaleString()}</div>
                                    </div>
                                `;
                                $('#chatMessages').append(messageElement);
                            });
                        }
                    }
                    
                    // Scroll to bottom of chat
                    $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching messages:', error);
                    console.error('Response:', xhr.responseText);
                    $('#chatMessages').html('<div class="no-messages">Error loading messages. Please try again.</div>');
                }
            });
        }

        $('#chatForm').on('submit', function(e) {
            e.preventDefault();
            if (!selectedUserId || !userHasMessages) return;

            const message = $('#messageInput').val();
            if (!message.trim()) return;
            
            console.log('Sending message:', message);
            $.ajax({
                url: 'chat_send_message.php',
                method: 'POST',
                data: { 
                    message: message, 
                    is_admin: 1, 
                    user_id: selectedUserId
                },
                success: function(response) {
                    console.log('Message sent response:', response);
                    if (response.trim() === 'success') {
                        $('#messageInput').val('');
                        fetchMessages();
                    } else {
                        alert('There was an error sending your message. Please try again.');
                        console.error('Error details:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error sending message:', error);
                    alert('Failed to send message. Please check your connection and try again.');
                }
            });
        });

        // Initial fetch of users
        fetchUsers();
        
        // Only auto-refresh the user list, not the messages (to avoid losing context)
        setInterval(fetchUsers, 5000); // Fetch user list every 5 seconds
        
        // Only fetch messages automatically when a user is selected
        setInterval(function() {
            if (selectedUserId) {
                fetchMessages();
            }
        }, 3000); // Fetch messages every 3 seconds if a user is selected
    </script>
</body>
</html>