<?php
    session_start();
    if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
        $user_id = $_SESSION['user_id'];
    }
    include "CheckConnection.php";
    $con = checkConnectionDb();

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['module_id'])) {
        $module_id = $_POST['module_id'];

        $stmt = $con->prepare("DELETE FROM Objective WHERE module_id = ?");
        $stmt->bind_param("i", $module_id);
        $stmt->execute();

        $stmt = $con->prepare("DELETE FROM Module WHERE module_id = ?");
        $stmt->bind_param("i", $module_id);
        $stmt->execute();
        $stmt->close();

        echo "Module deleted successfully";
    } else {
        // log error
        $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: Module not found";
        error_log($error . "\n", 3, "error.log");
    }
