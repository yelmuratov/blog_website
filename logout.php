<?php
    session_start(); // Start the session
    if(isset($_SESSION['user'])){
        unset($_SESSION['user']); // Unset the session variable
        header('Location: user_page/index.php'); // Redirect to the login page
    }else if(isset($_SESSION['admin'])){
        unset($_SESSION['admin']); // Unset the session variable
        header('Location: login.php'); // Redirect to the login page
    }
    exit(); // Ensure no further code is executed
?>