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
            background-color: #f4f7fb;
            padding-top: 60px;
        }
        .notification-item {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            transition: transform 0.3s ease-in-out;
        }
        .notification-item:hover {
            transform: translateY(-5px);
        }
        .notification-item .fas {
            color: #dc3545;
        }
        .notification-item .notification-time {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .badge-count {
            background-color: #dc3545;
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 50%;
        }
        .container {
            max-width: 900px;
        }
        .empty-message {
            text-align: center;
            margin-top: 30px;
            font-size: 1.2rem;
            color: #6c757d;
        }
        .list-group {
            padding-left: 0;
        }
        .list-group-item {
            border: none;
        }
        /* Back button style */
        .back-button {
            font-size: 1.1rem;
            color: #007bff;
            margin-top: 20px;
            text-decoration: none;
        }
        .back-button i {
            margin-right: 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            .notification-item {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

    

    <div class="container">
        <h2 class="text-center mb-4">Notifications</h2>
        
        <a href="index.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to Home</a>

        <div class="list-group mt-4">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification-item">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-bell me-2"></i>
                                <span><?php echo htmlspecialchars($notification['message']); ?></span>
                            </div>
                            <div class="notification-time">
                                <?php echo date("F j, Y, g:i a", strtotime($notification['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message alert alert-info">You have no notifications at the moment.</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- External JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybG1eKNU62or0tW7wV5SBQb1g5I7lPb3JdA5y+q9JqWfJFF6f6" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js" integrity="sha384-pzjw8f+ua7Kw1TIq0CgPZG4l6qkQ8GxZ8G2p7jbX+NzVVOV7p5y1pQwv5K9gOPcN" crossorigin="anonymous"></script>
</body>
</html>
