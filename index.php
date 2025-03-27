<?php
session_start();
include 'db.php'; // Include your database connection file
?>

<!DOCTYPE html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mass Transport Ticketing System</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-light: #ffffff;
            --bg-dark: #2c2c2c;
            --text-light: #000000;
            --text-dark: #e0e0e0;
            --hover-light: #007bff;
            --hover-dark: #007bff;
            --card-bg-dark: #3a3a3a;
            --footer-bg-dark: #3a3a3a;
            --notice-bg-dark: #444444;
            --carousel-caption-bg-dark: rgba(255, 255, 255, 0.7);
            --carousel-caption-text-dark: #000000;
            --reviews-bg-dark: #1e1e1e;
            --contact-bg-dark: #1e1e1e;
            --input-bg-dark: #3a3a3a;
            --input-text-dark: #e0e0e0;
            --input-border-dark: #555555;
            --section-bg-light-1: #f8f9fa;
            --section-bg-light-2: #e9ecef;
            --section-bg-dark-1: #343a40;
            --section-bg-dark-2: #495057;
        }

        body {
            color: #fff;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: var(--bg-light);
            color: var(--text-light);
            transition: background-color 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .dark-mode {
            --bg-light: var(--bg-dark);
            --text-light: var(--text-dark);
        }

        .carousel-item img {
            max-width: 100%;
            max-height: 400px;
            object-fit: cover;
            opacity: 0.9;
        }

        .carousel-caption {
            background: rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            padding: 10px;
            width: auto;
            max-width: 50%;
            margin: 150px 400px;
        }

        .carousel-caption.dark-mode {
            background: var(--carousel-caption-bg-dark);
            color: var(--carousel-caption-text-dark);
        }

        .notice-container {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            overflow: hidden;
            position: relative;
            white-space: nowrap;
        }

        .notice-container.dark-mode {
            background-color: var(--notice-bg-dark);
            color: var(--text-dark);
        }

        .notice {
            display: inline-block;
            animation: scroll 20s linear infinite;
        }

        @keyframes scroll {
            0% { transform: translateX(125%); }
            100% { transform: translateX(-100%); }
        }

        .card {
            transition: transform 0.5s, margin 0.5s;
        }

        .card:hover {
            transform: scale(1.05);
            transition: transform 0.3s;
            margin: 10px;
        }

        .dark-mode .card {
            background-color: var(--card-bg-dark);
            color: var(--text-dark);
        }

        .footer {
            background-color: #343a40;
            color: #ffffff;
        }

        .footer.dark-mode {
            background-color: var(--footer-bg-dark);
            color: var(--text-dark);
        }

        .reviews-section {
            background-color: var(--section-bg-light-1);
            transition: background-color 0.3s ease-in-out;
        }

        .dark-mode .reviews-section {
            background-color: var(--section-bg-dark-1);
        }

        .contact-section {
            background-color: var(--section-bg-light-2);
            transition: background-color 0.3s ease-in-out;
        }

        .dark-mode .contact-section {
            background-color: var(--section-bg-dark-2);
        }

        .dark-mode .form-control {
            background-color: var(--input-bg-dark);
            color: var(--input-text-dark);
            border-color: var(--input-border-dark);
        }

        .dark-mode .form-control::placeholder {
            color: var(--input-text-dark);
        }

        .form-group i {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 10px;
            color: #aaa;
        }

        .form-group {
            position: relative;
        }

        .form-control {
            padding-left: 30px;
        }

        .alert {
            display: none;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <!-- Notice Section -->
    <div class="notice-container">
        <div class="notice">
            <strong>Notice:</strong> Please make sure that your payment is done under 15 minutes otherwise you will not be able to select your desired seat. •• System may contain bugs if it persists don't hesitate to contact with Live Chat •• Thank you for visiting us ••
        </div>
    </div>

    <!-- Image Slider -->
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" data-interval="2000">
        <ol class="carousel-indicators">
            <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/metro.jpg" class="d-block w-100" alt="Image 1">
                <div class="carousel-caption d-none d-md-block">
                    <h4>Metro</h4>
                    <p>Experience the comfort of metro travel with the most updated technology to ensure your journey through the capital Dhaka</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/train.jpg" class="d-block w-100" alt="Image 2">
                <div class="carousel-caption d-none d-md-block">
                    <h4>Train</h4>
                    <p>Fast and reliable train services connecting major cities across the country, ensuring a comfortable and timely journey.</p>
                </div>
            </div>
        <div class="carousel-item">
                <img src="images/bus.jpg" class="d-block w-100" alt="Image 3">
                <div class="carousel-caption d-none d-md-block">
                    <h4>Bus</h4>
                    <p>Our bus services offer extensive routes covering the whole Dhaka City.</p>
                </div>
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <!-- Why Choose Us Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Why Choose Us</h2>
            <div class="row">
                <div class="col-md-3">
                    <div class="card mb-4 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Convenience</h5>
                            <p class="card-text">Book your tickets from the comfort of your home with our easy-to-use online platform.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mb-4 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Reliability</h5>
                            <p class="card-text">Our services are punctual and reliable, ensuring you reach your destination on time.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mb-4 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Affordability</h5>
                            <p class="card-text">We offer pricing based on the fare directed by the Government of Bangladesh.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card mb-4 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Ease of Payment</h5>
                            <p class="card-text">Use our secure and easy payment gateway, including options like bKash, to complete your transactions effortlessly.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- User Reviews Section -->
    <section class="py-5 reviews-section">
        <div class="container">
            <h2 class="text-center mb-4">See what our users say about our service</h2>
            <div class="row">
                <?php
                $query = "SELECT reviews.*, users.name FROM reviews JOIN users ON reviews.user_id = users.id ORDER BY reviews.display_order ASC LIMIT 5";
                $result = mysqli_query($conn1, $query);
                while ($row = mysqli_fetch_assoc($result)) {
                    $formatted_date = date('Y-m-d h:i A', strtotime($row['created_at']));
                    echo '<div class="col-md-4">';
                    echo '<div class="card mb-4 shadow-sm h-100">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title"><i class="fas fa-user"></i> ' . htmlspecialchars($row['name']) . '</h5>';
                    echo '<p class="card-text">' . htmlspecialchars($row['comment']) . '</p>';
                    echo '<p class="card-text"><small class="text-muted"><i class="fas fa-clock"></i> ' . htmlspecialchars($formatted_date) . '</small></p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- Contact Us Section -->
    <section class="py-5 contact-section">
        <div class="container">
            <h2 class="text-center mb-4">Contact Us</h2>
            <p class="text-center">We are always ready to hear from you</p>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="alert alert-success" role="alert" id="successMessage">
                        Your message was sent successfully!
                    </div>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body">
                            <form id="contactForm">
                                <div class="form-group">
                                    <i class="fas fa-user"></i>
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <i class="fas fa-envelope"></i>
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="form-group">
                                    <i class="fas fa-phone"></i>
                                    <label for="phone">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" required>
                                </div>
                                <div class="form-group">
                                    <i class="fas fa-comment"></i>
                                    <label for="message">Message</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#contactForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'contact_process.php',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.trim() === 'success') {
                            $('#successMessage').show();
                            $('#contactForm')[0].reset();
                        } else {
                            alert('There was an error sending your message. Please try again.');
                        }
                    }
                });
            });
        });
    </script>

    <?php include 'footer.php'; ?>
    
</body>
</html>