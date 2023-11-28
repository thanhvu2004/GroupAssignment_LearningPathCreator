<?php
    function checkConnectionDb(){
        try {
            // Attempt to establish a database connection
            $con = new mysqli("f3411302.gblearn.com", "f3411302_admin", "admin", "f3411302_LearningPathCreator");
            if ($con->connect_error) {
                throw new Exception("Database connection failed: " . $con->connect_error);
            }    
        } catch (Exception $e) {
            header("Location: error.php?error=500");
            exit;
        }
        return $con;
    }