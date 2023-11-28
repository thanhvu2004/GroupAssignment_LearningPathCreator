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

  objectives.appendChild(objective);
});
