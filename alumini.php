<?php
include 'db.php';
session_start();

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Handle testimonial submission
$notificationMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $isAdmin) {
    // Get form data
    $title = $_POST['title'];
    $name = $_POST['name'];
    $rank = $_POST['rank'];
    $description = $_POST['description'];
    
    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageName = time() . "_" . basename($_FILES['image']['name']);
        $targetDirectory = "uploads/";
        $targetFile = $targetDirectory . $imageName;

        // Check if the file is an image
        if (getimagesize($_FILES['image']['tmp_name']) !== false) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image = $imageName;
            } else {
                echo "Error uploading image.";
            }
        } else {
            echo "File is not an image.";
        }
    }

    // Insert testimonial into the database
    if ($title && $name && $rank && $description) {
        try {
            $query = "INSERT INTO testimonials (image, title, name, `rank`, description) 
                      VALUES (:image, :title, :name, :rank, :description)";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':rank', $rank);
            $stmt->bindParam(':description', $description);
            $stmt->execute();

            // Success notification
            $notificationMessage = "Testimonial added successfully!";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Please fill in all fields.";
    }
}

// Fetch testimonials for display
$query = "SELECT * FROM testimonials ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch notifications for the logged-in user
if ($isLoggedIn) {
    $userId = $_SESSION['user_id'];

    // Fetch notifications from the database
    $stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5");
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count unread notifications
    $unreadCount = 0;
    foreach ($notifications as $notification) {
        if (isset($notification['read']) && $notification['read'] == 0) {
            $unreadCount++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumini - NCC Journey</title>

    <!-- External CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        .header {
            background-color: #343a40;
            color: #fff;
            padding: 20px 0;
        }

        .header .navbar-nav .nav-link {
            color: #fff;
            padding: 15px 20px;
        }

        .header .navbar-nav .nav-link:hover {
            background-color: #495057;
            border-radius: 5px;
        }

        h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #343a40;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-title {
            font-weight: 600;
            font-size: 1.25rem;
        }

        .card-text {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .card-body {
            padding: 20px;
        }

        .profile-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            margin-top: -40px;
            margin-bottom: 15px;
        }

        .carousel-item img {
            max-height: 500px;
            object-fit: cover;
            transition: transform 0.3s ease-in-out;
        }

        .carousel-item img.zoomed {
            transform: scale(1.5);
        }

        footer {
            background-color: #343a40;
            color: white;
            padding: 30px 0;
            text-align: center;
            margin-top: auto;
        }

        .footer-icon {
            font-size: 1.5rem;
            margin: 0 10px;
        }

        /* Notification styles */
        .notification {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 9999;
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .notification.show {
            display: block;
            opacity: 1;
        }

        @media (max-width: 767px) {
            h2 {
                font-size: 1.5rem;
            }

            .carousel-item img {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php include('navbar.php'); ?>

<!-- Notification -->
<?php if ($notificationMessage): ?>
    <div id="notification" class="notification show">
        <?php echo $notificationMessage; ?>
    </div>
<?php endif; ?>

<!-- Main Content -->
<div class="container my-5">
    <?php if ($isAdmin): ?>
        <div class="d-flex justify-content-start mb-3">
            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addTestimonialModal">
                <i class="fas fa-plus-circle"></i> Add Alumini
            </button>
        </div>
    <?php endif; ?>

    <!-- Testimonial Section -->
    <section id="testimonials" class="py-5">
        <h2 class="text-center mb-4">Alumini</h2>
        <div class="row">
            <?php foreach ($testimonials as $testimonial): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars(!empty($testimonial['image']) ? "uploads/{$testimonial['image']}" : 'https://via.placeholder.com/400x300'); ?>" alt="Testimonial Image">
                        <div class="card-body">
                            <div class="d-flex justify-content-start">
                                <img src="<?php echo htmlspecialchars(!empty($testimonial['image']) ? "uploads/{$testimonial['image']}" : 'https://via.placeholder.com/80'); ?>" class="profile-img" alt="Profile Image">
                                <div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($testimonial['title']); ?></h5>
                                    <p class="card-text"><?php echo substr(htmlspecialchars($testimonial['description']), 0, 100); ?>...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
<!-- Add Testimonial Modal -->
<div class="modal fade" id="addTestimonialModal" tabindex="-1" aria-labelledby="addTestimonialModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTestimonialModalLabel">Add Alumini</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="testimonials.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" name="title" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" name="name" required>
            </div>
            <div class="mb-3">
                <label for="rank" class="form-label">Rank</label>
                <input type="text" class="form-control" name="rank" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control" name="image">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
      </div>
    </div>
  </div>
</div>
<!-- Footer -->
<footer>
    <div class="container">
        <p>&copy; 2024 NCC Journey. All rights reserved.</p>
    </div>
</footer>

<!-- JavaScript -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/js/bootstrap.bundle.min.js"></script>
<script>
    // Show the notification for 3 seconds
    window.onload = function() {
        const notification = document.getElementById('notification');
        if (notification) {
            setTimeout(function() {
                notification.classList.remove('show');
            }, 3000); // Hide after 3 seconds
        }
    }
</script>

</body>
</html>
