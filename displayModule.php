<?php 
    session_start();
    if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
        $user_id = $_SESSION['user_id'];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Module</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/module.css">
    <link rel="stylesheet" href="assets/css/rating.css">
</head>
<body>
    <?php include "NavBar.php"; ?>
    <h1>Display Module</h1>
    <?php 
        include "checkConnection.php";
        $moduleId = $_GET['moduleId'];
        $con = checkConnectionDb();
        $stmt = $con->prepare(" SELECT Module.*, User.first_name, User.last_name 
                                FROM Module INNER JOIN User 
                                ON Module.module_creator_id = User.user_id 
                                WHERE Module.module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        // check for error in the query
        if (!$stmt) {
            $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: " . $con->error;
            error_log($error . "\n", 3, "error.log");
        }
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $module = $result->fetch_assoc();
            $creatorName = $module['first_name']. " " . $module['last_name'];
            $creatorId = $module['module_creator_id'];
        }
        // Show the module 
        echo "<div class='path'>";
        echo "<div class='module'>";
        echo    "<h2>" . $module['module_title'] . "</h2>";
        echo    "<p>" . $module['module_description'] . "</p>";
        echo    "<p>Rating: <span id=\"currentRating_". $moduleId . "\">" . $module['rating'] . "</span></p>";
        echo    "<p>Creator: " . $creatorName . "</p>";
        echo "</div>";
        $stmt = $con->prepare("SELECT * FROM Objective WHERE module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $objectives = array();
            while ($row = $result->fetch_assoc()) {
                $objectives[] = $row;
            }
        }
        // Show the objectives
        for ($i = 0; $i < count($objectives); $i++) {
            echo "<div class='objective'>";
            echo    "<a href='". $objectives[$i]['objective_url'] ."'><h3>" . $objectives[$i]['objective_title'] . "</h3></a>";
            echo "</div>";
        }
        echo "</div>";
        $stmt->close();
        $con->close();
    ?>
    <!-- FLoating upvote and downvote buttons -->
    <div class="rating" id="vote">
        <p>Rate this path</p>
        <?php
            try {
                $con = checkConnectionDb();
                $stmt2 = $con->prepare("SELECT vote FROM UserVotes WHERE user_id = ? AND module_id = ?");
                $stmt2->bind_param("ii", $user_id, $moduleId);
                $stmt2->execute();
                $result2 = $stmt2->get_result();
                if ($result2->num_rows > 0) {
                    if ($result2->fetch_assoc()['vote'] == 'up'){
                        echo "<button onclick=\"vote('up', $moduleId)\" id=\"upvote_$moduleId\" class=\"selected\"><i class=\"fa-regular fa-thumbs-up\"></i></button>";
                        echo "<button onclick=\"vote('down', $moduleId)\" id=\"downvote_$moduleId\"><i class=\"fa-regular fa-thumbs-down\"></i></button>";  
                    } else {
                        echo "<button onclick=\"vote('up', $moduleId)\" id=\"upvote_$moduleId\"><i class=\"fa-regular fa-thumbs-up\"></i></button>";
                        echo "<button onclick=\"vote('down', $moduleId)\" id=\"downvote_$moduleId\" class=\"selected\"><i class=\"fa-regular fa-thumbs-down\"></i></button>";    
                    }              
                } else {
                    echo "<button onclick=\"vote('up', $moduleId)\" id=\"upvote_$moduleId\"><i class=\"fa-regular fa-thumbs-up\"></i></button>";
                    echo "<button onclick=\"vote('down', $moduleId)\" id=\"downvote_$moduleId\"><i class=\"fa-regular fa-thumbs-down\"></i></button>";                  
                }
                echo "<button onclick=\"share($moduleId)\" id=\"share_$moduleId\"><i class=\"fa-solid fa-share\"></i></i></button>";
                echo "<button onlick=\"clone($moduleId,$creatorId)\" id=\"clone_$moduleId\"><i class=\"fa-solid fa-clone\"></i></button>";
                $con->close();
                $stmt2->close();
            } catch (Exception $e) {
                error_log($e->getMessage(), 3, 'error.log');
            }
?>
    </div>
    <div id="popup">
        <div class="popup-content">
            <span id="close">&times;</span>
            <h2>Log in or sign up to vote!</h2>
            <a href="LogIn.php">Log in</a>
            <a href="SignUp.php">Sign up</a>
        </div>
    </div>
    <div id="popup2">
        <div class="popup-content">
            <span id="close2">&times;</span>
            <h2>Share this path</h2>
            <input type="text" id="shareLink">
            <button class="btn" onclick="copyLink()">Copy</button>
        </div>
    </div>
    <script src="assets/js/vote.js"></script>
    <script src="https://kit.fontawesome.com/8115f5ec82.js" crossorigin="anonymous"></script>
</body>
</html>