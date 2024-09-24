<?php
session_start();

// Database connection
try {
  $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
  die();
}

// Handle like and dislike actions
if (isset($_POST['action']) && isset($_POST['news_id'])) {
  if (!isset($_SESSION['user'])) {
    // Redirect to login page if not logged in
    header('Location: ../login.php');
    exit;
  }

  $news_id = (int)$_POST['news_id'];
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
  header('Location: ' . $_SERVER['PHP_SELF']); // Redirect to avoid form resubmission
  exit;
}

// Pagination setup
$itemsPerPage = 4;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Category filter
$categoryFilter = isset($_GET['category_id']) ? $_GET['category_id'] : null;

// Fetch categories
$categories = [];
$categorySql = "SELECT * FROM categories";
$categoryStmt = $conn->prepare($categorySql);
$categoryStmt->execute();
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch news with pagination and optional category filter
$news = [];
$sql = "SELECT n.*, 
           SUM(CASE WHEN l.value = 1 THEN 1 ELSE 0 END) AS likes, 
           SUM(CASE WHEN l.value = -1 THEN 1 ELSE 0 END) AS dislikes 
        FROM news n 
        LEFT JOIN likes l ON n.id = l.news_id";
if ($categoryFilter) {
  $sql .= " WHERE n.category_id = :category_id";
}
$sql .= " GROUP BY n.id LIMIT :offset, :itemsPerPage";
$stmt = $conn->prepare($sql);
if ($categoryFilter) {
  $stmt->bindParam(':category_id', $categoryFilter, PDO::PARAM_INT);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itemsPerPage', $itemsPerPage, PDO::PARAM_INT);
$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total number of records for pagination calculation
$countSql = "SELECT COUNT(*) FROM news";
if ($categoryFilter) {
  $countSql .= " WHERE category_id = :category_id";
}
$countStmt = $conn->prepare($countSql);
if ($categoryFilter) {
  $countStmt->bindParam(':category_id', $categoryFilter, PDO::PARAM_INT);
}
$countStmt->execute();
$totalItems = $countStmt->fetchColumn();
$totalPages = ceil($totalItems / $itemsPerPage);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Blog - Selecao Bootstrap Template</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <style>
    /* Custom styles to make all cards the same height */
    .card {
      height: 100%;
    }

    .card-img-top {
      object-fit: cover;
      height: 200px;
    }

    .card-title {
      min-height: 56px;
      /* Adjust to match the typical height of a two-line title */
    }

    .card-text {
      min-height: 100px;
      /* Adjust to provide space for the content */
    }

    .card-body {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
  </style>
</head>

<body class="blog-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="index.html" class="logo d-flex align-items-center">
        <h1 class="sitename">Selecao</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="../user_page/#hero">Home</a></li>
          <li><a href="../user_page/#about">About</a></li>
          <li><a href="../user_page/#services">Services</a></li>
          <li><a href="../user_page/#portfolio">Portfolio</a></li>
          <li><a href="../user_page/#team">Team</a></li>
          <li><a href="blog.php" class="active">Blog</a></li>
          <li><a href="../user_page/#contact">Contact</a></li>
          <?php if (isset($_SESSION['user'])) { ?>
            <div class="dropdown">
              <a class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Profile</a>
              <ul class="dropdown-menu">
                <li>
                  <p class="dropdown-item" style="cursor: pointer;">Name: <?php echo $_SESSION['user']['name']; ?></p>
                </li>
                <li>
                  <p class="dropdown-item" style="cursor: pointer;">Email: <?php echo $_SESSION['user']['email']; ?></p>
                </li>
                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
              </ul>
            </div>
          <?php } else {
            echo '<li><a href="../login.php">Login</a></li>';
            echo '<li><a href="../register.php">Register</a></li>';
          } ?>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

    </div>
  </header>

  <main class="main">

    <!-- Page Title -->
    <div class="page-title dark-background">
      <div class="container position-relative">
        <h1>Blog</h1>
        <p>Esse dolorum voluptatum ullam est sint nemo et est ipsa porro placeat quibusdam quia assumenda numquam molestias.</p>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current">Blog</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Blog Posts Section -->
    <section id="blog-posts" class="blog-posts section">
      <div class="container">
        <!-- Category Filter Dropdown -->
        <form method="GET" action="" class="mb-4">
          <select name="category_id" class="form-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
              <option value="<?= $category['id']; ?>" <?= isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                <?= $category['name']; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </form>
        <div class="row gy-4">
          <?php if (empty($news)): ?>
            <p class="text-center">No news found for this category.</p>
          <?php else: ?>
            <?php foreach ($news as $item): ?>
              <div class="col-lg-4">
                <article class="card shadow-sm">
                  <img src="../images/<?= htmlspecialchars($item['image']); ?>" alt="News Image" class="card-img-top img-fluid">
                  <div class="card-body">
                    <h5 class="card-title">
                      <a href="blog-details.php?id=<?= htmlspecialchars($item['id']); ?>">
                        <?= htmlspecialchars($item['title']); ?>
                      </a>
                    </h5>
                    <p class="card-text"><?= substr(htmlspecialchars($item['text']), 0, 100); ?>...</p>
                    <a href="blog-details.php?id=<?= htmlspecialchars($item['id']); ?>" class="btn btn-primary">Read More</a>
                  </div>
                </article>
              </div>

            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </section><!-- /Blog Posts Section -->

    <!-- Blog Pagination Section -->
    <section id="blog-pagination" class="blog-pagination section">
      <div class="container">
        <?php if ($totalPages > 1): ?>
          <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
              <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $categoryFilter ? '&category_id=' . $categoryFilter : ''; ?>" aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                  <span class="sr-only">Previous</span>
                </a>
              </li>
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                  <a class="page-link" href="?page=<?php echo $i; ?><?php echo $categoryFilter ? '&category_id=' . $categoryFilter : ''; ?>"><?php echo $i; ?></a>
                </li>
              <?php endfor; ?>
              <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $categoryFilter ? '&category_id=' . $categoryFilter : ''; ?>" aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                  <span class="sr-only">Next</span>
                </a>
              </li>
            </ul>
          </nav>
        <?php endif; ?>
      </div>
    </section><!-- /Blog Pagination Section -->

  </main>

  <footer id="footer" class="footer dark-background">
    <div class="container">
      <h3 class="sitename">Selecao</h3>
      <p>Et aut eum quis fuga eos sunt ipsa nihil. Labore corporis magni eligendi fuga maxime saepe commodi placeat.</p>
      <div class="social-links d-flex justify-content-center">
        <a href=""><i class="bi bi-twitter"></i></a>
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

  <!-- Preloader -->
  <div id="preloader"></div>

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