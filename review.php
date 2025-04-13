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
    $rating = intval($_POST['rating']);
    $comment = $_POST['comment'];
    $created_at = date('Y-m-d H:i:s');

    // Validate rating
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = "Please select a valid rating between 1 and 5 stars.";
        header('Location: review.php');
        exit;
    }

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
            color: #000000;
            font-family: 'Poppins', Arial, sans-serif;
            min-height: 100vh;
            animation: fadeIn 1s ease-out;
        }

        body.dark-mode {
            background-color: #121212;
            color: #ffffff;
        }

        .container {
            margin-top: 50px;
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        body.dark-mode .card {
            background: rgba(30, 30, 30, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 2rem;
            background: linear-gradient(45deg, #2c3e50, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-align: center;
        }

        body.dark-mode .card-title {
            background: linear-gradient(45deg, #3498db, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .rating-container {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 0.5rem;
        }

        .rating-star {
            font-size: 2.5rem;
            color: #e0e0e0;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .rating-star:hover,
        .rating-star.active {
            color: #ffd700;
            transform: scale(1.2);
        }

        .rating-star:hover ~ .rating-star {
            color: #ffd700;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 15px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        body.dark-mode .form-control {
            background: rgba(30, 30, 30, 0.9);
            border-color: #444;
            color: #fff;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-control::placeholder {
            color: #999;
        }

        body.dark-mode .form-control::placeholder {
            color: #666;
        }

        .btn-primary {
            background: linear-gradient(45deg, #2c3e50, #3498db);
            border: none;
            border-radius: 15px;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        body.dark-mode .btn-primary {
            background: linear-gradient(45deg, #3498db, #2ecc71);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        .alert {
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border: none;
            animation: slideIn 0.5s ease-out;
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

        .alert-success {
            background: linear-gradient(45deg, #2ecc71, #27ae60);
            color: white;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        body.dark-mode .form-label {
            color: #fff;
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .rating-description {
            text-align: center;
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #666;
        }

        body.dark-mode .rating-description {
            color: #999;
        }

        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: transparent;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #2c3e50;
            transition: all 0.3s ease;
            display: none; /* Hide by default */
        }

        body.dark-mode .dark-mode-toggle {
            color: #ffffff;
        }

        .dark-mode-toggle:hover {
            transform: scale(1.1);
        }

        /* Show toggle only on review page */
        body.review-page .dark-mode-toggle {
            display: block;
        }
    </style>
</head>
<body class="review-page">
    <button class="dark-mode-toggle" id="darkModeToggle">
        <i class="fas fa-moon"></i>
    </button>

    <?php include 'nav.php'; ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title"><i class="fas fa-star"></i> Leave a Review</h3>
                        <?php if (isset($_SESSION['message'])): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                            </div>
                        <?php endif; ?>
                        <form action="review.php" method="POST">
                            <div class="form-group">
                                <label class="form-label">Rating</label>
                                <div class="rating-container">
                                    <i class="fas fa-star rating-star" data-rating="1"></i>
                                    <i class="fas fa-star rating-star" data-rating="2"></i>
                                    <i class="fas fa-star rating-star" data-rating="3"></i>
                                    <i class="fas fa-star rating-star" data-rating="4"></i>
                                    <i class="fas fa-star rating-star" data-rating="5"></i>
                                </div>
                                <div class="rating-description">Click on the stars to rate</div>
                                <input type="hidden" id="rating" name="rating" required>
                            </div>
                            <div class="form-group">
                                <label for="comment" class="form-label">Your Review</label>
                                <textarea class="form-control" id="comment" name="comment" rows="4" placeholder="Share your experience with us..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Submit Review
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const darkModeToggle = document.getElementById('darkModeToggle');
            const body = document.body;
            const icon = darkModeToggle.querySelector('i');

            // Check for saved dark mode preference
            const isDarkMode = localStorage.getItem('darkMode') === 'true';
            if (isDarkMode) {
                body.classList.add('dark-mode');
                icon.classList.replace('fa-moon', 'fa-sun');
            }

            // Toggle dark mode
            darkModeToggle.addEventListener('click', function() {
                body.classList.toggle('dark-mode');
                const isDark = body.classList.contains('dark-mode');
                localStorage.setItem('darkMode', isDark);
                
                if (isDark) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                }
            });

            const stars = document.querySelectorAll('.rating-star');
            const ratingInput = document.getElementById('rating');
            
            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;
                    
                    stars.forEach(s => {
                        if (s.getAttribute('data-rating') <= rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });

                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-rating');
                    stars.forEach(s => {
                        if (s.getAttribute('data-rating') <= rating) {
                            s.style.color = '#ffd700';
                        }
                    });
                });

                star.addEventListener('mouseout', function() {
                    const currentRating = ratingInput.value;
                    stars.forEach(s => {
                        if (s.getAttribute('data-rating') <= currentRating) {
                            s.style.color = '#ffd700';
                        } else {
                            s.style.color = '#e0e0e0';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>