<?php
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'db.php'; // Database connection file

try {
    // Fetch all distinct users from the database
    $query = "SELECT DISTINCT u.id, u.username, u.role, u.created_at, c.full_name, c.email 
              FROM users u 
              LEFT JOIN cadets c ON u.id = c.user_id";
    $result = $conn->query($query);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>

    <!-- Bootstrap CSS for responsive layout -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts for typography -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            margin-top: 30px;
            max-width: 1200px;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .actions a {
            margin-right: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .actions a:hover {
            color: #0056b3;
        }
        .btn-add {
            background-color: #28a745;
            color: #fff;
            transition: all 0.2s ease-in-out;
        }
        .btn-add:hover {
            background-color: #218838;
        }
        .navbar {
            background-color: #FF3A3A; /* Neon Red */
        }
        .navbar-brand {
            color: #fff;
        }
        .navbar-nav .nav-link {
            color: #fff;
        }
        .navbar-nav .nav-link:hover {
            color: #FF1A1A;
        }
        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 14px;
            }
            .btn-add {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    
<?php include('navbar.php'); ?>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="my-3">Manage Users</h1>
            <a href="admin_console.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
        </div>

        <!-- Users Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle text-center">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->rowCount() > 0): ?>
                        <?php while ($user = $result->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                <td class="actions">
                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="text-primary"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="text-danger" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash-alt"></i> Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Admin Dashboard. All rights reserved.</p>
    </footer>

    <!-- Bootstrap JavaScript and dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn = null;
?>
