<?php
include 'db.php';
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NCC Blog & Projects - NCC Journey</title>

    <!-- CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.compat.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Preloader */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }

        #preloader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        #preloader svg {
            width: 100px;
            height: 100px;
        }

        /* Section Titles */
        h2 {
            font-size: 2.5rem;
            font-weight: 600;
            color: #343a40;
        }

        /* NCC Cadet Section */
        #ncc-cadets {
            background-color: #1c2331;
            color: white;
            padding: 60px 0;
            text-align: center;
        }

        #ncc-cadets .section-title {
            font-size: 2.8rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        /* Blog Section */
        #blogs {
            padding: 60px 0;
            background-color: #f8f9fa;
        }

        .blog-card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .card-body {
            flex-grow: 1;
        }

        .card-title {
            font-weight: 600;
        }

        /* Consistent Card Sizes */
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-img-top {
            max-height: 300px;
            object-fit: cover;
        }

        /* Footer Styling */
        footer {
            background-color: #343a40;
            color: white;
            padding: 30px 0;
            text-align: center;
        }

        .footer-icon {
            font-size: 1.5rem;
            margin: 0 10px;
            color: white;
        }

        .footer-icon:hover {
            color: #007bff;
        }

        /* Make the page fully responsive */
        @media (max-width: 767px) {
            #ncc-cadets .section-title {
                font-size: 2.5rem;
            }

            .carousel-item img {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>

<!-- Preloader -->
<div id="preloader">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
        <circle cx="50" cy="50" r="45" stroke="#000" stroke-width="5" fill="none" stroke-dasharray="283" stroke-dashoffset="280">
            <animate attributeName="stroke-dashoffset" from="283" to="0" dur="2s" repeatCount="indefinite" />
        </circle>
        <text x="50" y="50" text-anchor="middle" font-size="20" font-family="Arial" fill="#000" dy=".3em">NCC</text>
    </svg>
</div>

<?php include('navbar.php'); ?>

<!-- NCC Cadet Section -->
<section id="ncc-cadets">
    <div class="container">
        <h2 class="section-title">Welcome to the NCC Journey <i class="fas fa-users"></i></h2>
        <p>Our NCC cadets are committed to leadership, discipline, and community service. They are the pride of the nation, marching towards a brighter future with courage and determination.</p>
        <a href="#blogs" class="btn btn-light btn-lg">Discover Our Projects</a>
    </div>
</section>

<!-- Projects Section -->
<section id="blogs" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Blogs <i class="fas fa-project-diagram"></i></h2>
        <div class="row">
            <?php
            // Fetch all blog posts from the database using PDO
            $query = "SELECT posts.id, posts.title, posts.body, posts.image, users.username, posts.created_at 
                      FROM posts JOIN users ON posts.author_id = users.id ORDER BY posts.created_at DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($posts as $post) :
            ?>
                <div class="col-md-4 mb-3 animate__animated animate__fadeIn">
                    <div class="card blog-card">
                        <?php if (!empty($post['image'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" class="card-img-top" alt="Blog Image">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x300" class="card-img-top" alt="Blog Image">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                            <p class="card-text"><?php echo substr(htmlspecialchars($post['body']), 0, 100); ?>...</p>
                            <small class="text-muted">By <?php echo htmlspecialchars($post['username']); ?> on <?php echo date('d M Y', strtotime($post['created_at'])); ?></small>
                            <a href="post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary mt-3">Read More</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Image Carousel Section -->
<section id="carousel" class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Cadets Gallery <i class="fas fa-images"></i></h2>
        <div id="carouselExampleCaptions" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                // Fetch all carousel images from the database using PDO
                $query = "SELECT * FROM carousel_images ORDER BY created_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $activeClass = "active"; // First image should be active

                foreach ($images as $image) :
                    $imagePath = htmlspecialchars($image['image']); // Escape special characters to prevent XSS
                ?>
                    <div class="carousel-item <?php echo $activeClass; ?>">
                        <img src="<?php echo $imagePath; ?>" class="d-block w-100" alt="Carousel Image" onclick="zoomImage(this)">
                    </div>
                <?php
                    $activeClass = ""; // Only the first image should have the 'active' class
                endforeach;
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleCaptions" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>

<!-- Footer Section -->
<footer>
    <div class="container">
        <p>&copy; <?php echo date('Y'); ?> NCC Cadet Blog. All rights reserved.</p>
        <div>
            <a href="#" class="footer-icon"><i class="fab fa-facebook"></i></a>
            <a href="#" class="footer-icon"><i class="fab fa-twitter"></i></a>
            <a href="#" class="footer-icon"><i class="fab fa-instagram"></i></a>
        </div>
    </div>
</footer>

<!-- JS Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<!-- Preloader Hide -->
<script>
    window.addEventListener('load', () => {
        document.getElementById('preloader').classList.add('hidden');
    });
</script>

</body>
</html>
