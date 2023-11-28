<?php
    session_start();
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Connect to the database
            $con = new mysqli("f3411302.gblearn.com", "f3411302_admin", "admin", "f3411302_LearningPathCreator");
            if ($con->connect_error) {
                die("Connection failed: " . $con->connect_error);
            }
            // Retrieve the user_id based on the email
            $stmt = $con->prepare("SELECT user_id FROM User WHERE email = ?");
            $stmt->bind_param("s", $_SESSION['login_email']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $user_id = $row['user_id'];
            } else {
                die("User not found.");
            }
        // Validate module title
        if (empty($_POST["moduleTitle"])) {
            die("Module title is required");
        } else {
            $moduleTitle = sanitizeInput($_POST["moduleTitle"]);
        }

        // Validate module description
        if (empty($_POST["moduleDescription"])) {
            die("Module description is required");
        } else {
            $moduleDescription = sanitizeInput($_POST["moduleDescription"]);
            // Insert the module into the database
            $stmt = $con->prepare("INSERT INTO Module (module_title, module_description, module_creator_id) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $moduleTitle, $moduleDescription, $user_id);
            $stmt->execute();
            $module_id = $stmt->insert_id;
        }
        // Validate objectives
        if (empty($_POST["objectiveTitle"]) || empty($_POST["objectiveUrl"])) {
            die("At least one objective is required");
        } else {
            $objectiveTitles = $_POST["objectiveTitle"];
            $objectiveUrls = $_POST["objectiveUrl"];

            if (count($objectiveTitles) != count($objectiveUrls)) {
                die("Each objective must have a title and a URL");
            }

            $objectives = array();
            for ($i = 0; $i < count($objectiveTitles); $i++) {
                $objectives[] = array(
                    'title' => sanitizeInput($objectiveTitles[$i]),
                    'url' => filter_var($objectiveUrls[$i], FILTER_VALIDATE_URL)
                );
            }
            // Insert the objectives into the database

            $stmt = $con->prepare("INSERT INTO Objective (objective_title, objective_url, module_id) VALUES (?, ?, ?)");
            foreach ($objectives as $objective) {
                $stmt->bind_param("ssi", $objective['title'], $objective['url'], $module_id);
                $stmt->execute();
            }
        }    
        $stmt->close();    
        $con->close();
        header('Location: index.php');        
    }

    // Function to sanitize input
    function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }