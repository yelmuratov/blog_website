<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page if not logged in
    header('Location: ../login.php');
    exit;
}

// Database connection
try {
    $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}

// Check if the form is submitted and all necessary data is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['news_id'], $_POST['comment'])) {
    $news_id = (int)$_POST['news_id'];
    $user_id = $_SESSION['user']['id'];
    $comment_text = trim($_POST['comment']);

    // Validate comment text
    if (empty($comment_text)) {
        // Redirect back with an error message
        $_SESSION['error'] = 'Comment cannot be empty.';
        header('Location: blog-details.php?id=' . $news_id);
        exit;
    }

    // Insert the comment into the database
    try {
        $sql = "INSERT INTO comments (news_id, user_id, text, created_at) VALUES (:news_id, :user_id, :text, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':news_id', $news_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':text', $comment_text, PDO::PARAM_STR);
        $stmt->execute();

        // Redirect back to the news details page
        header('Location: blog-details.php?id=' . $news_id);
        exit;

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        die();
    }

} else {
    // Redirect back if the form was not submitted properly
    header('Location: blog.php');
    exit;
}
?>
