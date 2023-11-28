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
    <title>Home page</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/index.css?v=1.3">
</head>
<body>
    <?php include "NavBar.php";?>
    <h1>Hello <?php if(isset($_SESSION['fullname'])){echo $_SESSION['fullname'];}  ?></h1>
    <h2>Welcome to the Learning Path Creator!</h2>
    <p>Here you can create your own learning path and share it with others!</p>
    <p>Click on the "Create Path" button to get started!</p>
    <a href="CreatePath.php"><button>Create Path</button></a>
    <div class="AllModules">
        <!-- Diplay all modules from the db -->
        <?php
            $con = new mysqli("f3411302.gblearn.com", "f3411302_admin", "admin", "f3411302_LearningPathCreator");
            if ($con->connect_error) {
                die("Connection failed: " . $con->connect_error);
            }
            $stmt = $con->prepare("SELECT module_id, module_title, module_description, rating FROM Module");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='module'>";
                    echo    "<h3>" . $row['module_title'] . "</h3>";
                    echo    "<p>" . $row['module_description'] . "</p>";
                    // show the module's objectives and the url for each objective
                    $stmt2 = $con->prepare("SELECT objective_title, objective_url FROM Objective WHERE module_id = ?");
                    $stmt2->bind_param("i", $row['module_id']);
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
                        // A row exists, so get the existing vote
                        $row2 = $result2->fetch_assoc();
                        $existingVote = $row2['vote'];
                
                        // Disable the upvote or downvote button based on the existing vote
                        if ($existingVote === 'up') {
                            echo "<button disabled onclick=\"vote('up', {$row['module_id']})\" id=\"upvote_{$row['module_id']}\">&#8679;</button>";
                            echo "<button onclick=\"vote('down', {$row['module_id']})\" id=\"downvote_{$row['module_id']}\">&#8681;</button>";
                        } else {
                            echo "<button onclick=\"vote('up', {$row['module_id']})\" id=\"upvote_{$row['module_id']}\">&#8679;</button>";
                            echo "<button disabled onclick=\"vote('down', {$row['module_id']})\" id=\"downvote_{$row['module_id']}\">&#8681;</button>";
                        }
                    } else {
                        // No row exists, so show both buttons enabled
                        echo "<button onclick=\"vote('up', {$row['module_id']})\" id=\"upvote_{$row['module_id']}\">&#8679;</button>";
                        echo "<button onclick=\"vote('down', {$row['module_id']})\" id=\"downvote_{$row['module_id']}\">&#8681;</button>";
                    }                    
                    echo "<p id=\"currentRating_{$row['module_id']}\">" . number_format($row['rating'], 0) . "</p>";
                    echo "</div>";
                    echo "</div>";
                }
            }
            $con->close();
        ?>
    </div>
    <script>
        function vote(action, moduleId) {
            document.getElementById('upvote_' + moduleId).disabled = true;
            document.getElementById('downvote_' + moduleId).disabled = true;

            fetch('update_rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: action,
                    module_id: moduleId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error === 'not_logged_in') {
                    window.location.href = 'login.php';
                } else {
                    const updatedRating = data.updatedRating;
                    document.getElementById('currentRating_' + moduleId).innerText = updatedRating;
                    document.getElementById('downvote_' + moduleId).disabled = false;
                    document.getElementById('upvote_' + moduleId).disabled = false;
                    document.getElementById(action + 'vote_' + moduleId).disabled = true;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('downvote_' + moduleId).disabled = false;
                document.getElementById('upvote_' + moduleId).disabled = false;
            });
        }
    </script>
</body>
</html>