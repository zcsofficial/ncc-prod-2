<?php
// Include the database connection file
require_once 'db.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $dob = $_POST['dob'];
    $rank = $_POST['rank'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];
    $emergency_contact_number = $_POST['emergency_contact_number'];
    $cadet_batch = $_POST['cadet_batch']; // New field for cadet_batch

    // Profile picture upload handling
    $profile_picture = $_FILES['profile_picture']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($profile_picture);
    
    // Default profile picture if not uploaded
    if (empty($profile_picture)) {
        $profile_picture = 'default-profile.png'; // default image name
    } else {
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file);
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        // Start transaction
        $conn->beginTransaction();
        
        // Insert into the users table (username, password, role)
        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        
        // Get the last inserted user ID to link with the cadets table
        $user_id = $conn->lastInsertId();
        
        // Insert into the cadets table (user_id, full_name, dob, `rank`, email, contact number, emergency contact number, cadet_batch, profile_picture)
        $stmt = $conn->prepare("INSERT INTO cadets (user_id, full_name, dob, `rank`, email, contact_number, emergency_contact_number, cadet_batch, profile_picture) 
            VALUES (:user_id, :full_name, :dob, :rank, :email, :contact_number, :emergency_contact_number, :cadet_batch, :profile_picture)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':dob', $dob);
        $stmt->bindParam(':rank', $rank);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contact_number', $contact_number);
        $stmt->bindParam(':emergency_contact_number', $emergency_contact_number);
        $stmt->bindParam(':cadet_batch', $cadet_batch); // Bind the cadet_batch
        $stmt->bindParam(':profile_picture', $profile_picture);
        $stmt->execute();
        
        // Commit the transaction
        $conn->commit();
        
        // Redirect to a success page or show a success message
        echo "Registration successful!";
        header('Location: index.php'); // Redirect
        exit;
    } catch (PDOException $e) {
        // If there is an error, roll back the transaction
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Register</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" name="full_name" id="full_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username (Cadet ID)</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="dob" class="form-label">Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="rank" class="form-label">Rank</label>
                <select name="rank" id="rank" class="form-control">
                    <option value="None" selected>None</option>
                    <option value="ASSOCIATE NCC OFFICER (ANO)">ASSOCIATE NCC OFFICER (ANO)</option>
                    <option value="SENIOR UNDER OFFICER (SUO)">SENIOR UNDER OFFICER (SUO)</option>
                    <option value="UNDER OFFICER (UO)">UNDER OFFICER (UO)</option>
                    <option value="COMPANY SERGEANT MAJOR (CSM)">COMPANY SERGEANT MAJOR (CSM)</option>
                    <option value="COMPANY QUARTER MASTER SERGEANT (CQMS)">COMPANY QUARTER MASTER SERGEANT (CQMS)</option>
                    <option value="SERGEANT (SGT)">SERGEANT (SGT)</option>
                    <option value="CORPORAL (CPL)">CORPORAL (CPL)</option>
                    <option value="LANCE CORPORAL (L/CPL)">LANCE CORPORAL (L/CPL)</option>
                    <option value="CADET (CDT)">CADET (CDT)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select name="role" id="role" class="form-control" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="text" name="contact_number" id="contact_number" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="emergency_contact_number" class="form-label">Emergency Contact Number</label>
                <input type="text" name="emergency_contact_number" id="emergency_contact_number" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="cadet_batch" class="form-label">Batch</label>
                <input type="text" name="cadet_batch" id="cadet_batch" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="profile_picture" class="form-label">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
