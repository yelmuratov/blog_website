<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] != 'admin') {
    echo '<script>alert("You are not authorized to access this page."); window.location = "../login.php";</script>';
    exit();
}

// Database connection
$conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');

// Fetch categories for the dropdown
$categories = [];
try {
    $sql = "SELECT * FROM categories";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $text = $_POST['text'];
    $category_id = $_POST['category_id'];

    // Handle the image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = basename($_FILES['image']['name']);
        $image_path = '../images/' . $image_name;
        move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
    } else {
        $image_name = null;
    }

    $sql = "INSERT INTO news (title, description, text, category_id, image) VALUES (:title, :description, :text, :category_id, :image)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':text', $text);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':image', $image_name);

    if ($stmt->execute()) {
        echo '<script>alert("Article added successfully!"); window.location = "index.php";</script>';
    } else {
        echo "Failed to add article!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Article</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Add New Article</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="add_article.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title:</label>
                            <input type="text" id="title" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description:</label>
                            <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="text" class="form-label">Text:</label>
                            <textarea id="text" name="text" class="form-control" rows="5" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category:</label>
                            <select id="category_id" name="category_id" class="form-select" required>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Image:</label>
                            <input type="file" id="image" name="image" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-success">Add Article</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
