<?php
// Include the database connection file
include('db.php');

// Handle form submission to add achievement with certificate upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit'])) {
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

// Fetch cadets from the database
$cadets_stmt = $conn->query("SELECT id, full_name FROM cadets");
$cadets = $cadets_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch achievements to display
$achievements_stmt = $conn->query("SELECT a.id, a.achievement_name, a.achievement_date, c.full_name, a.certificate FROM achievements a JOIN cadets c ON a.cadet_id = c.id");
$achievements = $achievements_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements</title>
    <!-- Bootstrap CSS for styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center mb-4">Manage Achievements</h1>

    <!-- Show success or error message -->
    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Form to add new achievement -->
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
                    <small class="form-text text-muted">Allowed file formats: JPG, PNG. Max size: 5MB.</small>
                </div>
                <button type="submit" name="submit" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Add Achievement</button>
            </form>
        </div>
    </div>

    <!-- Display Achievements in Cards -->
    <div class="row">
        <?php foreach ($achievements as $achievement): ?>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title"><?php echo htmlspecialchars($achievement['achievement_name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Cadet:</strong> <?php echo htmlspecialchars($achievement['full_name']); ?></p>
                        <p><strong>Date:</strong> <?php echo htmlspecialchars($achievement['achievement_date']); ?></p>
                        <?php if ($achievement['certificate']): ?>
                            <p><strong>Certificate:</strong> <a href="<?php echo htmlspecialchars($achievement['certificate']); ?>" target="_blank">View Certificate</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Bootstrap JS & Font Awesome for icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
