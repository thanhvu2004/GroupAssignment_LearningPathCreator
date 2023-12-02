<?php
    ini_set('display_errors', 0);
    function checkConnectionDb(){
        try {
            // Attempt to establish a database connection
            $con = new mysqli("f3411302.gblearn.com", "f3411302_admin", "admin", "f3411302_LearningPathCreator");
            if ($con->connect_error) {
                throw new Exception("Database connection failed: " . $con->connect_error);
            }    
        } catch (Exception $e) {
            // log error to file with timestamp
            $error = date_default_timezone_set('America/Toronto') . " " . $e->getMessage() . "\n";
            error_log($error, 3, "error.log");
            // redirect user to error page
            header("Location: Error.php?error=500");
            exit;
        } 

        return $con;
    }