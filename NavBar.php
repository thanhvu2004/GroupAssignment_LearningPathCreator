<?php
    if (isset($_SESSION['login_email']) && $_SESSION['loggedIn'] && isset($_SESSION['loggedIn'])) {
        // User is logged in
        echo "<ul>";
        echo "<li><a href=\"Index.php\">Home</a></li>";
        echo "<li><a href=\"createPath.php\">Create Path</a></li>";
        echo "<li style=\"float: right\"><a href=\"Index.php?logout=1\">Log Out</a></li>";
        echo "<li style=\"float: right\"><a href=\"Profile.php\">Profile</a></li>";
        echo "<li style=\"float: right\">
                <form action=\"Index.php\" method=\"get\" id=\"search\">
                    <input type=\"text\" name=\"search\" placeholder=\"Search... (enter multiple keywords with , to seperate)\">
                    <button type=\"submit\"><i class=\"fas fa-search\"></i></button>
                </form>
            </li>";
        echo "</ul>";
    } else {
        // User is not logged in
        echo "<ul>";
        echo "<li><a href=\"Index.php\">Home</a></li>";
        echo "<li style=\"float: right\"><a href=\"SignUp.php\">Sign Up</a></li>";
        echo "<li style=\"float: right\"><a href=\"LogIn.php\">Log In</a></li>";
        echo "<li style=\"float: right\">
                <form action=\"Index.php\" method=\"get\" id=\"search\">
                    <input type=\"text\" name=\"search\" placeholder=\"Search... (enter multiple keywords with , to seperate)\">
                    <button type=\"submit\"><i class=\"fas fa-search\"></i></button>
                </form>
            </li>";
        echo "</ul>";
    }
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header('Location: Index.php');
        exit;
    }