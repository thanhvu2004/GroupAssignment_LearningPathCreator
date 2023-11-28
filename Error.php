<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/main.css?v=1.1">

</head>
<body>
    <?php include "NavBar.php";?>
    <h1>Oops! Something went wrong.</h1>
    <?php 
        // get the error message from the URL
        $error = $_GET['error'];
        if ($error==500){
            echo "<h1>$error</h1>";
            echo "<h2>Internal Server Error</p>";
        } elseif ($error==401){
            echo "<h1>$error</h1>";
            echo "<h2>Unauthorized</p>";
        } elseif ($error==404){
            echo "<h1>$error</h1>";
            echo "<h2>Page Not Found</p>";
        } else {
            echo "<h1>$error</h1>";
            echo "<h2>Something went wrong</p>";
        }
    ?>

</body>
</html>