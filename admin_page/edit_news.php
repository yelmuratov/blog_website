<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] != 'admin') {
    echo '<script>alert("You are not authorized to access this page."); window.location = "../login.php";</script>';
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the current news item from the database
    $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
    $sql = "SELECT * FROM news WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $news = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$news) {
        echo "News item not found!";
        exit();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Update the news item
    $id = $_POST['id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $text = $_POST['text'];
    $category_id = $_POST['category_id'];
    $image = $news['image']; // Default to current image

    // Handle image upload if a new image is provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = '../images/';
        $imageFullPath = $uploadDir . $imageName;

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($imageTmpPath, '../images/' . $imageName)) {
            $image = $imageName; // Update image path in the database
        }
    }

    $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
    $sql = "UPDATE news SET title = :title, description = :description, text = :text, category_id = :category_id, image = :image WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':text', $text);
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo '<script>alert("News updated successfully!"); window.location = "index.php";</script>';
    } else {
        echo "Failed to update news!";
    }
} else {
    echo "Invalid request!";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit News</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Edit News</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <input type="hidden" name="id" value="<?php echo $news['id']; ?>">

                            <div class="mb-3">
                                <label for="title" class="form-label">Title:</label>
                                <input type="text" id="title" name="title" class="form-control" value="<?php echo $news['title']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description:</label>
                                <textarea id="description" name="description" class="form-control" rows="3"><?php echo $news['description']; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="text" class="form-label">Text:</label>
                                <textarea id="text" name="text" class="form-control" rows="5" required><?php echo $news['text']; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category ID:</label>
                                <input type="number" id="category_id" name="category_id" class="form-control" value="<?php echo $news['category_id']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Image:</label>
                                <input type="file" id="image" name="image" class="form-control">
                                <?php if ($news['image']): ?>
                                    <img src="../images/<?php echo $news['image']; ?>" alt="Current Image" class="img-thumbnail mt-2" width="150">
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-success">Update News</button>
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