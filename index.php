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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/rating.css">
</head>
<body>
    <?php include "NavBar.php";
    include "CheckConnection.php";?>
    <!-- Home page -->
    <div class="welcome">
        <h1>Hello <?php if(isset($_SESSION['fullname'])){echo $_SESSION['fullname'];}  ?></h1>
        <h2>Welcome to the Learning Path Creator!</h2>
        <p>Here you can create your own learning path and share it with others!</p>
        <p>Click on the "Create Path" button to get started!</p>
        <a href="CreatePath.php"><button class="btn">Create Path</button></a>
    </div>
    <!-- Display search results -->
    <?php
        if (isset($_GET['search'])) {
            echo "<div class=\"SearchResult AllModules\">";
            echo "<h2>Search results for \"" . $_GET['search'] . "\"</h2>";
            $con = checkConnectionDb();
            $searchTerm = $_GET['search'];
            $keywords = explode(',', $searchTerm);
            // First, try to find a match in tag_name
            $stmt = $con->prepare("SELECT tag_keywords FROM Tag WHERE tag_name = ?");
            $stmt->bind_param('s', $searchTerm);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // If a match is found in tag_name, use the tag_keywords for the search
                $row = $result->fetch_assoc();
                $keywords = explode(',', $row['tag_keywords']);
            }
            $query = "SELECT * FROM Module WHERE ";
            // Create placeholders for the keywords in the query
            $placeholders = [];
            $params = [];
            foreach ($keywords as $keyword) {
                $placeholders[] = "module_title LIKE ? OR module_description LIKE ?";
                $params[] = "%$keyword%";
                $params[] = "%$keyword%";
            }
            // Join the placeholders with "OR" conditions
            $query .= implode(" OR ", $placeholders);
            $query .= " ORDER BY rating DESC";
            $stmt = $con->prepare($query);

            // Bind parameters using prepared statement to prevent SQL injection
            $stmt->bind_param(str_repeat('s', count($keywords) * 2), ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            echo '<div class="searchedModuleContainer">';
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='searchedModule'>";
                    echo "<p><a href='DisplayModule.php?moduleId=" . $row['module_id'] . "'>" . $row['module_title'] . "</a></p>";
                    echo "<p>" . $row['module_description'] . "</p>";
                    echo "<p>Rating: " . number_format($row['rating'], 0) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<h2>No results found</h2>";
            }
            echo "</div>";
            $stmt->close();
            $con->close();
            echo "</div>";
        }
    ?>

    <div class="AllModules">
        <!-- Diplay all modules from the db -->
        <?php
            $con = checkConnectionDb();

            $stmt = $con->prepare("SELECT * FROM Module ORDER BY rating DESC");
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
                    echo    "<pre>" . $row['module_description'] . "</pre>";
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
                    echo "<a href='DisplayModule.php?moduleId={$row['module_id']}' class=\"btn\"> Learn more</a>";
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