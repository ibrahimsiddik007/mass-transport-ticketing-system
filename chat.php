<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Chat - Mass Transport Ticketing System</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: var(--bg-light);
            color: var(--text-light);
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .dark-mode {
            --bg-light: #2c2c2c;
            --text-light: #e0e0e0;
        }

        .chat-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: var(--bg-light);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .chat-messages {
            height: 500px;
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

        .dark-mode .chat-container {
            background: #3a3a3a;
        }

        .dark-mode .chat-messages {
            background: #2c2c2c;
        }

        .dark-mode .chat-message {
            background: #444444;
        }

        .dark-mode .chat-message .username {
            color: #bb86fc;
        }

        .dark-mode .chat-message .timestamp {
            color: #bbbbbb;
        }

        .dark-mode .chat-message.admin-message {
            background: #4a4a4a;
        }

        .dark-mode .chat-message.user-message {
            background: #5a5a5a;
        }

        .dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }

        .dark-mode .btn-primary:hover {
            background-color: #9a67ea;
            border-color: #9a67ea;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <div class="chat-container">
            <h3 class="text-center mb-4">Live Chat</h3>
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
        $(document).ready(function() {
            function fetchMessages() {
                console.log('Fetching messages...');
                $.ajax({
                    url: 'chat_fetch_message.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        console.log('Messages fetched:', data);
                        $('#chatMessages').empty();
                        data.forEach(function(message) {
                            const messageElement = `
                                <div class="chat-message ${message.is_admin ? 'admin-message' : 'user-message'}">
                                    <div class="username">${message.is_admin ? 'System' : message.name}</div>
                                    <div class="message">${message.message}</div>
                                    <div class="timestamp">${new Date(message.created_at).toLocaleString()}</div>
                                </div>
                            `;
                            $('#chatMessages').append(messageElement);
                        });
                        $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching messages:', error);
                    }
                });
            }

            $('#chatForm').on('submit', function(e) {
                e.preventDefault();
                const message = $('#messageInput').val();
                console.log('Sending message:', message);
                $.ajax({
                    url: 'chat_send_message.php',
                    method: 'POST',
                    data: { message: message, is_admin: 0 },
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

            fetchMessages();
            setInterval(fetchMessages, 3000); // Fetch messages every 3 seconds
        });
    </script>
</body>
</html>