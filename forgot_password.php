<?php
require_once 'db.php';

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists in the cadets table
    $stmt = $conn->prepare("SELECT users.id AS user_id, cadets.email 
                            FROM cadets 
                            JOIN users ON cadets.user_id = users.id 
                            WHERE cadets.email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Generate a reset token
        $token = bin2hex(random_bytes(32));
        $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) 
                                VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))
                                ON DUPLICATE KEY UPDATE token = :token, expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR)");
        $stmt->bindParam(':user_id', $user['user_id']);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        // Send email (pseudo-code)
        $resetLink = "http://yourdomain.com/reset_password.php?token=" . $token;
        mail($email, "Password Reset Request", "Click the following link to reset your password: $resetLink");

        $message = "success"; // Success message for SweetAlert
    } else {
        $message = "error"; // Error message for SweetAlert
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - NCC Cadets</title>

    <!-- External CSS & Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .forgot-password-container {
            max-width: 420px;
            width: 100%;
            margin: 20px;
            padding: 30px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .forgot-password-container h2 {
            text-align: center;
            font-size: 2rem;
            color: #007bff;
            margin-bottom: 30px;
        }
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 12px;
        }
        .btn-submit {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px;
            width: 100%;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        .icon {
            font-size: 1.2rem;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="container forgot-password-container">
        <a href="login.php" class="btn btn-light mb-3">
            <i class="fas fa-arrow-left"></i> Back to Login
        </a>
        <h2><i class="fas fa-unlock-alt icon"></i> Forgot Password</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Enter your registered email" required>
            </div>
            <button type="submit" class="btn btn-submit">Send Reset Link</button>
        </form>
    </div>

    <script>
        <?php if ($message == "success"): ?>
        Swal.fire({
            icon: 'success',
            title: 'Email Sent',
            text: 'A password reset link has been sent to your email.',
            confirmButtonColor: '#007bff'
        });
        <?php elseif ($message == "error"): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No account found with this email.',
            confirmButtonColor: '#007bff'
        });
        <?php endif; ?>
    </script>
</body>
</html>
