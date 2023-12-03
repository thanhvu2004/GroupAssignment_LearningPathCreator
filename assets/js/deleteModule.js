function confirmDelete(moduleId) {
  if (confirm("Are you sure you want to delete this module?")) {
    // Delete the module
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "DeleteModule.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
      if (this.responseText === "Module deleted successfully") {
        location.reload(); // Reload the page to reflect the changes
      }
    };
    xhr.send("module_id=" + moduleId);
  }
}
