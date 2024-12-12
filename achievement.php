<?php
include 'db.php';
session_start();

// Check if the user is logged in and is an admin
$is_admin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';

// Handle form submission to add achievement with certificate upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) && $is_admin) {
    $cadet_id = $_POST['cadet_id'];
    $achievement_name = $_POST['achievement_name'];
    $achievement_date = $_POST['achievement_date'];

    // Handle file upload
    $allowed_formats = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    if ($_FILES['certificate']['error'] == 0) {
        $file_tmp = $_FILES['certificate']['tmp_name'];
        $file_name = $_FILES['certificate']['name'];
        $file_type = $_FILES['certificate']['type'];
        $file_size = $_FILES['certificate']['size'];

        // Check file size and format
        if ($file_size <= $max_file_size && in_array($file_type, $allowed_formats)) {
            // Move the uploaded file to the uploads folder
            $upload_dir = 'uploads/';
            $file_path = $upload_dir . basename($file_name);

            if (move_uploaded_file($file_tmp, $file_path)) {
                try {
                    // Insert achievement details along with the file path
                    $stmt = $conn->prepare("INSERT INTO achievements (cadet_id, achievement_name, achievement_date, certificate) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$cadet_id, $achievement_name, $achievement_date, $file_path]);
                    $message = "Achievement added successfully!";
                } catch (PDOException $e) {
                    $error = "Error: " . $e->getMessage();
                }
            } else {
                $error = "Failed to upload the certificate.";
            }
        } else {
            $error = "File size should not exceed 5 MB and must be JPG, PNG, or JPEG format.";
        }
    }
}

// Fetch cadets from the database (only for admins)
if ($is_admin) {
    $cadets_stmt = $conn->query("SELECT id, full_name FROM cadets");
    $cadets = $cadets_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch achievements to display
$achievements_stmt = $conn->query("SELECT a.id, a.achievement_name, a.achievement_date, c.full_name, a.certificate FROM achievements a JOIN cadets c ON a.cadet_id = c.id");
$achievements = $achievements_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - NCC Journey</title>

    <!-- CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa; /* Light background */
            color: #333; /* Dark text */
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
            width: 80px;
            height: 80px;
        }

        /* Section Titles */
        h2 {
            font-size: 2.2rem;
            font-weight: 600;
            color: #343a40;
            margin-bottom: 20px;
        }

        /* Achievements Section */
        #achievements {
            background-color: #f8f9fa; /* Light background */
            color: #333; /* Dark text */
            padding: 60px 15px;
            text-align: center;
        }

        #achievements .section-title {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .achievement-card {
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
            background-color: #ffffff; /* White background */
        }

        .achievement-card:hover {
            transform: translateY(-5px);
        }

        .card-body {
            color: #333;
        }

        .card-title {
            font-weight: 600;
        }

        .card img {
            object-fit: cover;
            max-height: 250px;
        }

        /* Footer */
        footer {
            background-color: #ffffff; /* White background */
            color: #333; /* Dark text */
            padding: 30px 15px;
            text-align: center;
            margin-top: auto;
            box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
        }

        .footer-icon {
            font-size: 1.5rem;
            margin: 0 10px;
            color: #333;
        }

        .footer-icon:hover {
            color: #007bff; /* Blue hover effect */
        }

        @media (max-width: 767px) {
            #achievements .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<!-- Preloader -->
<div id="preloader">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
        <circle cx="50" cy="50" r="45" stroke="#007bff" stroke-width="5" fill="none" stroke-dasharray="283" stroke-dashoffset="280">
            <animate attributeName="stroke-dashoffset" from="283" to="0" dur="2s" repeatCount="indefinite" />
        </circle>
        <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle" font-size="16" font-family="Arial" fill="#007bff">NCC</text>
    </svg>
</div>

<!-- Navbar -->
<?php include('navbar.php'); ?>

<!-- Achievements Section -->
<section id="achievements">
    <div class="container">
        <h2 class="section-title">Achievements</h2>

        <!-- Show success or error message -->
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Form to add new achievement (only for admins) -->
        <?php if ($is_admin): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title">Add Achievement</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="achievement.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="cadet_id" class="form-label">Cadet</label>
                            <select name="cadet_id" id="cadet_id" class="form-select" required>
                                <option value="" disabled selected>Select Cadet</option>
                                <?php foreach ($cadets as $cadet): ?>
                                    <option value="<?php echo $cadet['id']; ?>"><?php echo $cadet['full_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="achievement_name" class="form-label">Achievement Name</label>
                            <input type="text" name="achievement_name" id="achievement_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="achievement_date" class="form-label">Achievement Date</label>
                            <input type="date" name="achievement_date" id="achievement_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="certificate" class="form-label">Certificate (JPG, PNG, JPEG | Max: 5MB)</label>
                            <input type="file" name="certificate" id="certificate" class="form-control" accept=".jpg,.jpeg,.png" required>
                            <small class="form-text text-muted">Please upload the certificate in JPG, PNG, or JPEG format.</small>
                        </div>
                        <button type="submit" name="submit" class="btn btn-primary">Add Achievement</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <!-- Display existing achievements -->
        <div class="row">
            <?php foreach ($achievements as $achievement): ?>
                <div class="col-md-4">
                    <div class="card achievement-card mb-4">
                        <img src="<?php echo $achievement['certificate']; ?>" class="card-img-top" alt="Certificate Image">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $achievement['achievement_name']; ?></h5>
                            <p class="card-text">Achieved by: <?php echo $achievement['full_name']; ?></p>
                            <p class="card-text">Date: <?php echo date('F j, Y', strtotime($achievement['achievement_date'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="social-icons">
        <a href="#" class="footer-icon"><i class="fab fa-facebook"></i></a>
        <a href="#" class="footer-icon"><i class="fab fa-twitter"></i></a>
        <a href="#" class="footer-icon"><i class="fab fa-instagram"></i></a>
    </div>
    <p>&copy; 2024 NCC Journey. All Rights Reserved.</p>
</footer>

<!-- JS Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

<!-- Preloader hide script -->
<script>
    window.addEventListener('load', function() {
        document.getElementById('preloader').classList.add('hidden');
    });
</script>
</body>
</html>
