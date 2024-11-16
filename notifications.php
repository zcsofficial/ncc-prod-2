<?php
session_start();
require_once 'db.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
if (!$isLoggedIn) {
    header('Location: login.php');
    exit();
}

// Get user notifications
$userId = $_SESSION['user_id'];
$query = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>

    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJQ3LlLRI4PRpI3JpWmH7Eu4w8N6lU6tLg3sUoX8E7mJDh2y9zF4Xq5ZQt6V" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
        }
        .notification-card {
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .notification-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .notification-time {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .empty-message {
            text-align: center;
            margin-top: 30px;
            font-size: 1.2rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="mb-4 d-flex justify-content-between align-items-center">
                    <h2 class="text-center">Notifications</h2>
                    <a href="index.php" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>

                <div class="list-group">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item notification-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-bell text-danger me-3"></i>
                                        <span><?php echo htmlspecialchars($notification['message']); ?></span>
                                    </div>
                                    <small class="notification-time">
                                        <?php echo date("F j, Y, g:i a", strtotime($notification['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-message alert alert-info">You have no notifications at the moment.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- External JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybG1eKNU62or0tW7wV5SBQb1g5I7lPb3JdA5y1pQwv5K9gOPcN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0CgPZG4l6qkQ8GxZ8G2p7jbX+NzVVOV7p5y1pQwv5K9gOPcN" crossorigin="anonymous"></script>
</body>
</html>
