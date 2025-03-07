<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

//set the default timezone to Dhaka
date_default_timezone_set('Asia/Dhaka');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $created_at = date('Y-m-d H:i:s');

    $stmt = $conn1->prepare("INSERT INTO reviews (user_id, rating, comment, created_at) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $user_id, $rating, $comment, $created_at);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Thank you for your review!";
    header('Location: review.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Leave a Review - Mass Transport Ticketing System</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background: url('images/review_background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #000000; /* Default light mode text color */
            font-family: Arial, sans-serif;
            animation: fadeIn 2s ease-in-out;
        }
        body.dark-mode {
            background-color: #121212;
            color: #ffffff; /* Dark mode text color */
        }
        .card {
            margin: 20px auto;
            animation: fadeIn 2s ease-in-out;
            transition: transform 0.3s, box-shadow 0.3s;
            background: rgba(255, 255, 255, 0.8); /* Slightly transparent background */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Set max width for the card */
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 0 30px rgba(0, 123, 255, 0.4);
        }
        body.dark-mode .card {
            background: #1e1e1e; /* Solid dark background color */
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-body {
            color: #000000; /* Light mode card text */
        }
        body.dark-mode .card-body {
            color: #ffffff; /* Dark mode card text */
        }
        .btn-primary {
            background-color:rgb(67, 97, 58);
            border-color: #007bff;
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }
        .btn-primary:hover {
            background-color:rgb(8, 116, 13);
            border-color: #0056b3;
            transform: scale(1.05);
        }
        .btn-secondary {
            background-color:rgb(182, 37, 44);
            border-color:rgb(184, 48, 66);
            transition: background-color 0.3s, border-color 0.3s, transform 0.3s;
        }
        .btn-secondary:hover {
            background-color:rgb(250, 0, 0);
            border-color:rgb(155, 12, 12);
            transform: scale(1.05);
        }
        body.dark-mode .btn-primary {
            background-color: #bb86fc;
            border-color: #bb86fc;
        }
        body.dark-mode .btn-primary:hover {
            background-color: #9a67ea;
            border-color: #9a67ea;
            transform: scale(1.05);
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title text-center"><i class="fas fa-star"></i> Leave a Review</h3>
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success text-center">
                                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                            </div>
                        <?php endif; ?>
                        <form action="review.php" method="POST">
                            <div class="form-group">
                                <label for="rating">Rating</label>
                                <select class="form-control" id="rating" name="rating" required>
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="3">3 - Good</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="1">1 - Poor</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Submit Review</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', document.body.classList.contains('dark-mode'));
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (localStorage.getItem('dark-mode') === 'true') {
                document.body.classList.add('dark-mode');
            }
        });

        // Note: Ensure there's an element with ID 'dark-mode-toggle' in nav.php or elsewhere
        document.getElementById('dark-mode-toggle')?.addEventListener('click', toggleDarkMode);
    </script>
</body>
</html>