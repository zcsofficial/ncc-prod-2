<?php
include 'db.php';
session_start();

// Check if the user is logged in and is an admin
$is_admin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';

// Function to fetch all cadets
function fetchCadets($conn) {
    $stmt = $conn->query("SELECT id, full_name FROM cadets");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to fetch all achievements
function fetchAchievements($conn) {
    $stmt = $conn->query("SELECT a.id, a.achievement_name, a.achievement_date, c.full_name, a.certificate 
                          FROM achievements a 
                          JOIN cadets c ON a.cadet_id = c.id");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submission for adding an achievement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) && $is_admin) {
    $cadet_id = htmlspecialchars($_POST['cadet_id']);
    $achievement_name = htmlspecialchars($_POST['achievement_name']);
    $achievement_date = htmlspecialchars($_POST['achievement_date']);
    $error = null;

    // Handle file upload
    $allowed_formats = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    if ($_FILES['certificate']['error'] === 0) {
        $file_tmp = $_FILES['certificate']['tmp_name'];
        $file_name = pathinfo($_FILES['certificate']['name'], PATHINFO_FILENAME);
        $file_ext = pathinfo($_FILES['certificate']['name'], PATHINFO_EXTENSION);
        $file_type = $_FILES['certificate']['type'];
        $file_size = $_FILES['certificate']['size'];

        // Check file size and format
        if ($file_size <= $max_file_size && in_array($file_type, $allowed_formats)) {
            $upload_dir = 'uploads/';
            $randomized_file_name = $file_name . '_' . uniqid() . '.' . $file_ext;
            $file_path = $upload_dir . $randomized_file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                try {
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
            $error = "File size must not exceed 5 MB and must be in JPG, PNG, or JPEG format.";
        }
    } else {
        $error = "Error uploading the certificate. Please try again.";
    }
}

// Handle delete achievement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete']) && $is_admin) {
    $achievement_id = htmlspecialchars($_POST['achievement_id']);
    try {
        $stmt = $conn->prepare("DELETE FROM achievements WHERE id = ?");
        $stmt->execute([$achievement_id]);
        $message = "Achievement deleted successfully!";
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

$cadets = $is_admin ? fetchCadets($conn) : [];
$achievements = fetchAchievements($conn);
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
    <link rel="stylesheet" href="styles.css">
    <style>
        .card-body button {
            margin-right: 5px;
        }
        .btn {
    color: #fff !important;
    background-color: #007bff !important; /* Or use any other color */
    border: 1px solid #007bff !important;
}

.btn:hover {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
}

    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<section id="achievements">
    <div class="container">
        <h2 class="text-center">Achievements</h2>

        <?php if ($is_admin): ?>
            <button class="btn btn-primary mb-3" id="toggle-add-achievement">Add Achievement</button>
            <div id="add-achievement-form" style="display: none;">
                <form method="POST" action="achievement.php" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="cadet_id" class="form-label">Cadet</label>
                        <select name="cadet_id" class="form-select" required>
                            <option value="" disabled selected>Select Cadet</option>
                            <?php foreach ($cadets as $cadet): ?>
                                <option value="<?php echo $cadet['id']; ?>"><?php echo $cadet['full_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="achievement_name" class="form-label">Achievement Name</label>
                        <input type="text" name="achievement_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="achievement_date" class="form-label">Achievement Date</label>
                        <input type="date" name="achievement_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="certificate" class="form-label">Certificate</label>
                        <input type="file" name="certificate" class="form-control" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-success">Add</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($achievements as $achievement): ?>
                <div class="col-md-4">
                    <div class="card">
                        <img src="<?php echo $achievement['certificate']; ?>" class="card-img-top">
                        <div class="card-body">
                            <h5><?php echo $achievement['achievement_name']; ?></h5>
                            <p>Cadet: <?php echo $achievement['full_name']; ?></p>
                            <p>Date: <?php echo date('F j, Y', strtotime($achievement['achievement_date'])); ?></p>
                            <?php if ($is_admin): ?>
                                <form method="POST" action="achievement.php" style="display: inline;">
                                    <input type="hidden" name="achievement_id" value="<?php echo $achievement['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                                <button class="btn btn-warning btn-sm edit-btn" data-id="<?php echo $achievement['id']; ?>" data-name="<?php echo $achievement['achievement_name']; ?>" data-date="<?php echo $achievement['achievement_date']; ?>">Edit</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Modal for Edit -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editForm" method="POST" action="achievement.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Achievement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="achievement_id" id="editAchievementId">
                    <div class="mb-3">
                        <label for="editAchievementName" class="form-label">Achievement Name</label>
                        <input type="text" name="achievement_name" id="editAchievementName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="editAchievementDate" class="form-label">Achievement Date</label>
                        <input type="date" name="achievement_date" id="editAchievementDate" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="edit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggle-add-achievement').addEventListener('click', function() {
        const form = document.getElementById('add-achievement-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    });

    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const date = this.dataset.date;

            document.getElementById('editAchievementId').value = id;
            document.getElementById('editAchievementName').value = name;
            document.getElementById('editAchievementDate').value = date;

            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });
</script>
</body>
</html>
