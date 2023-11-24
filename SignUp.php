<?php
    session_start();
    $errors = array('email' => '', 'firstname' => '', 'lastname' => '', 'password' => '');
    $success = '';
    function registrationValidate($email, $firstname, $lastname, $password){
        global $errors;

        // Check if email is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Invalid email format";
        }

        // Check if first and last name is valid
        if (!preg_match("/^[a-zA-Z-' ]*$/", $firstname)) {
            $errors['firstname'] = "Only letters and white space allowed";
        }
        if (!preg_match("/^[a-zA-Z-' ]*$/", $lastname)) {
            $errors['lastname'] = "Only letters and white space allowed";
        }
        // Check if password is valid
        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.@])[A-Za-z\d@$!%*?&.@]{8,}$/", $password)) {
            $errors['password'] = "Password must contain at least 8 characters, an uppercase letter, a lowercase letter, a number, and a special character";
        }
        if (empty($errors['email']) && empty($errors['firstname']) && empty($errors['lastname']) && empty($errors['password'])) {
            return true;
        }
        return false;
        
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = strtolower($_POST['email']);
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $password = $_POST['password'];
        if (registrationValidate($email, $firstname, $lastname, $password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // Prepare the data to be written
            $pdo = new PDO('mysql:host=localhost;dbname=f3411302_LearningPathCreator;charset=utf8', 'root', '');
            $stmt = $pdo->prepare("INSERT INTO users (email, firstname, lastname, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$email, $firstname, $lastname, $hashed_password]);
            $success = "You have successfully registered!";
            $pdo = null;
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="assets/css/navbar.css?v=1.2">
    <link rel="stylesheet" href="assets/css/main.css?v=1.3">
</head>
<body>
    <?php include "navbar.php";?>
    <h1>Sign up to be a tutor!</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <!-- email -->
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br>
        <span class="error"><?php echo $errors['email']; ?></span><br>
        <!-- full name -->
        <div id="fullname">
            <div>
                <label for="firstname">First Name:</label><br>
                <input type="text" id="firstname" name="firstname" required><br>
                <span class="error"><?php echo $errors['firstname']; ?></span><br>
            </div>
            <div>
                <label for="lastname">Last Name:</label><br>
                <input type="text" id="lastname" name="lastname" required><br>
                <span class="error"><?php echo $errors['lastname']; ?></span><br>
            </div>
        </div>

        <!-- password -->
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br>
        <span class="error"><?php echo $errors['password']; ?></span><br>

        <input type="submit" value="Sign up!">  
    </form>
</body>
</html>