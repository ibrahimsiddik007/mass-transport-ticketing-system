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
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #50c878;
            --accent-color: #ff6b6b;
            --background-light: #f8f9fa;
            --text-light: #2c3e50;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition-speed: 0.3s;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            transition: all var(--transition-speed);
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .chat-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            position: relative;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .chat-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 200, 120, 0.1));
            border-radius: 20px;
            pointer-events: none;
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { background-position: -100% 0; }
            100% { background-position: 200% 0; }
        }

        .chat-messages {
            height: 500px;
            overflow-y: scroll;
            margin-bottom: 30px;
            padding: 20px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            box-shadow: inset 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease-out;
        }

        .chat-message {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 15px;
            background: var(--glass-bg);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all var(--transition-speed);
            animation: messageSlide 0.4s ease-out;
            position: relative;
            overflow: hidden;
        }

        @keyframes messageSlide {
            from { transform: translateX(-20px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        .chat-message::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .chat-message:hover::before {
            transform: translateX(100%);
        }

        .chat-message .username {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-message .username i {
            font-size: 1.2rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }

        .chat-message .message {
            color: var(--text-light);
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .chat-message .timestamp {
            font-size: 0.85rem;
            color: #6c757d;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .chat-message.admin-message {
            background: linear-gradient(135deg, #d1e7dd, #c3e6cb);
            border-left: 4px solid var(--secondary-color);
        }

        .chat-message.user-message {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-left: 4px solid var(--accent-color);
        }

        .input-group {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
            transition: all var(--transition-speed);
        }

        .input-group:focus-within {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .form-control {
            border: none;
            padding: 15px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-radius: 12px 0 0 12px;
            transition: all var(--transition-speed);
        }

        .form-control:focus {
            background: var(--glass-bg);
            box-shadow: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #357abd);
            border: none;
            padding: 15px 25px;
            border-radius: 0 12px 12px 0;
            transition: all var(--transition-speed);
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(74, 144, 226, 0.3);
        }

        .btn-primary:hover::before {
            transform: translateX(100%);
        }

        .btn-primary i {
            margin-right: 8px;
            animation: float 2s ease-in-out infinite;
        }

        /* Dark Mode Styles */
        body.dark-mode {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            --glass-bg: rgba(30, 30, 30, 0.8);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            --text-light: #e0e0e0;
        }

        body.dark-mode .chat-container {
            background: var(--glass-bg);
        }

        body.dark-mode .chat-messages {
            background: var(--glass-bg);
        }

        body.dark-mode .chat-message {
            background: var(--glass-bg);
        }

        body.dark-mode .chat-message.admin-message {
            background: linear-gradient(135deg, #2d4a3e, #1e3a2a);
            border-left: 4px solid var(--secondary-color);
        }

        body.dark-mode .chat-message.user-message {
            background: linear-gradient(135deg, #4a2d2d, #3a1e1e);
            border-left: 4px solid var(--accent-color);
        }

        body.dark-mode .form-control {
            background: var(--glass-bg);
            color: var(--text-light);
        }

        body.dark-mode .btn-primary {
            background: linear-gradient(135deg, #357abd, var(--primary-color));
        }

        /* Scrollbar Styling */
        .chat-messages::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(74, 144, 226, 0.3);
            border-radius: 4px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: rgba(74, 144, 226, 0.5);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .chat-container {
                margin: 20px;
                padding: 20px;
            }

            .chat-messages {
                height: 400px;
                padding: 15px;
            }

            .chat-message {
                padding: 12px;
            }

            .form-control {
                padding: 12px;
            }

            .btn-primary {
                padding: 12px 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container mt-5">
        <div class="chat-container">
            <h3 class="text-center mb-4">
                <i class="fas fa-comments"></i> Live Chat
            </h3>
            <div class="chat-messages" id="chatMessages"></div>
            <form id="chatForm">
                <div class="input-group">
                    <input type="text" class="form-control" id="messageInput" placeholder="Type a message..." required>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane"></i> Send
                        </button>
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
                                    <div class="username">
                                        <i class="fas ${message.is_admin ? 'fa-shield-alt' : 'fa-user'}"></i>
                                        ${message.is_admin ? 'System' : message.name}
                                    </div>
                                    <div class="message">${message.message}</div>
                                    <div class="timestamp">
                                        <i class="far fa-clock"></i>
                                        ${new Date(message.created_at).toLocaleString()}
                                    </div>
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
            setInterval(fetchMessages, 3000);
        });
    </script>
</body>
</html>