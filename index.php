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
        }

        body {
            color: #fff;
            animation: fadeIn 3s ease-in-out;
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
            animation: scroll 15s linear infinite;
        }

        @keyframes scroll {
            0% { transform: translateX(125%); }
            100% { transform: translateX(-100%); }
        }

        .card {
            transition: transform 0.9s, margin 0.9s;
        }

        .card:hover {
            transform: scale(1.3);
            transition: transform 0.9s;
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
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <!-- Notice Section -->
    <div class="notice-container">
        <div class="notice">
            <strong>Notice:</strong> Please make sure that your payment is done under 15 minutes otherwise you will not be able to select your desired seat.
        </div>
    </div>

    <!-- Image Slider -->
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
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

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    
</body>
</html>