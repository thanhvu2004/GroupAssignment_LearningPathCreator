function vote(action, moduleId) {
  document.getElementById("upvote_" + moduleId).disabled = true;
  document.getElementById("downvote_" + moduleId).disabled = true;

  fetch("updateRating.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      action: action,
      module_id: moduleId,
    }),
  })
    .then((response) => {
      if (!response.ok) {
        if (response.status === 401) {
          document.getElementById("popup").classList.add("show");
          console.clear();
        } else {
          window.location.href = "error.php?error="+response.statusText;
        }
      }
      return response.json();
    })
    .then((data) => {
      const responseAction = data.action;
      document.getElementById("downvote_" + moduleId).disabled = false;
      document.getElementById("upvote_" + moduleId).disabled = false;

      if (responseAction === "up") {
        document
          .getElementById("downvote_" + moduleId)
          .classList.remove("selected");
        document.getElementById("upvote_" + moduleId).classList.add("selected");
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

      const updatedRating = data.updatedRating;
      if (updatedRating !== undefined) {
        document.getElementById("currentRating_" + moduleId).innerText =
          updatedRating;
      } 
    })
      .catch((error) => {
        // Handle fetch errors or non-OK responses (other than 401)
        window.location.href = "error.php?error="+error;
        document.getElementById("downvote_" + moduleId).disabled = false;
        document.getElementById("upvote_" + moduleId).disabled = false;
    });
}
document.getElementById("close").addEventListener("click", function () {
  document.getElementById("popup").classList.remove("show");
});