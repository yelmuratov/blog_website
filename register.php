<?php
session_start();

if (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'user') {
    header('Location: user_page/index.php');
} else if (isset($_SESSION['admin']) && $_SESSION['admin']['role'] == 'admin') {
    header('Location: admin_page/index.php');
}

if (isset($_POST['ok'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmation_password = $_POST['confirmation_password'];
    if ($password == $confirmation_password) {

        //check email exist
        $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
        $sql = "SELECT * FROM user WHERE email = '$email'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $user = $stmt->fetch();
        if ($user) {
            $_SESSION['email_exist'] = "Email already exists.";
            header('Location: register.php');
            die();
        }

        $password = password_hash($password, PASSWORD_DEFAULT);
        $conn = new PDO('mysql:host=localhost;dbname=news', 'root', '');
        $sql = "INSERT INTO user (name, email, password) VALUES ('$name', '$email', '$password')";
        $conn->exec($sql);
        $_SESSION['register_success'] = "Registration successful.";
        header('Location: login.php');
    } else {
        $_SESSION['confirm_password_error'] = "Password and confirmation password do not match.";
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

    <title>Register</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="admin_page/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Custom styles for error messages -->
    <style>
        .alert {
            margin: 20px auto;
            padding: 15px;
            max-width: 500px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 14px;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <!-- Nested Row within Card Body -->
                <div class="row">
                    <div class="ml-4 mt-4" style="font-size: 20px; cursor:pointer">
                        <a href="user_page/index.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                        </svg>
                        Back
                        </a>
                    </div>
                    <div class="col-lg-7 d-flex justify-content-center align-items-center mx-auto">
                        <div class="p-5">
                            <?php
                            if (isset($_SESSION['confirm_password_error'])) {
                                echo '<div class="alert alert-danger">' . $_SESSION['confirm_password_error'] . '</div>';
                                unset($_SESSION['confirm_password_error']);
                            } else if (isset($_SESSION['email_exist'])) {
                                echo '<div class="alert alert-danger">' . $_SESSION['email_exist'] . '</div>';
                                unset($_SESSION['email_exist']);
                            }
                            ?>
                            <div class="text-center">
                                <h1 class="h4 text-gray-900 mb-4">Create an Account!</h1>
                            </div>
                            <form class="user" action="" method="post">
                                <div class="form-group">
                                    <input type="text" class="form-control form-control-user" id="exampleFirstName" placeholder="Full Name" name="name" required>
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control form-control-user" id="exampleInputEmail" placeholder="Email Address" name="email" required>
                                </div>
                                <div class="form-group row">
                                    <div class="col-sm-6 mb-3 mb-sm-0">
                                        <input type="password" class="form-control form-control-user" id="exampleInputPassword" placeholder="Password" name="password" required>
                                    </div>
                                    <div class="col-sm-6">
                                        <input type="password" class="form-control form-control-user" id="exampleRepeatPassword" placeholder="Repeat Password" name="confirmation_password" required>
                                    </div>
                                </div>
                                <button type="submit" name="ok" class="btn btn-primary btn-user btn-block">
                                    Register Account
                                </button>
                            </form>
                            <hr>
                            <div class="text-center">
                                <a class="small" href="login.php">Already have an account? Login!</a>
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