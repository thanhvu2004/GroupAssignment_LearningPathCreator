<?php
ini_set('display_errors', 0);
session_start();
if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header('Location: LogIn.php?error=401');
    exit;
}
// get the objective data from the session
if (isset($_SESSION['O_objectiveTitles']) && isset($_SESSION['O_objectiveUrls']) && isset($_SESSION['O_objectiveIds'])) {
    $O_objectiveTitles = $_SESSION['O_objectiveTitles'];
    $O_objectiveUrls = $_SESSION['O_objectiveUrls'];
    $O_objectiveIds = $_SESSION['O_objectiveIds'];
    unset($_SESSION['O_objectiveTitles']);
    unset($_SESSION['O_objectiveUrls']);
    unset($_SESSION['O_objectiveIds']);
} else {
    $O_objectiveTitles = array();
    $O_objectiveUrls = array();
    $O_objectiveIds = array();
}

include "CheckConnection.php";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $con = checkConnectionDb();

    // Validate module title and description
    $moduleTitle = isset($_POST["moduleTitle"]) ? sanitizeInput($_POST["moduleTitle"]) : '';
    if (strlen($moduleTitle) > 100) {
        $moduleTitle = substr($moduleTitle, 0, 100);
    }
    $moduleDescription = isset($_POST["moduleDescription"]) ? sanitizeInput($_POST["moduleDescription"]) : '';
    if (strlen($moduleDescription) > 1000) {
        $moduleDescription = substr($moduleDescription, 0, 1000);
    }

    $user_id = $_SESSION['user_id'];

    // Check if the module already exists
    $module_id = isset($_GET["module_id"]) ? sanitizeInput($_GET["module_id"]) : 'null';

    if ($module_id != 'null' && (!isset($_GET['clone']) || $_GET['clone'] != 1)) {
        // Module exists and is not being cloned, update the Module record
        $updateStmt = $con->prepare("UPDATE Module SET module_title = ?, module_description = ? WHERE module_id = ?");
        $updateStmt->bind_param("ssi", $moduleTitle, $moduleDescription, $module_id);
        $updateStmt->execute();
        $lastInsertedModuleId = $module_id; // Retrieve the auto-generated module_id
        $updateStmt->close();
    } else {       
        // Module does not exist or is being cloned, insert a new Module record
        $insertStmt = $con->prepare("INSERT INTO Module (module_title, module_description, module_creator_id) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sss", $moduleTitle, $moduleDescription, $user_id);
        $insertStmt->execute();
        $lastInsertedModuleId = $con->insert_id; // Retrieve the auto-generated module_id
        $insertStmt->close();    
    }

    // Validate objectives
    if (!empty($_POST["objectiveTitle"]) && !empty($_POST["objectiveUrl"])) {
        $P_objectiveTitles = array_filter($_POST["objectiveTitle"]);
        $P_objectiveUrls = array_filter($_POST["objectiveUrl"]);

        if (!empty($P_objectiveTitles) && !empty($P_objectiveUrls)) {

            $insertStmt = $con->prepare("INSERT INTO Objective (objective_title, objective_url, module_id) VALUES (?, ?, ?)");
            $updateStmt = $con->prepare("UPDATE Objective SET objective_title = ?, objective_url = ? WHERE objective_id = ?");
            
            if (count($O_objectiveTitles) == 0 || isset($_GET['clone']) && $_GET['clone'] == 1) {
                // Insert new objectives
                for ($i = 0; $i < count($P_objectiveTitles); $i++) {
                    $insertStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $lastInsertedModuleId);
                    $insertStmt->execute();
                }
            } elseif (count($O_objectiveTitles) < count($P_objectiveTitles)) {
                // Insert new objectives
                for ($i = count($O_objectiveTitles); $i < count($P_objectiveTitles); $i++) {
                    $insertStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $lastInsertedModuleId);
                    $insertStmt->execute();
                }
                // Check if there are any objectives to update
                if (count($O_objectiveTitles) > 0) {
                    // Update objectives
                    for ($i = 0; $i < count($O_objectiveTitles); $i++) {
                        $updateStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $O_objectiveIds[$i]);
                        $updateStmt->execute();
                    }
                }
            } elseif (count($O_objectiveTitles) == count($P_objectiveTitles)) {
                // Update objectives
                for ($i = 0; $i < count($P_objectiveTitles); $i++) {
                    $updateStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $O_objectiveIds[$i]);
                    $updateStmt->execute();
                }
            } elseif (count($O_objectiveTitles) > count($P_objectiveTitles)) {
                // Update objectives
                for ($i = 0; $i < count($P_objectiveTitles); $i++) {
                    $updateStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $O_objectiveIds[$i]);
                    $updateStmt->execute();
                }
                // Delete objectives
                for ($i = count($P_objectiveTitles); $i < count($O_objectiveTitles); $i++) {
                    $deleteStmt = $con->prepare("DELETE FROM Objective WHERE objective_id = ?");
                    $deleteStmt->bind_param("i", $O_objectiveIds[$i]);
                    $deleteStmt->execute();
                }
            }
        }
    }
    
    $con->close();
    header("Location: Profile.php");
    echo "<a href=\"Profile.php\">Profile</a>";
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}