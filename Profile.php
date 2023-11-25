<?php
session_start();

if (!isset($_SESSION['login_email']) || !isset($_SESSION['fullname'])) {
    header('Location: login.php');
    exit;
}

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

    // Retrieve the image data based on the user_id
    $stmt = $con->prepare("SELECT image_data FROM UserImages WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $image_info = getimagesizefromstring($row['image_data']);
        $image_type = $image_info['mime'];
        $image_data = 'data:' . $image_type . ';base64,' . base64_encode($row['image_data']);
    } else {
        $image_data = 'assets/img/default.png';
    }
} else {
    // User not found by email
    $image_data = 'assets/img/default.png';
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home page</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>
    <?php include "navbar.php"; ?>
    <!-- Profile -->
    <h1>Profile</h1>
    <div class="container">
        <!-- display user avatar -->
        <div class="bio1">
            <img src="<?php echo $image_data; ?>" alt="avatar">        
            <a class="btn center" id="updProfile" href="updateProfile.php">Update Profile</a>
        </div>
    </div>
</body>
</html>
