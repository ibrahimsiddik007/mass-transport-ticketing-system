<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<footer class="footer py-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="text-uppercase mb-4">Mass Transport Ticketing System</h5>
                <p class="mb-4">Your one-stop solution for all your transportation needs. Book tickets for metro, train, and bus services with ease.</p>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="text-uppercase mb-4">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php">Home</a></li>
                    <li class="mb-2"><a href="metro.php">Metro</a></li>
                    <li class="mb-2"><a href="train.php">Train</a></li>
                    <li class="mb-2"><a href="bus.php">Bus</a></li>
                    <li class="mb-2"><a href="long_route.php">InterCity Bus</a></li>
                </ul>
            </div>
            
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="text-uppercase mb-4">Contact Us</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i> masstransportsystem@gmail.com</li>
                    <li class="mb-2"><i class="fas fa-phone me-2"></i> +880 1601750278</li>
                    <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> Dhaka, Bangladesh</li>
                </ul>
            </div>
        </div>
        
        <hr class="my-4">
        
        <div class="row align-items-center">
            <div class="col-md-12 text-center text-md-end">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Mass Transport Ticketing System. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>

<style>
    .footer {
        background: linear-gradient(135deg, #2c3e50, #3498db);
        color: #ffffff;
        position: relative;
        overflow: hidden;
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3498db, #2ecc71, #3498db);
    }

    .footer h5 {
        font-weight: 600;
        margin-bottom: 1.5rem;
        position: relative;
        display: inline-block;
    }

    .footer h5::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 0;
        width: 40px;
        height: 2px;
        background: #3498db;
    }

    .footer a {
        color: #ffffff;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .footer a:hover {
        color: #3498db;
        transform: translateX(5px);
    }

    .social-links a {
        display: inline-block;
        width: 40px;
        height: 40px;
        line-height: 40px;
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        margin-right: 10px;
        transition: all 0.3s ease;
    }

    .social-links a:hover {
        background: #3498db;
        transform: translateY(-5px);
    }

    .newsletter-form .form-control {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: #ffffff;
    }

    .newsletter-form .form-control::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .newsletter-form .btn-primary {
        background: #3498db;
        border: none;
        padding: 0.5rem 1rem;
    }

    .newsletter-form .btn-primary:hover {
        background: #2980b9;
    }

    .footer hr {
        border-color: rgba(255, 255, 255, 0.1);
    }

    /* Dark mode support */
    body.dark-mode .footer {
        background: linear-gradient(135deg, #1a1a1a, #2c3e50);
    }

    body.dark-mode .footer::before {
        background: linear-gradient(90deg, #3498db, #2ecc71, #3498db);
    }

    body.dark-mode .footer a:hover {
        color: #2ecc71;
    }

    body.dark-mode .social-links a:hover {
        background: #2ecc71;
    }

    body.dark-mode .newsletter-form .form-control {
        background: rgba(255, 255, 255, 0.05);
    }

    body.dark-mode .newsletter-form .btn-primary {
        background: #2ecc71;
    }

    body.dark-mode .newsletter-form .btn-primary:hover {
        background: #27ae60;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .footer {
            text-align: center;
        }

        .footer h5::after {
            left: 50%;
            transform: translateX(-50%);
        }

        .social-links {
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .footer .col-md-6.text-md-start,
        .footer .col-md-6.text-md-end {
            text-align: center !important;
        }
    }
</style>

<script>
    // Newsletter form submission
    document.querySelector('.newsletter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]').value;
        // Here you would typically send the email to your server
        alert('Thank you for subscribing to our newsletter!');
        this.reset();
    });
</script>
</body>
</html>