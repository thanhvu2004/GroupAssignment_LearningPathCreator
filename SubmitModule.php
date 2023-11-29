<?php
ini_set('display_errors', 0);
session_start();
if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
    $user_id = $_SESSION['user_id'];
} else {
    header('Location: Login.php?error=401');
    exit;
}
// get the objective data from the session
if (isset($_SESSION['O_objectiveTitles']) && isset($_SESSION['O_objectiveUrls']) && isset($_SESSION['O_objectiveIds'])) {
    $O_objectiveTitles = $_SESSION['O_objectiveTitles'];
    $O_objectiveUrls = $_SESSION['O_objectiveUrls'];
    $O_objectiveIds = $_SESSION['O_objectiveIds'];
} else {
    $O_objectiveTitles = array();
    $O_objectiveUrls = array();
    $O_objectiveIds = array();
}
include "checkConnection.php";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $con = checkConnectionDb();

    // Validate module title and description
    $moduleTitle = isset($_POST["moduleTitle"]) ? sanitizeInput($_POST["moduleTitle"]) : '';
    $moduleDescription = isset($_POST["moduleDescription"]) ? sanitizeInput($_POST["moduleDescription"]) : '';



    $user_id = $_SESSION['user_id'];

    // Check if the module already exists
    $module_id = isset($_GET["module_id"]) ? sanitizeInput($_GET["module_id"]) : 'null';
    
    if ($module_id != 'null') {
        // Module exists, update the Module record
        $updateStmt = $con->prepare("UPDATE Module SET module_title = ?, module_description = ? WHERE module_id = ?");
        $updateStmt->bind_param("ssi", $moduleTitle, $moduleDescription, $module_id);
        $updateStmt->execute();
        $lastInsertedModuleId = $module_id; // Retrieve the auto-generated module_id
        $updateStmt->close();
    } else {
        // Module does not exist, insert a new Module record
        $insertStmt = $con->prepare("INSERT INTO Module (module_title, module_description, module_creator_id) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sss", $moduleTitle, $moduleDescription, $user_id);
        $insertStmt->execute();
        $lastInsertedModuleId = $con->insert_id; // Retrieve the auto-generated module_id
        $insertStmt->close();    
    }

    // Validate objectives
    if (!empty($_POST["objectiveTitle"]) && !empty($_POST["objectiveUrl"])) {
        $P_objectiveTitles = $_POST["objectiveTitle"];
        $P_objectiveUrls = $_POST["objectiveUrl"];
        echo "P_objectiveTitles: " . count($P_objectiveTitles) . "<br>"; // debug
        echo "P_objectiveUrls: " . count($P_objectiveUrls) . "<br>"; // debug
        echo "O_objectiveTitles: " . count($O_objectiveTitles) . "<br>"; // debug
        echo "O_objectiveUrls: " . count($O_objectiveUrls) . "<br>"; // debug

        $insertStmt = $con->prepare("INSERT INTO Objective (objective_title, objective_url, module_id) VALUES (?, ?, ?)");
        $updateStmt = $con->prepare("UPDATE Objective SET objective_title = ?, objective_url = ? WHERE objective_id = ?");
        
        if (count($O_objectiveTitles) == 0) {
            // Insert new objectives
            echo "nothing in O_objectiveTitles, inserting new objectives"; // debug
            for ($i = 0; $i < count($P_objectiveTitles); $i++) {
                $insertStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $lastInsertedModuleId);
                $insertStmt->execute();
            }
        } elseif (count($O_objectiveTitles) < count($P_objectiveTitles)) {
            // Insert new objectives
            echo "O_objectiveTitles < P_objectiveTitles, inserting new objectives"; // debug
            for ($i = count($O_objectiveTitles); $i < count($P_objectiveTitles); $i++) {
                $insertStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $lastInsertedModuleId);
                $insertStmt->execute();
            }
            // Check if there are any objectives to update
            if (count($O_objectiveTitles) > 0) {
                // Update objectives
                echo "O_objectiveTitles > 0, updating objectives"; // debug
                for ($i = 0; $i < count($O_objectiveTitles); $i++) {
                    $updateStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $O_objectiveIds[$i]);
                    $updateStmt->execute();
                }
            }
        } elseif (count($O_objectiveTitles) == count($P_objectiveTitles)) {
            // Update objectives
            echo "O_objectiveTitles == P_objectiveTitles, updating objectives"; // debug
            for ($i = 0; $i < count($P_objectiveTitles); $i++) {
                $updateStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $O_objectiveIds[$i]);
                $updateStmt->execute();
            }
        } elseif (count($O_objectiveTitles) > count($P_objectiveTitles)) {
            // Update objectives
            echo "O_objectiveTitles > P_objectiveTitles, updating objectives"; // debug
            for ($i = 0; $i < count($P_objectiveTitles); $i++) {
                $updateStmt->bind_param("ssi", $P_objectiveTitles[$i], $P_objectiveUrls[$i], $O_objectiveIds[$i]);
                $updateStmt->execute();
            }
            // Delete objectives
            echo "O_objectiveTitles > P_objectiveTitles, deleting objectives"; // debug
            for ($i = count($P_objectiveTitles); $i < count($O_objectiveTitles); $i++) {
                $deleteStmt = $con->prepare("DELETE FROM Objective WHERE objective_id = ?");
                $deleteStmt->bind_param("i", $O_objectiveIds[$i]);
                $deleteStmt->execute();
            }
        }
    }
    
    $con->close();
    try {
        // Redirect to Profile.php
        header("Location: Profile.php");
        exit();
    } catch (Exception $e) {
        // log error
        echo "<a href=\"Profile.php\">Back to Profile</a>";
        $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: " . $e->getMessage();
        error_log($error . "\n", 3, "logs/errors.log");
    }
    exit();
}

// Function to sanitize input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}