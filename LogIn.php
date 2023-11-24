<?php
    session_start();
    $errors = array('login' => '');
    function loginValidate($login_email, $login_password) {
        try {
            $con = new mysqli("f3411302.gblearn.com", "f3411302_admin", "admin", "f3411302_LearningPathCreator");
            if ($con->connect_error) {
                throw new Exception("Connection failed: " . $con->connect_error);
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        $stmt = $con->prepare("SELECT email, password, first_name, last_name FROM User WHERE email = ?");
        $stmt->bind_param("s", $login_email);
        $stmt->execute();
        $stmt->bind_result($email, $password, $firstname, $lastname);
        $stmt->fetch();
        $stmt->close();
        $con->close();
        if ($email && password_verify($login_password, $password)) {
            return $firstname . " " . $lastname;
        }
        return false;
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $login_email = strtolower($_POST['login_email']); // case insensitive
        $login_password = $_POST['login_password'];
        $fullname = loginValidate($login_email, $login_password);
        if ($fullname) {
            $_SESSION['login_email'] = $login_email;
            $_SESSION['loggedIn'] = true;
            $_SESSION['fullname'] = $fullname;
            header('Location: index.php');
        } else {
            $errors['login'] = "Invalid email or password";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MainPage</title>
    <link rel="stylesheet" href="assets/css/navbar.css?v=1.2">
    <link rel="stylesheet" href="assets/css/main.css?v=1.3">
</head>
<body>
    <?php include "navbar.php";?>
    <h1>Log in</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <!-- email -->
        <label for="login_email">Email:</label><br>
        <input type="email" id="login_email" name="login_email" required><br>
        <!-- password -->
        <label for="login_password">Password:</label><br>
        <input type="password" id="login_password" name="login_password" required><br>
        <!-- submit -->
        <input type="submit" value="Log In!">  
        <span class="error"><?php echo $errors['login']; ?></span><br>

    </form>
</body>
</html>