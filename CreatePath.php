<?php
    session_start();
    if (!isset($_SESSION['login_email']) || !isset($_SESSION['fullname'])) {
        header('Location: login.php');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
        <link rel="stylesheet" href="assets/css/navbar.css">
        <link rel="stylesheet" href="assets/css/main.css">
    </head>
    <body>
        <?php include "NavBar.php";?>
        <form id="moduleForm" method="post" action="submitModule.php">
            <label for="moduleTitle">Module Title:</label>
            <input type="text" id="moduleTitle" name="moduleTitle" required>

            <label for="moduleDescription">Module Description:</label>
            <textarea id="moduleDescription" name="moduleDescription" required></textarea>

            <div id="objectives">
                <div class="objective">
                    <label for="objectiveTitle1">Objective Title:</label>
                    <input type="text" id="objectiveTitle1" name="objectiveTitle[]" required>

                    <label for="objectiveUrl1">Objective URL:</label>
                    <input type="url" id="objectiveUrl1" name="objectiveUrl[]" required>
                </div>
            </div>

            <button type="button" id="addObjective">Add Objective</button>
            <input type="submit" value="Submit">
        </form>

        <script>
            document.getElementById('addObjective').addEventListener('click', function() {
                var objectives = document.getElementById('objectives');
                var objectiveCount = objectives.getElementsByClassName('objective').length + 1;

                var objective = document.createElement('div');
                objective.className = 'objective';

                var label = document.createElement('label');
                label.htmlFor = 'objectiveTitle' + objectiveCount;
                label.textContent = 'Objective Title:';
                objective.appendChild(label);

                var input = document.createElement('input');
                input.type = 'text';
                input.id = 'objectiveTitle' + objectiveCount;
                input.name = 'objectiveTitle[]';
                input.required = true;
                objective.appendChild(input);

                label = document.createElement('label');
                label.htmlFor = 'objectiveUrl' + objectiveCount;
                label.textContent = 'Objective URL:';
                objective.appendChild(label);

                input = document.createElement('input');
                input.type = 'url';
                input.id = 'objectiveUrl' + objectiveCount;
                input.name = 'objectiveUrl[]';
                input.required = true;
                objective.appendChild(input);

                objectives.appendChild(objective);
            });
        </script>
    </body>
</html>