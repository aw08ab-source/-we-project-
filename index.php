<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniTrack</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Moving Banner Styles */
        .moving-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(90deg, #4e73df, #224abe);
            color: white;
            padding: 12px 0;
            font-weight: 600;
            font-size: 1.1rem;
            z-index: 1000;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .banner-content {
            display: inline-block;
            white-space: nowrap;
            padding-left: 100%;
            animation: scrollBanner 25s linear infinite;
        }
        
        @keyframes scrollBanner {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-100%);
            }
        }
        
        /* Photo Gallery Styles */
        .photo-gallery {
            max-width: 800px;
            margin: 40px auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .gallery-slide {
            width: 100%;
            height: 400px;
            object-fit: cover;
            display: none;
        }
        
        .gallery-slide.active {
            display: block;
            animation: fadeIn 1s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0.7; }
            to { opacity: 1; }
        }
        
        .gallery-indicators {
            display: flex;
            justify-content: center;
            margin-top: 15px;
            gap: 10px;
        }
        
        .gallery-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ccc;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .gallery-indicator.active {
            background-color: #4e73df;
        }
        
        /* Adjust main content padding for fixed banner */
        body {
            padding-bottom: 60px;
        }
    </style>
</head>

<body>

<?php
    require_once 'config.php'; // This will auto-setup database
?>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.html">
            <img src="images/logo.png" alt="UniTrack" width="100" height="50">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
                <li class="nav-item"><a class="nav-link" href="courses.php">Courses</a></li>
                <li class="nav-item"><a class="nav-link" href="results.php">Results</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="calculate.html">Calculate GPA</a></li>
                <li class="nav-item"><a class="nav-link" href="Questionnaire.html">Questionnaire</a></li>
                <li class="nav-item"><a class="nav-link" href="funpage.html">Fun Page</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- MAIN IMAGE -->
<div class="container text-center my-4">
    <img src="images/images.png" alt="SQU Image" class="img-fluid rounded shadow">
</div>

<!-- INTRO SECTION -->
<div class="container text-center my-4">
    <h1 class="mb-3">Welcome to Sultan Qaboos University</h1>
    <p>Your future begins here. Explore our programs and resources.</p>
</div>

<!-- PHOTO GALLERY -->
<div class="container">
    <div class="photo-gallery">
        <img src="images/campus1.jpg" alt="University Campus" class="gallery-slide active">
        <img src="images/library.jpg" alt="Library" class="gallery-slide">
        <img src="images/mosque.jpg" alt="Student Life" class="gallery-slide">
        <img src="images/graduation.jpg" alt="Graduation" class="gallery-slide">
        
        <div class="gallery-indicators">
            <div class="gallery-indicator active" data-slide="0"></div>
            <div class="gallery-indicator" data-slide="1"></div>
            <div class="gallery-indicator" data-slide="2"></div>
            <div class="gallery-indicator" data-slide="3"></div>
        </div>
    </div>
</div>

<!-- QUICK LINKS -->
<div class="container text-center">
    <div class="row g-4">
        <div class="col-md-4">
            <a href="courses.html" class="text-decoration-none text-dark">
                <div class="p-4 border rounded bg-white shadow-sm">
                    <h4>Courses</h4>
                    <p>View all available school courses.</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="results.html" class="text-decoration-none text-dark">
                <div class="p-4 border rounded bg-white shadow-sm">
                    <h4>Results</h4>
                    <p>Check the latest student results.</p>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="calculate.html" class="text-decoration-none text-dark">
                <div class="p-4 border rounded bg-white shadow-sm">
                    <h4>calculate GPA</h4>
                    <p>Checkout the gpa calculator.</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- MOVING BANNER -->
<div class="moving-banner">
    <div class="banner-content" id="bannerText">
        <!-- Banner content will be populated by JavaScript -->
    </div>
</div>

<!-- FOOTER -->
<footer class="text-center py-3 mt-5 bg-light border-top">
    © 2025 Al-Tarbiyah Private School. All Rights Reserved.
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Update banner with current date and time
    function updateBanner() {
        const now = new Date();
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        const dateString = now.toLocaleDateString('en-US', options);
        const timeString = now.toLocaleTimeString('en-US');
        
        const bannerText = `Welcome to the UniTrack website! Today is ${dateString}, and the time is ${timeString}`;
        document.getElementById('bannerText').textContent = bannerText;
    }
    
    // Update banner immediately and every second
    updateBanner();
    setInterval(updateBanner, 1000);
    
    // Photo Gallery Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelectorAll('.gallery-slide');
        const indicators = document.querySelectorAll('.gallery-indicator');
        let currentSlide = 0;
        const slideInterval = 3000; // 3 seconds
        
        // Function to show a specific slide
        function showSlide(index) {
            // Hide all slides
            slides.forEach(slide => slide.classList.remove('active'));
            indicators.forEach(indicator => indicator.classList.remove('active'));
            
            // Show the selected slide
            slides[index].classList.add('active');
            indicators[index].classList.add('active');
            
            currentSlide = index;
        }
        
        // Function to show next slide
        function nextSlide() {
            let nextIndex = (currentSlide + 1) % slides.length;
            showSlide(nextIndex);
        }
        
        // Add click events to indicators
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                showSlide(index);
                // Reset the auto-advance timer
                clearInterval(autoAdvance);
                autoAdvance = setInterval(nextSlide, slideInterval);
            });
        });
        
        // Auto-advance slides
        let autoAdvance = setInterval(nextSlide, slideInterval);
        
        // Pause auto-advance on hover
        const gallery = document.querySelector('.photo-gallery');
        gallery.addEventListener('mouseenter', () => {
            clearInterval(autoAdvance);
        });
        
        gallery.addEventListener('mouseleave', () => {
            autoAdvance = setInterval(nextSlide, slideInterval);
        });
    });
</script>

</body>
</html>