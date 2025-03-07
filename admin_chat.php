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
        .chat-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .user-list {
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .chat-messages {
            height: 400px;
            overflow-y: scroll;
            margin-bottom: 20px;
            padding: 10px;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .chat-message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 10px;
            background: #f1f1f1;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .chat-message .username {
            font-weight: bold;
            color: #007bff;
        }
        .chat-message .timestamp {
            font-size: 0.8em;
            color: #888888;
        }
        .chat-message.admin-message {
            background: #d1e7dd;
        }
        .chat-message.user-message {
            background: #f8d7da;
        }
        .input-group {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .logout-link {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .user-item {
            padding: 10px;
            cursor: pointer;
        }
        .user-item.unread {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-container">
            <a href="admin_dashboard.php?logout=true" class="btn btn-danger logout-link">Logout</a>
            <h3 class="text-center mb-4">Admin Chat</h3>
            <div class="user-list" id="userList"></div>
            <div class="chat-messages" id="chatMessages"></div>
            <form id="chatForm">
                <div class="input-group">
                    <input type="text" class="form-control" id="messageInput" placeholder="Type a message..." required>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i> Send</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        let selectedUserId = null;

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
                            const userElement = `<div class="user-item ${user.unread_count > 0 ? 'unread' : ''}" data-user-id="${user.id}">${user.name} ${user.unread_count > 0 ? '(' + user.unread_count + ' unread)' : ''}</div>`;
                            $('#userList').append(userElement);
                        });

                        $('.user-item').on('click', function() {
                            selectedUserId = $(this).data('user-id');
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

            console.log('Fetching messages for user:', selectedUserId);
            $.ajax({
                url: 'chat_fetch_message.php',
                method: 'GET',
                data: { user_id: selectedUserId },
                dataType: 'json',
                success: function(data) {
                    console.log('Messages fetched:', data);
                    $('#chatMessages').empty();
                    data.forEach(function(message) {
                        const messageElement = `
                            <div class="chat-message ${message.is_admin ? 'admin-message' : 'user-message'}">
                                <div class="username">${message.is_admin ? 'system' : message.name}</div>
                                <div class="message">${message.message}</div>
                                <div class="timestamp">${new Date(message.created_at).toLocaleString()}</div>
                            </div>
                        `;
                        $('#chatMessages').append(messageElement);
                    });
                    $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                    fetchUsers(); // Refresh the user list to update unread status
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching messages:', error);
                }
            });
        }

        $('#chatForm').on('submit', function(e) {
            e.preventDefault();
            if (!selectedUserId) return;

            const message = $('#messageInput').val();
            console.log('Sending message:', message);
            $.ajax({
                url: 'chat_send_message.php',
                method: 'POST',
                data: { message: message, is_admin: 1, user_id: selectedUserId },
                success: function(response) {
                    console.log('Message sent response:', response);
                    if (response.trim() === 'success') {
                        $('#messageInput').val('');
                        fetchMessages();
                    } else {
                        alert('There was an error sending your message. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error sending message:', error);
                }
            });
        });

        fetchUsers();
        setInterval(fetchMessages, 3000); // Fetch messages every 3 seconds
    </script>
</body>
</html>