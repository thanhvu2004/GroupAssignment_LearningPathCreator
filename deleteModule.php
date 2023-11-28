<?php
    session_start();
    if (!isset($_SESSION['login_email']) || !isset($_SESSION['fullname'])) {
        header('Location: login.php');
        exit;
    }
    if (isset($_GET['module_id'])) {
        $moduleId = $_GET['module_id'];
        $con = new mysqli("f3411302.gblearn.com", "f3411302_admin", "admin", "f3411302_LearningPathCreator");
        if ($con->connect_error) {
            die("Connection failed: " . $con->connect_error);
        }
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
        $stmt = $con->prepare("DELETE FROM Objective WHERE module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $stmt = $con->prepare("DELETE FROM UserVotes WHERE module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $stmt = $con->prepare("DELETE FROM Module WHERE module_id = ?");
        $stmt->bind_param("i", $moduleId);
        $stmt->execute();
        $stmt->close();
        $con->close();
        header('Location: Profile.php');
    } else {
        header('Location: Profile.php');
    }