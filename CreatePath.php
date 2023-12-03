<?php
    ini_set('display_errors', 0);
    session_start();
    if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
        $user_id = $_SESSION['user_id'];
    } else {
        header('Location: LogIn.php?error=401');
    }
    include "CheckConnection.php";

    $module = null;
    $objectives = null;
    if (isset($_GET['module_id'])) { // If the user is editing a module
        $moduleId = $_GET['module_id'];
        $con = checkConnectionDb();
        if (isset($_GET['creatorName'])) {
            $stmt = $con->prepare("SELECT module_creator_id FROM Module WHERE module_id = ? AND module_creator_id = 
            (SELECT user_id FROM User WHERE CONCAT(first_name, ' ', last_name) = ?)");
            $stmt->bind_param("is", $moduleId, $_GET['creatorName']);
            $stmt->execute();
            $result = $stmt->get_result();
            $originalCreatorId = $result->fetch_assoc()['module_creator_id'];
        }
        // check if the user is the creator of the module
        $stmt = $con->prepare("SELECT module_creator_id FROM Module WHERE module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['module_creator_id'] != $_SESSION['user_id']) { // the url only have moduleId -> the user is not the creator of the module
                if (isset($_GET['creatorName']) && $row['module_creator_id'] == $originalCreatorId) { // the url para have moduleId and creatorId -> the user is cloning the module from another user
                    $originalCreator = $_GET['creatorName'];
                } 
                else { 
                    $_SESSION = array();
                    session_destroy();
                    header('Location: LogIn.php?error=401');
                    exit;
                }
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
        <form id="moduleForm" method="post" action="SubmitModule.php<?php echo isset($moduleId) ? "?module_id=".$moduleId : ""; echo isset($originalCreator) ? "&clone=1" : "";?>" onsubmit="return validateForm()">
            <label for="moduleTitle">Module Title:</label>
            <input type="text" id="moduleTitle" name="moduleTitle" value="<?php echo $module ? $module['module_title'] : ''; ?>" required>

            <label for="moduleDescription">Module Description:</label>
            <textarea id="moduleDescription" name="moduleDescription" required><?php 
                echo $module ? htmlspecialchars_decode($module['module_description']) : ''; 
                if (isset($_GET['creatorName'])) {
                    echo "\n\nThis module was contributed by " . $_GET['creatorName'] . ".";
                }
            ?></textarea>
            
            <div id="objectives">
                <?php
                    // Add the required number of Objective title and url field
                    if ($objectives) {
                        for ($i = 0; $i < count($objectives); $i++) {
                            echo '<div class="objective">';
                            echo    '<label for="objectiveTitle' . ($i + 1) . '">Objective Title:</label>';
                            echo    '<input type="text" id="objectiveTitle' . ($i + 1) . '" name="objectiveTitle[]" value="' . $objectives[$i]['objective_title'] . '" required>';
                    
                            echo    '<label for="objectiveUrl' . ($i + 1) . '">Objective URL:</label>';
                            echo    '<input type="url" id="objectiveUrl' . ($i + 1) . '" name="objectiveUrl[]" value="' . $objectives[$i]['objective_url'] . '" required>';
                    
                            // Add a delete button next to each objective, but disable it if there's only one objective
                            echo '<button type="button" class="delete btn" onclick="deleteObjective(this, ' . $objectives[$i]['objective_id'] . ')" ' . (count($objectives) <= 1 ? 'disabled' : '') . '>Delete</button>';
                    
                            echo '</div>';
                        }
                    }
                ?>
            </div>

            <button type="button" id="addObjective" class="btn add">Add Objective</button>
            <input type="submit" value="Submit">
            <p id="warning"></p>
        </form>

        <script src="assets/js/addObjective.js"></script>
        <script src="https://kit.fontawesome.com/8115f5ec82.js" crossorigin="anonymous"></script>
    </body>
</html>