<?php
include 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user and cadet details
$query = "
    SELECT u.username, u.role, 
           c.full_name, c.dob, c.rank, c.email, c.contact_number, 
           c.emergency_contact_number
    FROM users u 
    JOIN cadets c ON u.id = c.user_id 
    WHERE u.id = :user_id
";
$stmt = $conn->prepare($query);
$stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if user data exists
if (!$user) {
    echo "User profile not found.";
    exit();
}

// Fetch attendance data for the logged-in user
$attendance_query = "
    SELECT e.event_name, e.event_date, a.status 
    FROM attendance a 
    JOIN events e ON a.event_id = e.id 
    WHERE a.cadet_id IN (SELECT id FROM cadets WHERE user_id = :user_id)
    ORDER BY e.event_date DESC
";
$attendance_stmt = $conn->prepare($attendance_query);
$attendance_stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
$attendance_stmt->execute();
$attendance_result = $attendance_stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = $isLoggedIn && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - NCC Journey</title>
    <!-- External Libraries for Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            background-color: #f4f7fc;
            font-family: 'Poppins', sans-serif;
        }

        .profile-header {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 30px;
        }

        .profile-header h3 {
            font-weight: 600;
            color: #2c3e50;
        }

        .badge-role {
            background-color: #4e73df;
            color: white;
            font-size: 14px;
            padding: 5px 10px;
            border-radius: 50px;
        }

        .profile-detail .row p {
            font-size: 16px;
            color: #2c3e50;
        }

        .card-table {
            margin-top: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            vertical-align: middle;
            text-align: center;
        }

        .table td {
            font-size: 14px;
        }

        .btn-edit-profile {
            background-color: #28a745;
            color: white;
            font-size: 14px;
            padding: 10px 20px;
            border-radius: 5px;
        }

        .btn-edit-profile:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .profile-header {
                padding: 20px;
            }

            .card-table {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container mt-5">
    <div class="row">
        <!-- Profile Header -->
        <div class="col-lg-4 col-md-5">
            <div class="profile-header text-center">
                <h3 class="card-title"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p class="text-muted">Rank: <?php echo htmlspecialchars($user['rank']); ?></p>
                <span class="badge badge-role"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span>
                <a href="edit_profile.php" class="btn-edit-profile mt-3"><i class="fas fa-edit"></i> Edit Profile</a>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="col-lg-8 col-md-7">
            <div class="card profile-detail p-4">
                <h4 class="mb-4">Profile Information</h4>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($user['dob']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($user['contact_number']); ?></p>
                    </div>
                </div>

                <!-- Attendance History -->
                <div class="card card-table p-3">
                    <h5 class="mb-4">Attendance History</h5>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Event Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($attendance_result): ?>
                                <?php foreach ($attendance_result as $attendance): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($attendance['event_name']); ?></td>
                                        <td><?php echo htmlspecialchars($attendance['event_date']); ?></td>
                                        <td class="<?php echo ($attendance['status'] == 'present') ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo ucfirst($attendance['status']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3">No attendance records found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- External JS Libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0-alpha1/js/bootstrap.bundle.min.js"></script>

</body>
</html>
