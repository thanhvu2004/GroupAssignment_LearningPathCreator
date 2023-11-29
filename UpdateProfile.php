<?php
    ini_set('display_errors', 0);
    session_start();

    if (!isset($_SESSION['login_email']) || !isset($_SESSION['fullname'])) {
        header('Location: login.php?error=401');
        exit;
    }
    include "checkConnection.php";

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
        $con = checkConnectionDb();

        // Retrieve the user_id based on the email
        $stmt = $con->prepare("SELECT user_id FROM User WHERE email = ?");
        $stmt->bind_param("s", $_SESSION['login_email']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['user_id'];

            // Check file upload and handle image processing
            if ($_FILES['image']['error'] == 0) {
                $imageData = handleImageUpload($_FILES['image']);

                if ($imageData !== false) {
                    $imageType = $_FILES['image']['type']; // Get image type

                    // Check if the user already has an image in the UserImages table
                    $checkStmt = $con->prepare("SELECT user_id FROM UserImages WHERE user_id = ?");
                    $checkStmt->bind_param("i", $user_id);
                    $checkStmt->execute();
                    $checkResult = $checkStmt->get_result();

                    if ($checkResult->num_rows > 0) {
                        // Update the UserImages table with the new image data and type
                        $updateStmt = $con->prepare("UPDATE UserImages SET image_data = ?, image_type = ? WHERE user_id = ?");
                        $updateStmt->bind_param("ssi", $imageData, $imageType, $user_id);
                        $updateStmt->execute();
                    } else {
                        // Insert new record into UserImages table
                        $insertStmt = $con->prepare("INSERT INTO UserImages (image_data, image_type, user_id) VALUES (?, ?, ?)");
                        $insertStmt->bind_param("ssi", $imageData, $imageType, $user_id);
                        $insertStmt->execute();
                    }

                    echo "Image uploaded successfully!";
                } else {
                    echo "Error: Unable to process the uploaded image.";
                    $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: Unable to process the uploaded image.";
                    error_log($error . "\n", 3, "logs/errors.log");
                }
            } else {
                echo "Error: There was a problem with the uploaded file.";
                $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: There was a problem with the uploaded file.";
                error_log($error . "\n", 3, "logs/errors.log");
            }
        } else {
            echo "User not found.";
            $error = date_default_timezone_set('America/Toronto') . " - " . date('m/d/Y h:i:s a', time()) . " - " . "Error: User not found.";
            error_log($error . "\n", 3, "logs/errors.log");
        }
        $con->close();
        header('Location: profile.php');
    }

    // Function to handle image upload and processing
    function handleImageUpload($imageFile)
    {
        $imageData = false;

        // Check if the file is an image
        $imageInfo = getimagesize($imageFile['tmp_name']);

        if ($imageInfo !== false) {
            if ($imageInfo['mime'] == 'image/jpeg' || $imageInfo['mime'] == 'image/png' || $imageInfo['mime'] == 'image/gif') {
                $imageData = file_get_contents($imageFile['tmp_name']);
            } else {
                return false;
            }
        } else {
            return false;
        }

        $newImage = imagecreatetruecolor(500, 500);
        $white = imagecolorallocate($newImage, 255, 255, 255);
        imagefill($newImage, 0, 0, $white);

        // resize uploaded image so that the biggest side is 500px
        if ($imageInfo[0] > $imageInfo[1]) {
            $newWidth = 500;
            $newHeight = 500 * ($imageInfo[1] / $imageInfo[0]);
        } else {
            $newWidth = 500 * ($imageInfo[0] / $imageInfo[1]);
            $newHeight = 500;
        }

        $dst_x = (500 - $newWidth) / 2;
        $dst_y = (500 - $newHeight) / 2;

        $image = imagecreatefromstring($imageData);
        imagecopyresampled($newImage, $image, $dst_x, $dst_y, 0, 0, $newWidth, $newHeight, $imageInfo[0], $imageInfo[1]);
        ob_start();
        imagejpeg($newImage);
        $imageData = ob_get_contents();
        ob_end_clean();

        return $imageData;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile Image</title>
    <link rel="stylesheet" href="assets/css/main.css?v=1.1">
</head>

<body>
    <form method="post" enctype="multipart/form-data" style="margin-top: 20px;">
        <label for="image">Profile Image:</label>
        <input type="file" id="image" name="image">
        <br>
        <input type="submit" value="Update Profile">
    </form>
    <br><a class="btn" href="profile.php">Back to Profile</a>
</body>

</html>