<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Module</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/module.css">
</head>
<body>
    <?php include "NavBar.php";?>
    <h1>Display Module</h1>
    <?php 
        include "checkConnection.php";
        $moduleId = $_GET['moduleId'];
        $con = checkConnectionDb();
        $stmt = $con->prepare("SELECT * FROM Module WHERE module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $module = $result->fetch_assoc();
        }
        // Show the module 
        echo "<div class='path'>";
        echo "<div class='module'>";
        echo    "<h2>" . $module['module_title'] . "</h2>";
        echo    "<p>" . $module['module_description'] . "</p>";
        echo    "<p>Rating: " . $module['rating'] . "</p>";
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
</body>
</html>