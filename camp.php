<?php
session_start();
require 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch all available camps from the database
try {
    $stmt = $conn->prepare("SELECT * FROM camps ORDER BY camp_date DESC");
    $stmt->execute();
    $camps = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching camps: " . $e->getMessage();
}

// Function to check cadet eligibility for camp
function isEligible($cadetId, $conn) {
    try {
        // Calculate attendance record
        $attendanceStmt = $conn->prepare("SELECT COUNT(*) AS attended FROM attendance WHERE cadet_id = ? AND status = 'present'");
        $attendanceStmt->execute([$cadetId]);
        $attendance = $attendanceStmt->fetch(PDO::FETCH_ASSOC);

        // Count achievements
        $achievementsStmt = $conn->prepare("SELECT COUNT(*) AS achievements FROM achievements WHERE cadet_id = ?");
        $achievementsStmt->execute([$cadetId]);
        $achievements = $achievementsStmt->fetch(PDO::FETCH_ASSOC);

        // Fetch rank priority
        $rankStmt = $conn->prepare("SELECT rank_priority FROM cadets WHERE id = ?");
        $rankStmt->execute([$cadetId]);
        $rankPriority = $rankStmt->fetch(PDO::FETCH_ASSOC);

        if ($attendance && $achievements && $rankPriority) {
            // Define eligibility criteria
            return $attendance['attended'] >= 5 && $achievements['achievements'] >= 2 && $rankPriority['rank_priority'] <= 3;
        }
        return false;
    } catch (PDOException $e) {
        echo "Error checking eligibility: " . $e->getMessage();
        return false;
    }
}

// Handle form submission to add a new camp (only for admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $campName = $_POST['camp_name'];
    $location = $_POST['location'];
    $campDetails = $_POST['camp_details'];
    $campDate = $_POST['camp_date'];
    $eligibilityRanks = isset($_POST['eligibility_ranks']) ? implode(', ', $_POST['eligibility_ranks']) : '';
    $eligibilityAchievements = $_POST['eligibility_achievements'];
    $eligibilityAttendance = $_POST['eligibility_attendance'];

    try {
        $stmt = $conn->prepare("INSERT INTO camps (camp_name, location, camp_details, camp_date, eligibility) VALUES (?, ?, ?, ?, ?)");
        $eligibilityDescription = "Ranks: $eligibilityRanks, Achievements: $eligibilityAchievements, Attendance: $eligibilityAttendance%";
        $stmt->execute([$campName, $location, $campDetails, $campDate, $eligibilityDescription]);
        header("Location: camp.php"); // Redirect to avoid form resubmission
        exit();
    } catch (PDOException $e) {
        echo "Error creating camp: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Camps</title>

    <!-- Bootstrap CSS for layout and styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <!-- Google Fonts for better typography -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        .camp-item {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        .camp-item h5 {
            color: #007bff;
        }
        .badge {
            font-size: 0.9em;
        }
        .btn-primary, .btn-success {
            background-color: #FF3A3A; /* Neon Red */
            border-color: #FF3A3A;
        }
        .btn-primary:hover, .btn-success:hover {
            background-color: #FF1A1A;
            border-color: #FF1A1A;
        }
        .form-control {
            border-radius: 25px;
        }
        .form-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <!-- Back Button to Index -->
    <a href="index.php" class="btn btn-secondary back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>

    <h1 class="mb-4 text-center">Available Camps</h1>

    <!-- List of Available Camps -->
    <?php if (!empty($camps)): ?>
        <div class="row">
            <?php foreach ($camps as $camp): ?>
                <div class="col-md-4">
                    <div class="camp-item">
                        <h5 class="mb-1"><?php echo htmlspecialchars($camp['camp_name']); ?></h5>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($camp['location']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($camp['camp_date']); ?></p>
                        <p><strong>Details:</strong> <?php echo htmlspecialchars($camp['camp_details']); ?></p>
                        <p><strong>Eligibility:</strong> <?php echo htmlspecialchars($camp['eligibility']); ?></p>

                        <!-- Eligibility Badge -->
                        <?php if ($isLoggedIn && isEligible($_SESSION['user_id'], $conn)): ?>
                            <span class="badge bg-success">Eligible</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Not Eligible</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">No camps available at the moment.</p>
    <?php endif; ?>

    <!-- Admin Only: Button to Add a New Camp -->
    <?php if ($isAdmin): ?>
        <h2 class="mt-5">Create New Camp</h2>
        <button class="btn btn-primary mb-3" onclick="toggleForm()"><i class="fas fa-plus"></i> Add Camp</button>

        <!-- Form to Add a New Camp -->
        <form action="camp.php" method="POST" id="campForm" style="display: none;">
            <div class="mb-3">
                <label for="camp_name" class="form-label">Camp Name</label>
                <input type="text" name="camp_name" id="camp_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" name="location" id="location" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="camp_details" class="form-label">Camp Details</label>
                <textarea name="camp_details" id="camp_details" class="form-control" required></textarea>
            </div>
            <div class="mb-3">
                <label for="camp_date" class="form-label">Date</label>
                <input type="date" name="camp_date" id="camp_date" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="eligibility_ranks" class="form-label">Eligibility - Ranks</label>
                <select name="eligibility_ranks[]" id="eligibility_ranks" class="form-control" multiple required>
                    <option value="LANCE CORPORAL (L/CPL)">LANCE CORPORAL (L/CPL)</option>
                    <option value="CORPORAL (CPL)">CORPORAL (CPL)</option>
                    <option value="SERGEANT (SGT)">SERGEANT (SGT)</option>
                    <option value="UNDER OFFICER (UO)">UNDER OFFICER (UO)</option>
                    <option value="SENIOR UNDER OFFICER (SUO)">SENIOR UNDER OFFICER (SUO)</option>
                    <option value="ASSOCIATE NCC OFFICER (ANO)">ASSOCIATE NCC OFFICER (ANO)</option>
                    <option value="COMPANY SERGEANT MAJOR (CSM)">COMPANY SERGEANT MAJOR (CSM)</option>
                    <option value="CADET MAJOR (CM)">CADET MAJOR (CM)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="eligibility_achievements" class="form-label">Minimum Achievements</label>
                <input type="number" name="eligibility_achievements" id="eligibility_achievements" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="eligibility_attendance" class="form-label">Minimum Attendance (%)</label>
                <input type="number" name="eligibility_attendance" id="eligibility_attendance" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    <?php endif; ?>
</div>

<script>
    // Toggle visibility of the Add Camp form for admins
    function toggleForm() {
        const form = document.getElementById('campForm');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
</script>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>

</body>
</html>
