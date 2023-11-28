function vote(action, moduleId) {
  document.getElementById("upvote_" + moduleId).disabled = true;
  document.getElementById("downvote_" + moduleId).disabled = true;

  fetch("update_rating.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: action,
      module_id: moduleId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.error === "not_logged_in") {
        window.location.href = "login.php";
      } else {
        const updatedRating = data.updatedRating;
        const responseAction = data.action;
        document.getElementById("currentRating_" + moduleId).innerText =
          updatedRating;
        document.getElementById("downvote_" + moduleId).disabled = false;
        document.getElementById("upvote_" + moduleId).disabled = false;

        if (responseAction === "up") {
          document
            .getElementById("downvote_" + moduleId)
            .classList.remove("selected");
          document
            .getElementById("upvote_" + moduleId)
            .classList.add("selected");
        } else if (responseAction === "down") {
          document
            .getElementById("upvote_" + moduleId)
            .classList.remove("selected");
          document
            .getElementById("downvote_" + moduleId)
            .classList.add("selected");
        } else if (responseAction === "cancel") {
          document
            .getElementById("upvote_" + moduleId)
            .classList.remove("selected");
          document
            .getElementById("downvote_" + moduleId)
            .classList.remove("selected");
        }
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      document.getElementById("downvote_" + moduleId).disabled = false;
      document.getElementById("upvote_" + moduleId).disabled = false;
    });
}
