<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mass Transport Ticketing System</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .carousel-item img {
            max-width: 100%;
            max-height: 700px;
            object-fit: cover;
            opacity: 0.9;
        }
        .carousel-caption {
            background: rgba(0, 0, 0, 0.5); 
            border-radius: 15px; 
            padding: 10px;
            width: auto;
            max-width: 50%;
            margin: 450px 400px ; 
        }
        </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <!-- Image Slider -->
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
            
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images\metro.jpg" class="d-block w-100" alt="Image 1">
                <div class="carousel-caption d-none d-md-block">
                    <h4>Metro</h4>
                    <p>Experience the comfort of metro travel with the most updated technology to ensure your journey through the captial Dhaka</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images\train.jpg" class="d-block w-100" alt="Image 2">
                <div class="carousel-caption d-none d-md-block">
                    <h4>Train</h4>
                    <p>Fast and reliable train services connecting major cities across the country, ensuring a comfortable and timely journey.</p>
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
    </style>
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Why Choose Us</h2>
            <style>
                .card {
                    transition: transform 0.9s, margin 0.9s;
                }
                .card:hover {
                    transform: scale(1.3);
                    transition: transform 0.9s;
                    margin: 10px;
                }
            </style>
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


                





                <!-- Footer -->
                <footer class="bg-dark text-white py-4">
                    <div class="container text-center">
                        <p>&copy; 2024 Mass Transport Ticketing System. All rights reserved.</p>
                        <p>Contact us: <a href="mailto:support@masstransport.com" class="text-white">support@masstransport.com</a></p>
                        <p>Follow us on:
                            <a href="#" class="text-white ml-2">Facebook</a> |
                            <a href="#" class="text-white ml-2">Twitter</a> |
                            <a href="#" class="text-white ml-2">Instagram</a>
                        </p>
                    </div>
                </footer>

</body>
</html>