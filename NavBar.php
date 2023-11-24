<?php
    if (isset($_SESSION['login_email']) && $_SESSION['loggedIn'] && isset($_SESSION['loggedIn'])) {
        // User is logged in
        echo "<ul>";
        echo "<li><a href=\"index.php\">Home</a></li>";
        echo "<li style=\"float: right\"><a href=\"index.php?logout=1\">Log Out</a></li>";
        echo "<li style=\"float: right\"><a href=\"Profile.php\">Profile</a></li>";
        echo "</ul>";
    } else {
        // User is not logged in
        echo "<ul>";
        echo "<li><a href=\"index.php\">Home</a></li>";
        echo "<li style=\"float: right\"><a href=\"SignUp.php\">Sign Up</a></li>";
        echo "<li style=\"float: right\"><a href=\"LogIn.php\">Log In</a></li>";
        echo "</ul>";
    }
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }