<?php
include 'db.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all posts for the admin
$query = "SELECT posts.id, posts.title, posts.body, posts.created_at, posts.updated_at, users.username, posts.image
          FROM posts
          JOIN users ON posts.author_id = users.id
          ORDER BY posts.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add Post
if (isset($_POST['add_post'])) {
    $title = $_POST['title'];
    $body = $_POST['body'];
    $author_id = $_SESSION['user_id'];

    // Handling image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageName = time() . '_' . $_FILES['image']['name'];
        $imagePath = 'uploads/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        $image = $imagePath;
    }

    $insert_query = "INSERT INTO posts (title, body, author_id, image) VALUES (:title, :body, :author_id, :image)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':body', $body);
    $stmt->bindValue(':author_id', $author_id);
    $stmt->bindValue(':image', $image);
    $stmt->execute();
    $_SESSION['success_message'] = "Blog post added successfully!";
    header("Location: manage_blogs.php");
    exit();
}

// Edit Post
if (isset($_GET['edit'])) {
    $post_id = $_GET['edit'];
    $edit_query = "SELECT * FROM posts WHERE id = :post_id";
    $stmt = $conn->prepare($edit_query);
    $stmt->bindValue(':post_id', $post_id);
    $stmt->execute();
    $post_to_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (isset($_POST['update_post'])) {
    $post_id = $_POST['post_id'];
    $title = $_POST['title'];
    $body = $_POST['body'];
    $image = $post_to_edit['image']; // Keep existing image

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageName = time() . '_' . $_FILES['image']['name'];
        $imagePath = 'uploads/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        $image = $imagePath;
    }

    $update_query = "UPDATE posts SET title = :title, body = :body, image = :image WHERE id = :post_id";
    $stmt = $conn->prepare($update_query);
    $stmt->bindValue(':title', $title);
    $stmt->bindValue(':body', $body);
    $stmt->bindValue(':image', $image);
    $stmt->bindValue(':post_id', $post_id);
    $stmt->execute();
    $_SESSION['success_message'] = "Blog post updated successfully!";
    header("Location: manage_blogs.php");
    exit();
}

// Delete Post
if (isset($_GET['delete'])) {
    $post_id = $_GET['delete'];
    $delete_query = "DELETE FROM posts WHERE id = :post_id";
    $stmt = $conn->prepare($delete_query);
    $stmt->bindValue(':post_id', $post_id);
    $stmt->execute();
    $_SESSION['success_message'] = "Blog post deleted successfully!";
    header("Location: manage_blogs.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Blogs - NCC Journey</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Roboto', sans-serif;
        }

        .card-header {
            background-color: #17a2b8;
            color: white;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .btn-custom {
            background-color: #28a745;
            color: white;
        }

        .btn-custom:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-primary {
            background-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }

        .card-body {
            background-color: #f8f9fa;
        }

        .form-label {
            font-weight: bold;
        }

        /* Ensure images are not too large */
        .post-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }

        .back-button {
            font-size: 18px;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <?php include('navbar.php'); ?>

    <div class="container mt-5">

        <!-- Back Button -->
        <a href="admin_console.php" class="btn btn-secondary back-button">
            <i class="fas fa-arrow-left"></i> Back to Admin Console
        </a>

        <h3 class="text-center mt-3">Manage Blogs</h3>

        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }

        if (isset($_SESSION['error_message'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>

        <!-- Blog Posts Table -->
        <div class="card">
            <div class="card-header">
                <h4>All Blog Posts</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Created At</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post) { ?>
                            <tr>
                                <td><?php echo $post['id']; ?></td>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars($post['username']); ?></td>
                                <td><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></td>
                                <td>
                                    <?php if ($post['image']) { ?>
                                        <img src="<?php echo $post['image']; ?>" class="post-image" alt="Post Image">
                                    <?php } else { ?>
                                        No Image
                                    <?php } ?>
                                </td>
                                <td>
                                    <a href="manage_blogs.php?edit=<?php echo $post['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="manage_blogs.php?delete=<?php echo $post['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>

</html>
