function vote(action, moduleId) {
  document.getElementById("upvote_" + moduleId).disabled = true;
  document.getElementById("downvote_" + moduleId).disabled = true;

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "updateRating.php", true);
  xhr.setRequestHeader("Content-Type", "application/json");

  xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
      var data = JSON.parse(xhr.responseText);
      handleResponse(data, moduleId);
    } else if (xhr.readyState == 4) {
      // Something went wrong with the request
      reEnableButtons(moduleId);
      window.location.reload();
    }
  };

  xhr.send(
    JSON.stringify({
      action: action,
      module_id: moduleId,
    })
  );
}

function reEnableButtons(moduleId) {
  document.getElementById("upvote_" + moduleId).disabled = false;
  document.getElementById("downvote_" + moduleId).disabled = false;
}

function handleResponse(data, moduleId) {
  if (data.code === 401) {
    reEnableButtons(moduleId);
    document.getElementById("popup").classList.add("show");
  } else {
    const responseAction = data.action;
    reEnableButtons(moduleId);

    if (responseAction === "up") {
      document
        .getElementById("downvote_" + moduleId)
        .classList.remove("selected");
      document.getElementById("upvote_" + moduleId).classList.add("selected");
    } else if (responseAction === "down") {
      document
        .getElementById("upvote_" + moduleId)
        .classList.remove("selected");
      document.getElementById("downvote_" + moduleId).classList.add("selected");
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
}

function share(moduleId) {
  var url = window.location.href;
  var link = url.split("?")[0] + "?moduleId=" + moduleId;
  navigator.clipboard.writeText(link);
  document.getElementById("shareLink").value = link;
  document.getElementById("popup2").classList.add("show");
}

function copyLink() {
  var copyText = document.getElementById("shareLink");
  copyText.select();
  copyText.setSelectionRange(0, 99999);
  document.execCommand("copy");
}

function clone(moduleId, creatorName) {
  window.location.href =
    "createPath.php?module_id=" + moduleId + "&creatorName=" + creatorName;
}

// Close the popup when the user clicks outside of it or presses the escape key or clicks the close button with id "close"
window.addEventListener("click", (event) => {
  if (event.target === document.getElementById("popup")) {
    document.getElementById("popup")?.classList.remove("show");
  }
});

document.addEventListener("keydown", (evt) => {
  if (evt.key === "Escape") {
    document.getElementById("popup")?.classList.remove("show");
  }
});

document.getElementById("close")?.addEventListener("click", () => {
  document.getElementById("popup")?.classList.remove("show");
});

// Close the popup when the user clicks outside of it or presses the escape key or clicks the close button with id "close2"
window.addEventListener("click", (event) => {
  if (event.target === document.getElementById("popup2")) {
    document.getElementById("popup2")?.classList.remove("show");
  }
});

document.addEventListener("keydown", (evt) => {
  if (evt.key === "Escape") {
    document.getElementById("popup2")?.classList.remove("show");
  }
});

document.getElementById("close2")?.addEventListener("click", () => {
  document.getElementById("popup2")?.classList.remove("show");
});
