<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['carousel_image'])) {
    $image = $_FILES['carousel_image'];

    // Validate and upload the image
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($image["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if file is a valid image
    $check = getimagesize($image["tmp_name"]);
    if ($check === false) {
        echo "File is not an image.";
        exit;
    }

    // Check file size (5MB max)
    if ($image["size"] > 5000000) {
        echo "Sorry, your file is too large.";
        exit;
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        exit;
    }

    // Upload file
    if (move_uploaded_file($image["tmp_name"], $target_file)) {
        // Insert image into the database
        $query = "INSERT INTO carousel_images (image) VALUES (:image)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':image', $target_file);
        $stmt->execute();

        // Redirect to index.php after successful upload
        header('Location: dashboard.php');
        exit;
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>
