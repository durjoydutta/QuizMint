// Avatar dropdown functionality
document.addEventListener("DOMContentLoaded", function () {
  const userAvatar = document.getElementById("user-avatar");
  const userMenu = document.getElementById("user-menu");
  const logoutButton = document.getElementById("logout-button");

  if (userAvatar && userMenu) {
    // Toggle menu on avatar click
    userAvatar.addEventListener("click", function () {
      userMenu.classList.toggle("active");
    });

    // Close menu when clicking outside
    document.addEventListener("click", function (event) {
      if (
        !userAvatar.contains(event.target) &&
        !userMenu.contains(event.target)
      ) {
        userMenu.classList.remove("active");
      }
    });
  }

  // Logout functionality
  if (logoutButton) {
    logoutButton.addEventListener("click", async function () {
      try {
        await fetch("api/auth.php?action=logout");
        window.location.href = "login.php";
      } catch (error) {
        console.error("Logout failed:", error);
      }
    });
  }
});
