<?php
    ini_set('display_errors', 0);
    session_start();
    if (isset($_GET['logout'])) {
        session_unset();
        session_destroy();
        header('Location: index.php');
        exit;
    }
    if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
        $user_id = $_SESSION['user_id'];
    }
    include "checkConnection.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css   ">
    <link rel="stylesheet" href="assets/css/rating.css">
</head>
<body>
    <?php include "NavBar.php";?>
    <!-- Home page -->
    <div class="welcome">
        <h1>Hello <?php if(isset($_SESSION['fullname'])){echo $_SESSION['fullname'];}  ?></h1>
        <h2>Welcome to the Learning Path Creator!</h2>
        <p>Here you can create your own learning path and share it with others!</p>
        <p>Click on the "Create Path" button to get started!</p>
        <a href="createPath.php"><button class="btn">Create Path</button></a>
    </div>
    <div class="AllModules">
        <!-- Diplay all modules from the db -->
        <?php
            $con = checkConnectionDb();

            $stmt = $con->prepare("SELECT module_id, module_title, module_description, rating FROM Module");
            if (!$stmt) {
                $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: " . $con->error;
                error_log($error . "\n", 3, "error.log");
            }
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='module'>";
                    echo    "<h3>" . $row['module_title'] . "</h3>";
                    echo    "<p>" . $row['module_description'] . "</p>";
                    // show the module's objectives and the url for each objective
                    $stmt2 = $con->prepare("SELECT objective_title, objective_url FROM Objective WHERE module_id = ?");
                    if (!$stmt2) {
                        $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: " . $con->error;
                        error_log($error . "\n", 3, "error.log");
                    }
                    $stmt2->bind_param("i", $row['module_id']);
                    if (!$stmt2->execute()) {
                        $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: " . $stmt2->error;
                        error_log($error . "\n", 3, "error.log");
                    }
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    if ($result2->num_rows > 0) {
                        echo "<div class='objectives'>";
                        while ($row2 = $result2->fetch_assoc()) {
                            echo "<a href='" . $row2['objective_url'] . "' target='_blank'>" . $row2['objective_title'] . "</a><br>";
                        }
                        echo "</div>";
                    }
                    echo "<div class='rating'>";
                    // show the upvote and downvote buttons
                    $stmt2 = $con->prepare("SELECT vote FROM UserVotes WHERE user_id = ? AND module_id = ?");
                    $stmt2->bind_param("ii", $user_id, $row['module_id']);
                    $stmt2->execute();
                    $result2 = $stmt2->get_result();
                    if ($result2->num_rows > 0) {
                        if ($result2->fetch_assoc()['vote'] == 'up'){
                            echo "<button onclick=\"vote('up', {$row['module_id']})\" id=\"upvote_{$row['module_id']}\" class=\"selected\"><i class=\"fa-regular fa-thumbs-up\"></i></button>";
                            echo "<button onclick=\"vote('down', {$row['module_id']})\" id=\"downvote_{$row['module_id']}\"><i class=\"fa-regular fa-thumbs-down\"></i></button>";  
                        } else {
                            echo "<button onclick=\"vote('up', {$row['module_id']})\" id=\"upvote_{$row['module_id']}\"><i class=\"fa-regular fa-thumbs-up\"></i></button>";
                            echo "<button onclick=\"vote('down', {$row['module_id']})\" id=\"downvote_{$row['module_id']}\" class=\"selected\"><i class=\"fa-regular fa-thumbs-down\"></i></button>";    
                        }              
                    } else {
                        echo "<button onclick=\"vote('up', {$row['module_id']})\" id=\"upvote_{$row['module_id']}\"><i class=\"fa-regular fa-thumbs-up\"></i></button>";
                        echo "<button onclick=\"vote('down', {$row['module_id']})\" id=\"downvote_{$row['module_id']}\"><i class=\"fa-regular fa-thumbs-down\"></i></button>";                  
                    }
                    echo "<p id=\"currentRating_{$row['module_id']}\">" . number_format($row['rating'], 0) . "</p>";
                    echo "<a href='displayModule.php?moduleId={$row['module_id']}' class=\"btn\"> Learn more</a>";
                    echo "</div>";
                    echo "</div>";
                }
            }
            $con->close();
        ?>
    </div>
        <!-- pop up to prompt the user to log in/sign up if the user haven't -->
    <div id="popup">
        <div class="popup-content">
            <span id="close">&times;</span>
            <h2>Log in or sign up to vote!</h2>
            <a href="LogIn.php">Log in</a>
            <a href="SignUp.php">Sign up</a>
        </div>
    </div>
    <script src="assets/js/vote.js"></script>
    <script src="https://kit.fontawesome.com/8115f5ec82.js" crossorigin="anonymous"></script>
</body>
</html>