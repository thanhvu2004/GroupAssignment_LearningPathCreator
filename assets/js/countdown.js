window.onload = function () {
  var countdownNumberEl = document.getElementById("countdown-number");
  var countdown = 4;

  countdownNumberEl.textContent = countdown;

  var countdownInterval = setInterval(function () {
    countdown--;

    if (countdown < 0) {
      clearInterval(countdownInterval);
      window.location.href = "LogIn.php";
    } else {
      countdownNumberEl.textContent = countdown;
    }
  }, 1000);
};