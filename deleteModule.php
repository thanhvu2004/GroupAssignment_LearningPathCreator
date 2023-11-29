<?php
    session_start();
    if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
        $user_id = $_SESSION['user_id'];
    }
    include "checkConnection.php";
    $con = checkConnectionDb();
    if (isset($_GET['module_id'])) {
        $module_id = $_GET['module_id'];
        // delete all objectives associated with the module
        $stmt = $con->prepare("DELETE FROM Objective WHERE module_id = ?");
        $stmt->bind_param("i", $module_id);
        $stmt->execute();
        // delete the module
        $stmt = $con->prepare("DELETE FROM Module WHERE module_id = ?");
        $stmt->bind_param("i", $module_id);
        $stmt->execute();
    
        if ($stmt->affected_rows > 0) {
            echo "Module deleted successfully";
            header('Location: Profile.php');
        } else {
            echo "Error deleting module";
            header('Location: Profile.php');
        }
    } else {
        echo "Error deleting module";
        header('Location: Profile.php');
    }