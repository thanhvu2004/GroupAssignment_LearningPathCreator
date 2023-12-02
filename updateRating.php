<?php
        ini_set('display_errors', 0);
        session_start();
        include "checkConnection.php";
        define('CONTENT_TYPE_JSON', 'application/json');

        // Check if the request is a POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Ensure user is logged in or implement necessary authentication checks
            if (!isset($_SESSION['login_email']) || !isset($_SESSION['fullname'])) {
                http_response_code(200); // Unauthorized
                echo json_encode(['error' => 'not_logged_in', 'code' => 401]);
                exit;
            }

            // Get the raw POST data and decode it into an associative array
            $postData = json_decode(file_get_contents('php://input'), true);

            // Get the action and module ID from the POST data
            $action = $postData['action'];
            $module_id = $postData['module_id'];

            // Get the user's ID
            $user_id = $_SESSION['user_id'];

            // Connect to the database
            $con = checkConnectionDb();

            // Check if a row already exists in UserVotes for the current user and module
            $stmt = $con->prepare("SELECT vote FROM UserVotes WHERE user_id = ? AND module_id = ?");
            $stmt->bind_param("ii", $user_id, $module_id);
            $stmt->execute();
            $result = $stmt->get_result();

        $existingVote = null;

        if ($result->num_rows > 0) {
            // A row exists, so get the existing vote
            $row = $result->fetch_assoc();
            $existingVote = $row['vote'];

            if ($existingVote !== $action) {
                // The existing vote is different from the new vote, so update the rating and the vote
                $stmt = $con->prepare("UPDATE UserVotes SET vote = ? WHERE user_id = ? AND module_id = ?");
                $stmt->bind_param("sii", $action, $user_id, $module_id);
                $stmt->execute();
            }
        } else {
            // No row exists, so insert a new row and update the rating
            $stmt = $con->prepare("INSERT INTO UserVotes (user_id, module_id, vote) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $user_id, $module_id, $action);
            $stmt->execute();
        }

        // Retrieve the current rating of the module from the database
        $stmt = $con->prepare("SELECT rating FROM Module WHERE module_id = ?");
        $stmt->bind_param("i", $module_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentRating = $row['rating'];

        // Update the rating based on the action (upvote or downvote)
        if ($action === 'up' && $existingVote === 'down') {
            $currentRating += 2; // Increment rating by 2 for upvote if the existing vote was a downvote
        } elseif ($action === 'down' && $existingVote === 'up') {
            $currentRating -= 2; // Decrement rating by 2 for downvote if the existing vote was an upvote
        } elseif ($action === 'up' && $existingVote === 'up' || $action === 'down' && $existingVote === 'down'){
            $stmt = $con->prepare("DELETE FROM UserVotes WHERE user_id = ? AND module_id = ?");
            $stmt->bind_param("ii", $user_id, $module_id);
            $stmt->execute();
            $action === 'up' ? $currentRating-- : $currentRating++;
            $action = 'cancel';
        } else {
            $action === 'up' ? $currentRating++ : $currentRating--;
        }

        // Update the module's rating in the database
        $updateStmt = $con->prepare("UPDATE Module SET rating = ? WHERE module_id = ?");
        $updateStmt->bind_param("di", $currentRating, $module_id);
        $updateStmt->execute();

        // Return the updated rating as a JSON response
        error_log(CONTENT_TYPE_JSON . "\n", 3, "error.log");
        header(CONTENT_TYPE_JSON);
        echo json_encode(['updatedRating' => $currentRating,'action' => $action]);
        } else {
            $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: Module not found.";
            error_log($error . "\n", 3, "error.log");
        }

        // Close database connection
        $con->close();
    } else {
        http_response_code(400); // Bad Request
        header('Content-Type: application/json');
        echo json_encode(['error' => 'invalid_request']);
        $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: Invalid request.";
        error_log($error . "\n", 3, "error.log");
        exit;
    }