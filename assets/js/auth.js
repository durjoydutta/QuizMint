document.addEventListener("DOMContentLoaded", () => {
  // Check if we're on login or signup page
  const isLoginPage = window.location.pathname.includes("login");

  // DOM Elements
  const usernameInput = document.getElementById("username");
  const passwordInput = document.getElementById("password");
  const authMessage = document.getElementById("auth-message");

  // Additional elements for signup page
  const emailInput = isLoginPage ? null : document.getElementById("email");
  const confirmPasswordInput = isLoginPage
    ? null
    : document.getElementById("confirm-password");

  // Buttons
  const loginButton = isLoginPage
    ? document.getElementById("login-button")
    : null;
  const signupButton = isLoginPage
    ? null
    : document.getElementById("signup-button");

  // Add event listeners
  if (isLoginPage && loginButton) {
    loginButton.addEventListener("click", handleLogin);
  } else if (!isLoginPage && signupButton) {
    signupButton.addEventListener("click", handleSignup);
  }

  /**
   * Handle login form submission
   */
  async function handleLogin() {
    const username = usernameInput.value.trim();
    const password = passwordInput.value;

    // Basic validation
    if (!username || !password) {
      showMessage("Please fill in all fields", "error");
      return;
    }

    try {
      // Disable button during request
      loginButton.disabled = true;

      const response = await fetch("/quizmint/api/auth.php?action=login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          username: username,
          password: password,
        }),
      });

      const data = await response.json();

      if (data.error) {
        showMessage(data.error, "error");
        loginButton.disabled = false;
      } else {
        showMessage("Login successful! Redirecting to quiz...", "success");

        // Redirect to index page (quiz) after a short delay instead of dashboard
        setTimeout(() => {
          window.location.href = "index.php";
        }, 1000);
      }
    } catch (error) {
      showMessage("An error occurred. Please try again.", "error");
      loginButton.disabled = false;
    }
  }

  /**
   * Handle signup form submission
   */
  async function handleSignup() {
    const username = usernameInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Basic validation
    if (!username || !email || !password || !confirmPassword) {
      showMessage("Please fill in all fields", "error");
      return;
    }

    if (password !== confirmPassword) {
      showMessage("Passwords do not match", "error");
      return;
    }

    if (password.length < 8) {
      showMessage("Password must be at least 8 characters long", "error");
      return;
    }

    if (!isValidEmail(email)) {
      showMessage("Please enter a valid email address", "error");
      return;
    }

    try {
      // Disable button during request
      signupButton.disabled = true;

      const response = await fetch("/quizmint/api/auth.php?action=register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          username: username,
          email: email,
          password: password,
        }),
      });

      const data = await response.json();

      if (data.error) {
        showMessage(data.error, "error");
        signupButton.disabled = false;
      } else {
        showMessage(
          "Account created successfully! Redirecting to quiz...",
          "success"
        );

        // Redirect to quiz page after a short delay
        setTimeout(() => {
          window.location.href = "index.php";
        }, 1500);
      }
    } catch (error) {
      showMessage("An error occurred. Please try again.", "error");
      signupButton.disabled = false;
    }
  }

  /**
   * Show a message to the user
   */
  function showMessage(message, type) {
    authMessage.textContent = message;
    authMessage.className = `auth-message ${type}`;
  }

  /**
   * Validate email format
   */
  function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  }
});
