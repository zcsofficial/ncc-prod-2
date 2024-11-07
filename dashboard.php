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
    <title>Admin Dashboard - NCC Journey</title>

    <!-- External CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #343a40;
        }

        .carousel-item img {
            max-height: 500px;
            object-fit: cover;
            transition: transform 0.3s ease-in-out;
        }

        .carousel-item img.zoomed {
            transform: scale(1.5);
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card-title {
            font-weight: 600;
        }

        .card-body {
            padding: 20px;
        }

        .card-text {
            color: #6c757d;
        }

        .modal-header, .modal-footer {
            border: none;
        }

        footer {
            background-color: #343a40;
            color: white;
            padding: 30px 0;
            text-align: center;
        }

        .footer-icon {
            font-size: 1.5rem;
            margin: 0 10px;
        }

        .zoomed {
            transform: scale(1.5);
            z-index: 1;
        }

        @media (max-width: 767px) {
            .carousel-item img {
                max-height: 300px;
            }

            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container my-5">
    <?php if ($isAdmin): ?>
        <!-- Button to open the blog post creation modal -->
        <div class="d-flex justify-content-start mb-3">
            <button type="button" class="btn btn-primary me-2 animate__animated animate__fadeIn" data-bs-toggle="modal" data-bs-target="#addBlogModal">
                <i class="fas fa-plus-circle"></i> Add New Blog Post
            </button>
            <!-- Button to open the image upload modal -->
            <button type="button" class="btn btn-secondary animate__animated animate__fadeIn" data-bs-toggle="modal" data-bs-target="#addImageModal">
                <i class="fas fa-image"></i> Upload New Image for Carousel
            </button>
        </div>
    <?php endif; ?>

    <!-- Blog Section -->
    <section id="blogs" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Our Projects</h2>
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
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <?php if (!empty($post['image'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($post['image']); ?>" class="card-img-top" alt="Blog Image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x300" class="card-img-top" alt="Blog Image">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <p class="card-text"><?php echo substr(htmlspecialchars($post['body']), 0, 100); ?>...</p>
                                <small class="text-muted">By <?php echo htmlspecialchars($post['username']); ?> on <?php echo date('d M Y', strtotime($post['created_at'])); ?></small>
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
            <h2 class="text-center mb-4">Image Carousel</h2>
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
                        $imagePath = htmlspecialchars($image['image']);
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
</div>

<!-- Modal for Adding Blog Post (Admin Only) -->
<div class="modal fade" id="addBlogModal" tabindex="-1" aria-labelledby="addBlogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="add_blog.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBlogModalLabel">Add New Blog Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="body" class="form-label">Body</label>
                        <textarea class="form-control" id="body" name="body" rows="5" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Upload Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for Image Upload (Admin Only) -->
<div class="modal fade" id="addImageModal" tabindex="-1" aria-labelledby="addImageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="add_image.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="addImageModalLabel">Upload Image for Carousel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="carousel_image" class="form-label">Upload Image</label>
                        <input type="file" class="form-control" id="carousel_image" name="carousel_image" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload Image</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer Section -->
<footer>
    <p>&copy; 2024 NCC Blog. All Rights Reserved.</p>
    <div>
        <a href="#" class="footer-icon text-white"><i class="fab fa-facebook"></i></a>
        <a href="#" class="footer-icon text-white"><i class="fab fa-twitter"></i></a>
        <a href="#" class="footer-icon text-white"><i class="fab fa-instagram"></i></a>
    </div>
</footer>

<!-- Bootstrap and JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/js/bootstrap.bundle.min.js"></script>

<script>
    function zoomImage(image) {
        image.classList.toggle('zoomed');
    }
</script>

</body>
</html>
