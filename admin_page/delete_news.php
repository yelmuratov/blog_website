<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin']['role'] != 'admin') {
    echo '<script>alert("You are not authorized to access this page."); window.location = "../login.php";</script>';
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Delete the news item from the database
    $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
    $sql = "DELETE FROM news WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo '<script>alert("News deleted successfully!"); window.location = "index.php";</script>';
    } else {
        echo "Failed to delete news!";
    }
} else {
    echo "Invalid request!";
    exit();
}
?>
