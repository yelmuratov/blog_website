<?php
session_start();

if (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'user') {
    header('Location: user_page/index.php');
} else if (isset($_SESSION['admin']) && $_SESSION['admin']['role'] == 'admin') {
    header('Location: admin_page/index.php');
}

if (isset($_POST['ok'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
    $sql = "SELECT * FROM user WHERE email = '$email'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $user = $stmt->fetch();
    if ($user) {
        if (password_verify($password, $user['password'])) {
            switch ($user['role']) {
                case 'admin':
                    $_SESSION['admin'] = $user;
                    header('Location: admin_page/index.php');
                    break;
                case 'user':
                    $_SESSION['user'] = $user;
                    header('Location: user_page/index.php');
                    break;
            }
        } else {
            $_SESSION['login_error'] = 'Email or password is incorrect';
        }
    } else {
        $_SESSION['login_error'] = 'User not found';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login - SB Admin</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="admin_page/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Additional custom styles -->
    <style>
        body {
            background: linear-gradient(90deg, rgba(2, 0, 36, 1) 0%, rgba(9, 9, 121, 1) 35%, rgba(0, 212, 255, 1) 100%);
        }

        .card {
            border-radius: 10px;
        }

        .bg-login-image {
            background: url('login-background.jpg');
            background-size: cover;
            background-position: center;
            border-radius: 10px 0 0 10px;
        }

        .form-control-user {
            border-radius: 30px;
            padding: 15px 20px;
        }

        .custom-control-label {
            cursor: pointer;
        }

        h1.h4 {
            font-weight: 700;
            color: #3a3b45;
        }

        .card-body {
            padding: 40px;
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                    <div class="ml-4 mt-4" style="font-size: 20px; cursor:pointer">
                <a href="user_page/index.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                    </svg>
                    Back
                </a>
            </div>
                        <!-- Nested Row within Card Body -->
                        <div class="row" style="padding:0 100px;">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <?php
                                    if (isset($_SESSION['login_error'])) {
                                        echo '<div class="alert alert-danger">' . $_SESSION['login_error'] . '</div>';
                                        unset($_SESSION['login_error']);
                                    } else if (isset($_SESSION['register_success'])) {
                                        echo '<div class="alert alert-success">' . $_SESSION['register_success'] . '</div>';
                                        unset($_SESSION['register_success']);
                                    }
                                    ?>

                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form class="user" action="" method="post">
                                        <div class="form-group">
                                            <input type="email" name="email" class="form-control form-control-user" id="exampleInputEmail" aria-describedby="emailHelp" placeholder="Enter Email Address...">
                                        </div>
                                        <div class="form-group">
                                            <input type="password" name="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password">
                                        </div>
                                        <button type="submit" name="ok" class="btn btn-primary btn-user btn-block">
                                            Login
                                        </button>
                                    </form>
                                    <div class="text-center">
                                        <a class="small" href="register.php">
                                            Haven't an account? Register!
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>