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
      return response.text().then((text) => {
        console.log("Raw response:", text);
        try {
          const data = JSON.parse(text);
          // ... rest of your code ...
        } catch (error) {
          console.log("JSON parsing error:", error);
        }
      });
    })
    .then((response) => {
      return response.json().then((data) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
          // window.location.href = "Error.php?error=" + response.statusText;
        }
        return response.json();
        // return data;
      });
    })
    .then((data) => {
      if (data.code === 401) {
        document.getElementById("popup").classList.add("show");
      } else {
        const responseAction = data.action;
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

        const updatedRating = data.updatedRating;
        if (updatedRating !== undefined) {
          document.getElementById("currentRating_" + moduleId).innerText =
            updatedRating;
        }
      }
    })
    .catch((error) => {
      // Handle fetch errors or non-OK responses (other than 401)
      // window.location.href = "Error.php?error=" + error;
      console.log("Fetch Error:", error);
      document.getElementById("downvote_" + moduleId).disabled = false;
      document.getElementById("upvote_" + moduleId).disabled = false;
    });
}
document.getElementById("close").addEventListener("click", function () {
  document.getElementById("popup").classList.remove("show");
});
