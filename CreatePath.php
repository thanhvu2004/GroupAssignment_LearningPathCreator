<?php
    session_start();
    if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        header('Location: Login.php?error=401');
        exit;
    }
    include "checkConnection.php";

    $module = null;
    $objectives = null;
    if (isset($_GET['module_id'])) {
        $moduleId = $_GET['module_id'];
        $con = new mysqli("f3411302.gblearn.com", "f3411302_admin", "admin", "f3411302_LearningPathCreator");
        // check if the user is the creator of the module
        $stmt = $con->prepare("SELECT module_creator_id FROM Module WHERE module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['module_creator_id'] != $_SESSION['user_id']) {
                header('Location: Login.php');
                exit;
            }
        }
        // Data from the module table
        $stmt = $con->prepare("SELECT * FROM Module WHERE module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $module = $result->fetch_assoc();
        }
        // Data from the objective table
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
        $O_objectiveTitles = array();
        $O_objectiveUrls = array();
        $O_objectiveIds = array();
        foreach ($objectives as $objective) {
            $O_objectiveTitles[] = $objective['objective_title'];
            $O_objectiveUrls[] = $objective['objective_url'];
            $O_objectiveIds[] = $objective['objective_id'];
        }
        $_SESSION['O_objectiveTitles'] = $O_objectiveTitles;
        $_SESSION['O_objectiveUrls'] = $O_objectiveUrls;
        $_SESSION['O_objectiveIds'] = $O_objectiveIds;
        $stmt->close();
        $con->close();
    } else {
        $_SESSION['O_objectiveTitles'] = null;
        $_SESSION['O_objectiveUrls'] = null;
        $_SESSION['O_objectiveIds'] = null;
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="assets/css/navbar.css">
        <link rel="stylesheet" href="assets/css/main.css">
    </head>
    <body>
        <?php include "NavBar.php";?>
        <form id="moduleForm" method="post" action="submitModule.php<?php echo isset($moduleId) ? "?module_id=".$moduleId : ""; ?>">
            <label for="moduleTitle">Module Title:</label>
            <input type="text" id="moduleTitle" name="moduleTitle" value="<?php echo $module ? $module['module_title'] : ''; ?>" required>

            <label for="moduleDescription">Module Description:</label>
            <textarea id="moduleDescription" name="moduleDescription" required><?php echo $module ? htmlspecialchars_decode($module['module_description']) : ''; ?></textarea>
            
            <div id="objectives">
                <div class="objective">
                    <label for="objectiveTitle1">Objective Title:</label>
                    <input type="text" id="objectiveTitle1" name="objectiveTitle[]" value="<?php echo $objectives ? $objectives[0]['objective_title'] : ''; ?>" required>

                    <label for="objectiveUrl1">Objective URL:</label>
                    <input type="url" id="objectiveUrl1" name="objectiveUrl[]" value="<?php echo $objectives ? $objectives[0]['objective_url'] : ''; ?>" required>
                </div>
                <?php
                    // Add the required number of Objective title and url field
                    if ($objectives) {
                        for ($i = 1; $i < count($objectives); $i++) {
                            echo '<div class="objective">';
                            echo    '<label for="objectiveTitle' . ($i + 1) . '">Objective Title:</label>';
                            echo    '<input type="text" id="objectiveTitle' . ($i + 1) . '" name="objectiveTitle[]" value="' . $objectives[$i]['objective_title'] . '" required>';

                            echo    '<label for="objectiveUrl' . ($i + 1) . '">Objective URL:</label>';
                            echo    '<input type="url" id="objectiveUrl' . ($i + 1) . '" name="objectiveUrl[]" value="' . $objectives[$i]['objective_url'] . '" required>';
                            echo '</div>';
                        }
                    }
                ?>
            </div>

            <button type="button" id="addObjective">Add Objective</button>
            <input type="submit" value="Submit">
        </form>

        <script src="assets/js/addObjective.js"></script>
    </body>
</html>