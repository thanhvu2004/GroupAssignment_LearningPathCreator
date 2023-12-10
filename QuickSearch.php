<?php
    // handle form submission
    session_start();
    include "CheckConnection.php";
    if (isset($_POST['tag_name']) && isset($_POST['tag_keywords']) && isset($_SESSION['login_email'])) {
        $con = checkConnectionDb();

        $tagName = $_POST['tag_name'];
        $tagKeywords = $_POST['tag_keywords'];

        // Check if tag name already exists
        $stmt = $con->prepare("SELECT tag_id FROM Tag WHERE tag_name = ?");
        $stmt->bind_param("s", $tagName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Tag name already exists
            // Check if there is new keywords to add
            $row = $result->fetch_assoc();
            $tagId = $row['tag_id'];
            $stmt = $con->prepare("SELECT tag_keywords FROM Tag WHERE tag_id = ?");
            $stmt->bind_param("i", $tagId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $tagKeywords = $row['tag_keywords'] . "," . $tagKeywords;
            $stmt = $con->prepare("UPDATE Tag SET tag_keywords = ? WHERE tag_id = ?");
            $stmt->bind_param("si", $tagKeywords, $tagId);
            $stmt->execute();
            $stmt->close();
        } else {
            // Tag name does not exist
            $stmt = $con->prepare("INSERT INTO Tag (tag_name, tag_keywords) VALUES (?, ?)");
            $stmt->bind_param("ss", $tagName, $tagKeywords);
            $stmt->execute();
            $stmt->close();
            $con->close();
            header('Location: QuickSearch.php');
            exit;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Search</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/quickSearch.css">
</head>
<body>
    <?php include "NavBar.php"; ?>
    <h1>Quick Search</h1> 
    <!-- show all tags name -->
    <h2>All Tags</h2>
    <h2>Click on a Tag for quick search!</h2><br>
    <div id="tag_container">
        <?php
            $con = checkConnectionDb();
            $stmt = $con->prepare("SELECT tag_name FROM Tag");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<a href='index.php?search=" . $row['tag_name'] . "' class='tag'>" . $row['tag_name'] . "</a>";
                }
            } else {
                echo "<p>No tags found</p>";
            }
            $con->close();
        ?>
    </div>
    <h2>Add a keyword to help other quickly search for subjects</h2>
    <!-- a form to add tag name and the tag keywords to Tag table -->
    <form action="QuickSearch.php" method="post">
        <label for="tag_name">Tag Name</label>
        <input type="text" name="tag_name" id="tag_name" placeholder="Tag Name" required>
        <label for="tag_keywords">Tag Keywords</label>
        <input type="text" name="tag_keywords" id="tag_keywords" placeholder="Tag Keywords (Seperate keywords with ,)" required>
        <input type="submit" value="Add Tag">
        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_SESSION['login_email'])) {
                echo "<p class=\"error\">You must be logged in to add a tag.</p>";
            }
        ?>
    </form>
    <script src="https://kit.fontawesome.com/8115f5ec82.js" crossorigin="anonymous"></script>
</body>
</html>