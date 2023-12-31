<?php
    session_start();
    if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
        $user_id = $_SESSION['user_id'];
    }
    include "CheckConnection.php";

    $con = checkConnectionDb();
    // Retrieve the user_id based on the email
    $stmt = $con->prepare("SELECT user_id FROM User WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['login_email']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['user_id'];

        // Retrieve the image data based on the user_id
        $stmt = $con->prepare("SELECT image_data FROM UserImages WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $image_info = getimagesizefromstring($row['image_data']);
            $image_type = $image_info['mime'];
            $image_data = 'data:' . $image_type . ';base64,' . base64_encode($row['image_data']);
        } else {
            $image_data = 'assets/img/default.png';
        }
    } else {
        // User not found by email
        $image_data = 'assets/img/default.png';
    }
    $con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/profile.css?v=1.2">
</head>
<body>
    <?php include "NavBar.php"; ?>
    <!-- Profile -->
    <h1>Profile</h1>
    <div class="container">
        <!-- display user avatar -->
        <div class="bio1">
            <img src="<?php echo $image_data; ?>" alt="avatar">        
            <h2><?php echo $_SESSION['fullname']; ?></h2>
            <h3><?php echo $_SESSION['login_email']; ?></h3>
            <a class="btn center" id="updProfile" href="UpdateProfile.php">Update Profile</a>
        </div>
        <!-- display user owned modules -->
        <div class="bio2">
            <h2>My Modules</h2>
            <?php
                $con = checkConnectionDb();
                $stmt = $con->prepare("SELECT module_id, module_title, module_description, rating FROM Module WHERE module_creator_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    echo "<div class='AllModules'>";
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
                        echo "<p id=\"currentRating_{$row['module_id']}\">Rating: " . number_format($row['rating'], 0) . "</p>";
                        echo "<a href=\"CreatePath.php?module_id={$row['module_id']}\"><button class=\"btn\">Edit</button></a>";
                        echo "<button onclick=\"confirmDelete({$row['module_id']})\" class=\"btn delete2\">Delete</button>";
                        echo "</div>";
                        echo "</div>";
                    }
                    echo "</div>";
                }
                $con->close();
            ?>
    </div>
    <script src="assets/js/deleteModule.js"></script>
    <script src="https://kit.fontawesome.com/8115f5ec82.js" crossorigin="anonymous"></script>
</body>
</html>
