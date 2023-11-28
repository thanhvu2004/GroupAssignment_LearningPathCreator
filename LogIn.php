<?php
    session_start();
    if (isset($_SESSION['login_email']) || isset($_SESSION['fullname'])) {
        $user_id = $_SESSION['user_id'];
    }
    include "checkConnection.php";
    $errors = array('login' => '');
    function loginValidate($login_email, $login_password) {
        $con = checkConnectionDb();
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

    function getUserId($login_email){
        $con = checkConnectionDb();
        $stmt = $con->prepare("SELECT user_id FROM User WHERE email = ?");
        $stmt->bind_param("s", $login_email);
        $stmt->execute();
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
        $con->close();
        return $user_id;
    }

    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $login_email = strtolower($_POST['login_email']); // case insensitive
        $login_password = $_POST['login_password'];
        $fullname = loginValidate($login_email, $login_password);
        $user_id = getUserId($login_email);
        if ($fullname) {
            $_SESSION['login_email'] = $login_email;
            $_SESSION['loggedIn'] = true;
            $_SESSION['fullname'] = $fullname;
            $_SESSION['user_id'] = $user_id;
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
    <link rel="stylesheet" href="assets/css/main.css?v=1.5">
</head>
<body>
    <?php include "navbar.php";?>
    <?php 
        if(isset($_GET['error'])){
            if($_GET['error'] == 401){
                echo "<h2>You must be logged in before create and share your path!</h2>";
            }
        }
    ?>
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
        <span>Don't have an account? </span><a href="SignUp.php">Join Us</a>
    </form>
</body>
</html>