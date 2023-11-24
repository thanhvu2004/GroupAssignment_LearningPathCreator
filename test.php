<?php
$email = "test@gmail.com";
$firstname = "test";
$lastname = "test";
$password = "test";
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    // Prepare the data to be written
    try {
        $con = new mysqli("f3411302.gblearn.com", "f3411302_admin", "admin", "f3411302_LearningPathCreator");
        if ($con->connect_error) {
            throw new Exception("Connection failed: " . $con->connect_error);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    $stmt = $con->prepare("INSERT INTO User (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);
    $stmt->execute();
    $stmt->close();
    $con->close();
    $success = "You have successfully registered!";
    echo $success;