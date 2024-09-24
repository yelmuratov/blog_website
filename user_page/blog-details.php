<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    // Redirect to login page if not logged in
    header('Location: ../login.php');
    exit;
}

$users = [];

try{
  $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
  $sql = "SELECT * FROM user";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}catch(PDOException $e){
  echo "Error: " . $e->getMessage();
  die();
}

// Database connection
try {
    $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    die();
}

// Get the news ID from the URL
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id === 0) {
    // Redirect to blog page if no valid ID is provided
    header('Location: blog.php');
    exit;
}

// Handle like and dislike actions
if (isset($_POST['action'])) {
    $user_id = $_SESSION['user']['id'];
    $action = $_POST['action'];
    $value = ($action === 'like') ? 1 : -1;

    // Check if the user has already liked or disliked this news
    $sql = "SELECT * FROM likes WHERE news_id = :news_id AND user_id = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':news_id', $news_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $existingLike = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existingLike) {
        // Update the existing record
        $sql = "UPDATE likes SET value = :value WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':value', $value, PDO::PARAM_INT);
        $stmt->bindParam(':id', $existingLike['id'], PDO::PARAM_INT);
    } else {
        // Insert a new record
        $sql = "INSERT INTO likes (news_id, user_id, value) VALUES (:news_id, :user_id, :value)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':news_id', $news_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':value', $value, PDO::PARAM_INT);
    }

    $stmt->execute();
    header('Location: blog-details.php?id=' . $news_id); // Redirect to avoid form resubmission
    exit;
}

//last three news items
$recentNews = [];

try {
  $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
  $sql = "SELECT * FROM news ORDER BY id DESC LIMIT 3";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $recentNews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
  die();
}

// Fetch the news details
$sql = "SELECT n.*, 
           (SELECT COUNT(*) FROM likes WHERE news_id = n.id AND value = 1) AS likes, 
           (SELECT COUNT(*) FROM likes WHERE news_id = n.id AND value = -1) AS dislikes 
        FROM news n 
        WHERE n.id = :id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':id', $news_id, PDO::PARAM_INT);
$stmt->execute();
$newsItem = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$newsItem) {
    // Redirect to blog page if news not found
    header('Location: blog.php');
    exit;
}

// Fetch categories with post counts
$categorySql = "SELECT c.id, c.name, (SELECT COUNT(*) FROM news WHERE category_id = c.id) AS count 
                FROM categories c";
$categoryStmt = $conn->prepare($categorySql);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the last three news items

// Fetch comments for the current news item
$conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
$sql = "SELECT * FROM comments WHERE news_id = :news_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':news_id', $news_id, PDO::PARAM_INT);
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Blog Details - Selecao Bootstrap Template</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="blog-details-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <h1 class="sitename">Selecao</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero">Home</a></li>
          <li><a href="#about">About</a></li>
          <li><a href="#services">Services</a></li>
          <li><a href="#portfolio">Portfolio</a></li>
          <li><a href="#team">Team</a></li>
          <li><a href="blog.php">Blog</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

    </div>
  </header>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title dark-background">
      <div class="container position-relative">
        <h1><?= htmlspecialchars($newsItem['title']); ?></h1>
        <p><?= substr(htmlspecialchars($newsItem['text']), 0, 100); ?></p>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li class="current"><?= htmlspecialchars($newsItem['title']); ?></li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <div class="container">
      <div class="row">

        <div class="col-lg-8">

          <!-- Blog Details Section -->
          <section id="blog-details" class="blog-details section">
            <div class="container">

              <article class="article">

                <div class="post-img">
                  <img src="../images/<?= htmlspecialchars($newsItem['image']); ?>" alt="News Image" class="img-fluid">
                </div>

                <h2 class="title"><?= htmlspecialchars($newsItem['title']); ?></h2>
                <div class="content">
                  <p><?= nl2br(htmlspecialchars($newsItem['text'])); ?></p>
                </div>

                <!-- Like and Dislike Section -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <form method="POST" action="">
                        <input type="hidden" name="news_id" value="<?= $news_id; ?>">
                        <button type="submit" name="action" value="like" class="btn btn-outline-success">
                            <i class="bi bi-hand-thumbs-up"></i> Like (<?= $newsItem['likes']; ?>)
                        </button>
                        <button type="submit" name="action" value="dislike" class="btn btn-outline-danger">
                            <i class="bi bi-hand-thumbs-down"></i> Dislike (<?= $newsItem['dislikes']; ?>)
                        </button>
                    </form>
                </div><!-- End Like and Dislike Section -->

              </article>

              <!-- Comments Section -->
              <section id="blog-comments" class="blog-comments section mt-4">
                <div class="container">
                    <h4 class="comments-count"><?= count($comments); ?> Comments</h4>

                    <?php foreach ($comments as $comment): ?>
                        <div class="comment">
                            <div class="d-flex">
                                <div class="comment-img"><img src="assets/img/user-avatar.png" alt=""></div>
                                <div>
                                    <h4>
                                      <?php
                                        foreach ($users as $user) {
                                            if ($user['id'] == $comment['user_id']) {
                                                echo htmlspecialchars($user['name']);
                                                break;
                                            }
                                        }
                                      ?>
                                    </h4>
                                    <time datetime="<?= htmlspecialchars($comment['created_at']); ?>"><?= date('M d, Y', strtotime($comment['created_at'])); ?></time>
                                    <p><?= nl2br(htmlspecialchars($comment['text'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <!-- Comment Form -->
                    <section id="comment-form" class="comment-form section mt-4">
                        <div class="container">
                            <form action="submit_comment.php" method="POST">
                                <input type="hidden" name="news_id" value="<?= $news_id; ?>">
                                <div class="row">
                                    <div class="col form-group">
                                        <textarea name="comment" class="form-control" placeholder="Your Comment*"></textarea>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary">Post Comment</button>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
              </section><!-- /Comments Section -->

            </div>
          </section><!-- /Blog Details Section -->

        </div>

        <div class="col-lg-4 sidebar">

          <div class="widgets-container">

            <!-- Categories Widget -->
            <div class="categories-widget widget-item">
              <h3 class="widget-title">Categories</h3>
              <ul class="mt-3">
                <?php foreach ($categories as $category): ?>
                    <li><a href="blog.php?category_id=<?= htmlspecialchars($category['id']); ?>">
                        <?= htmlspecialchars($category['name']); ?> 
                        <span>(<?= htmlspecialchars($category['count']); ?>)</span></a></li>
                <?php endforeach; ?>
              </ul>
            </div><!--/Categories Widget -->

            <!-- Recent Posts Widget -->
            <div class="recent-posts-widget widget-item">
              <h3 class="widget-title">Recent Posts</h3>
              <?php foreach ($recentNews as $recent): ?>
                <div class="post-item">
                  <h4><a href="blog-details.php?id=<?= $recent['id']; ?>"><?= htmlspecialchars($recent['title']); ?></a></h4>
                </div><!-- End recent post item-->
              <?php endforeach; ?>
            </div><!--/Recent Posts Widget -->

          </div>

        </div>

      </div>
    </div>

  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container">
      <h3 class="sitename">Selecao</h3>
      <p>Et aut eum quis fuga eos sunt ipsa nihil. Labore corporis magni eligendi fuga maxime saepe commodi placeat.</p>
      <div class="social-links d-flex justify-content-center">
        <a href=""><i class="bi bi-twitter-x"></i></a>
        <a href=""><i class="bi bi-facebook"></i></a>
        <a href=""><i class="bi bi-instagram"></i></a>
        <a href=""><i class="bi bi-skype"></i></a>
        <a href=""><i class="bi bi-linkedin"></i></a>
      </div>
      <div class="container">
        <div class="copyright">
          <span>Copyright</span> <strong class="px-1 sitename">Selecao</strong> <span>All Rights Reserved</span>
        </div>
        <div class="credits">
          Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
