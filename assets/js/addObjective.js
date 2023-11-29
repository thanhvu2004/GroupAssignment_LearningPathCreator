document.getElementById("addObjective").addEventListener("click", function () {
  var objectives = document.getElementById("objectives");
  var objectiveCount =
    objectives.getElementsByClassName("objective").length + 1;

  var objective = document.createElement("div");
  objective.className = "objective";

  var label = document.createElement("label");
  label.htmlFor = "objectiveTitle" + objectiveCount;
  label.textContent = "Objective Title:";
  objective.appendChild(label);

  var input = document.createElement("input");
  input.type = "text";
  input.id = "objectiveTitle" + objectiveCount;
  input.name = "objectiveTitle[]";
  input.required = true;
  objective.appendChild(input);

  label = document.createElement("label");
  label.htmlFor = "objectiveUrl" + objectiveCount;
  label.textContent = "Objective URL:";
  objective.appendChild(label);

  input = document.createElement("input");
  input.type = "url";
  input.id = "objectiveUrl" + objectiveCount;
  input.name = "objectiveUrl[]";
  input.required = true;
  objective.appendChild(input);

  var button = document.createElement("button");
  button.type = "button";
  button.className = "delete btn deleteObjective";
  button.textContent = "Delete";
  button.addEventListener("click", function () {
    deleteObjective(this, objectiveCount);
  });
  objective.appendChild(button);

  objectives.appendChild(objective);
});

function deleteObjective(button, objectiveId) {
  // Delete the objective
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "deleteObjective.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function () {
    if (this.readyState === XMLHttpRequest.DONE && this.status === 200) {
      // Remove the objective element from the DOM
      var objective = button.parentNode;
      objective.parentNode.removeChild(objective);
    }
  };
  xhr.send("objective_id=" + objectiveId);
}