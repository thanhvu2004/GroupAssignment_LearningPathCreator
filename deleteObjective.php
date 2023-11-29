<?php
session_start();

if (isset($_POST['objective_id'])) {
    $objective_id = $_POST['objective_id'];

    // Find the objective in the session array and delete it
    foreach ($_SESSION['objectives'] as $i => $objective) {
        if ($objective['objective_id'] == $objective_id) {
            unset($_SESSION['objectives'][$i]);
            echo "Objective deleted successfully";
            return;
        }
    }

    echo "Objective not found";
}