document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements - Navigation
  const navItems = document.querySelectorAll(".dashboard-nav li");
  const sections = document.querySelectorAll(".dashboard-section");
  const logoutButton = document.getElementById("logout-button");

  // DOM Elements - User info
  const userGreeting = document.getElementById("user-greeting");
  const usernameDisplay = document.getElementById("username-display");
  const emailDisplay = document.getElementById("email-display");
  const userInitial = document.getElementById("user-initial");

  // DOM Elements - Overview stats
  const totalQuizzesElement = document.getElementById("total-quizzes");
  const avgScoreElement = document.getElementById("avg-score");
  const bestCategoryElement = document.getElementById("best-category");
  const questionsAnsweredElement =
    document.getElementById("questions-answered");
  const recentQuizzesContainer = document.getElementById("recent-quizzes");
  const categoryStatsContainer = document.getElementById("category-stats");

  // DOM Elements - Difficulty stats
  const difficultyStatsContainer = document.getElementById("difficulty-stats");

  // DOM Elements - Settings
  const displayNameInput = document.getElementById("display-name");
  const currentPasswordInput = document.getElementById("current-password");
  const newPasswordInput = document.getElementById("new-password");
  const confirmNewPasswordInput = document.getElementById(
    "confirm-new-password"
  );
  const saveSettingsButton = document.getElementById("save-settings");
  const settingsMessageElement = document.getElementById("settings-message");

  // DOM Elements - History
  const historyItemsContainer = document.getElementById("history-items");
  const categoryFilterSelect = document.getElementById("category-filter");
  const sortFilterSelect = document.getElementById("sort-filter");

  // Check authentication and load dashboard data
  loadUserData();

  // Add event listeners
  navItems.forEach((item) => {
    item.addEventListener("click", () =>
      switchSection(item.getAttribute("data-target"))
    );
  });

  logoutButton.addEventListener("click", handleLogout);

  if (saveSettingsButton) {
    saveSettingsButton.addEventListener("click", saveAccountSettings);
  }

  if (categoryFilterSelect) {
    categoryFilterSelect.addEventListener("change", filterQuizHistory);
  }

  if (sortFilterSelect) {
    sortFilterSelect.addEventListener("change", filterQuizHistory);
  }

  /**
   * Load all user data and update UI
   */
  async function loadUserData() {
    try {
      const userInfoResponse = await fetch(
        "/quizmint/api/auth.php?action=get_user_info"
      );
      const userInfoData = await userInfoResponse.json();

      if (userInfoData.error) {
        // Not authenticated, redirect to login
        window.location.href = "login.php";
        return;
      }

      // User is authenticated, update UI with user info
      updateUserInfo(userInfoData.user);

      // Load dashboard data
      loadDashboardData();
    } catch (error) {
      console.error("Authentication check failed:", error);
      window.location.href = "login.php";
    }
  }

  /**
   * Update UI with user information
   */
  function updateUserInfo(user) {
    userGreeting.textContent = `Welcome, ${user.username}!`;
    usernameDisplay.textContent = user.username;
    emailDisplay.textContent = user.email;

    if (displayNameInput) {
      displayNameInput.value = user.username;
    }

    // Set user initial for the avatar
    if (user.username) {
      userInitial.textContent = user.username.charAt(0).toUpperCase();
    }
  }

  /**
   * Load all dashboard data
   */
  async function loadDashboardData() {
    try {
      const response = await fetch(
        "/quizmint/api/auth.php?action=get_user_stats"
      );
      const data = await response.json();

      if (data.error) {
        console.error("Error loading stats:", data.error);
        return;
      }

      // Update overview stats
      updateOverviewStats(data);

      // Update category stats
      updateCategoryStats(data.category_stats);

      // Update difficulty stats
      updateDifficultyStats(data.difficulty_stats);

      // Update recent quizzes
      updateRecentQuizzes(data.recent_quizzes);

      // Load full quiz history
      updateQuizHistory(data.recent_quizzes || []);

      // Populate category filter select
      populateCategoryFilter(data.category_stats);
    } catch (error) {
      console.error("Failed to load dashboard data:", error);
    }
  }

  /**
   * Update overview statistics
   */
  function updateOverviewStats(data) {
    const overall = data.overall_stats;

    if (!overall) return;

    // Update total quizzes
    totalQuizzesElement.textContent = overall.total_quizzes || "0";

    // Update average score
    const avgPercentage = Math.round(overall.average_percentage || 0);
    avgScoreElement.textContent = `${avgPercentage}%`;

    // Update total questions answered
    questionsAnsweredElement.textContent = overall.total_questions || "0";

    // Find best category
    if (data.category_stats && data.category_stats.length > 0) {
      // Categories are already sorted by performance in the API
      bestCategoryElement.textContent = data.category_stats[0].category || "-";
    }
  }

  /**
   * Update category statistics
   */
  function updateCategoryStats(categoryStats) {
    if (!categoryStatsContainer) return;

    if (!categoryStats || categoryStats.length === 0) {
      categoryStatsContainer.innerHTML =
        '<p class="no-data">No category data available</p>';
      return;
    }

    categoryStatsContainer.innerHTML = "";

    categoryStats.forEach((category) => {
      const percentage = Math.round(category.average_percentage);

      const categoryElement = document.createElement("div");
      categoryElement.className = "category-item";
      categoryElement.innerHTML = `
        <div class="category-name">
          <span>${category.category}</span>
          <span>${percentage}%</span>
        </div>
        <div class="category-progress-bar">
          <div class="category-progress" style="width: ${percentage}%"></div>
        </div>
        <div class="category-details">
          ${category.correct_answers}/${category.total_questions} correct (${category.quizzes_taken} quizzes)
        </div>
      `;

      categoryStatsContainer.appendChild(categoryElement);
    });
  }

  /**
   * Update difficulty statistics
   */
  function updateDifficultyStats(difficultyStats) {
    if (!difficultyStatsContainer) return;

    if (!difficultyStats || difficultyStats.length === 0) {
      difficultyStatsContainer.innerHTML =
        '<p class="no-data">No difficulty data available</p>';
      return;
    }

    difficultyStatsContainer.innerHTML = "";

    // Create elements for each difficulty
    const difficulties = ["easy", "medium", "hard"];

    difficulties.forEach((diff) => {
      // Find matching stat
      const stat = difficultyStats.find(
        (s) => s.difficulty.toLowerCase() === diff
      ) || {
        total_questions: 0,
        correct_answers: 0,
        percentage: 0,
      };

      const percentage = Math.round(stat.percentage || 0);

      const diffElement = document.createElement("div");
      diffElement.className = `difficulty-item ${diff}`;
      diffElement.innerHTML = `
        <div class="difficulty-value">${percentage}%</div>
        <div class="difficulty-label">${
          diff.charAt(0).toUpperCase() + diff.slice(1)
        }</div>
        <div class="difficulty-details">
          ${stat.correct_answers || 0}/${stat.total_questions || 0} correct
        </div>
      `;

      difficultyStatsContainer.appendChild(diffElement);
    });

    // If we have charts, render them here
    renderCharts(difficultyStats);
  }

  /**
   * Update recent quizzes
   */
  function updateRecentQuizzes(quizzes) {
    if (!recentQuizzesContainer) return;

    if (!quizzes || quizzes.length === 0) {
      recentQuizzesContainer.innerHTML =
        '<p class="no-data">No recent quizzes found</p>';
      return;
    }

    recentQuizzesContainer.innerHTML = "";

    // Only show the 5 most recent quizzes
    const recentQuizzesSlice = quizzes.slice(0, 5);

    recentQuizzesSlice.forEach((quiz) => {
      const percentage = Math.round((quiz.score / quiz.total_questions) * 100);
      const date = new Date(quiz.date_taken).toLocaleDateString();

      // Format completion time (seconds to MM:SS)
      const minutes = Math.floor(quiz.completion_time / 60);
      const seconds = quiz.completion_time % 60;
      const formattedTime = `${minutes}:${seconds.toString().padStart(2, "0")}`;

      const quizElement = document.createElement("div");
      quizElement.className = "activity-item";
      quizElement.innerHTML = `
        <div class="activity-details">
          <span>${quiz.category}</span> quiz: ${quiz.score}/${quiz.total_questions} (${percentage}%)
        </div>
        <div class="activity-date">${date} • ${formattedTime}</div>
      `;

      recentQuizzesContainer.appendChild(quizElement);
    });
  }

  /**
   * Update full quiz history
   */
  function updateQuizHistory(quizzes) {
    if (!historyItemsContainer) return;

    if (!quizzes || quizzes.length === 0) {
      historyItemsContainer.innerHTML =
        '<p class="no-data">No quiz history available</p>';
      return;
    }

    filterQuizHistory(); // Apply any filters
  }

  /**
   * Filter and display quiz history
   */
  function filterQuizHistory() {
    // If this function is called before data is loaded, exit
    if (!window.quizData || !historyItemsContainer) return;

    const quizzes = window.quizData.recent_quizzes || [];
    if (quizzes.length === 0) return;

    const categoryFilter = categoryFilterSelect.value;
    const sortFilter = sortFilterSelect.value;

    // Apply category filter
    let filteredQuizzes = quizzes;
    if (categoryFilter !== "all") {
      filteredQuizzes = quizzes.filter(
        (quiz) => quiz.category === categoryFilter
      );
    }

    // Apply sorting
    if (sortFilter === "date-desc") {
      filteredQuizzes.sort(
        (a, b) => new Date(b.date_taken) - new Date(a.date_taken)
      );
    } else if (sortFilter === "date-asc") {
      filteredQuizzes.sort(
        (a, b) => new Date(a.date_taken) - new Date(b.date_taken)
      );
    } else if (sortFilter === "score-desc") {
      filteredQuizzes.sort(
        (a, b) => b.score / b.total_questions - a.score / a.total_questions
      );
    } else if (sortFilter === "score-asc") {
      filteredQuizzes.sort(
        (a, b) => a.score / a.total_questions - b.score / b.total_questions
      );
    }

    // Display filtered and sorted quizzes
    historyItemsContainer.innerHTML = "";

    if (filteredQuizzes.length === 0) {
      historyItemsContainer.innerHTML =
        '<p class="no-data">No quizzes match the selected filters</p>';
      return;
    }

    filteredQuizzes.forEach((quiz) => {
      const percentage = Math.round((quiz.score / quiz.total_questions) * 100);
      const date = new Date(quiz.date_taken).toLocaleDateString();

      // Format completion time (seconds to MM:SS)
      const minutes = Math.floor(quiz.completion_time / 60);
      const seconds = quiz.completion_time % 60;
      const formattedTime = `${minutes}:${seconds.toString().padStart(2, "0")}`;

      const historyItem = document.createElement("div");
      historyItem.className = "history-item";
      historyItem.innerHTML = `
        <span>${date}</span>
        <span>${quiz.category}</span>
        <span>${quiz.score}/${quiz.total_questions} (${percentage}%)</span>
        <span>${formattedTime}</span>
      `;

      historyItemsContainer.appendChild(historyItem);
    });
  }

  /**
   * Populate category filter dropdown
   */
  function populateCategoryFilter(categoryStats) {
    if (!categoryFilterSelect || !categoryStats) return;

    // Clear existing options except "All Categories"
    while (categoryFilterSelect.options.length > 1) {
      categoryFilterSelect.remove(1);
    }

    // Add an option for each category
    const categories = categoryStats.map((cat) => cat.category);
    categories.forEach((category) => {
      const option = document.createElement("option");
      option.value = category;
      option.textContent = category;
      categoryFilterSelect.appendChild(option);
    });
  }

  /**
   * Render data visualization charts
   */
  function renderCharts(difficultyStats) {
    // This is where you would initialize charts if using a charting library
    // For now, we'll keep this function as a placeholder
    console.log(
      "Charts would be rendered here if a chart library was included"
    );
  }

  /**
   * Handle account settings form submission
   */
  async function saveAccountSettings() {
    if (!displayNameInput || !currentPasswordInput) return;

    const displayName = displayNameInput.value.trim();
    const currentPassword = currentPasswordInput.value;
    const newPassword = newPasswordInput.value;
    const confirmNewPassword = confirmNewPasswordInput.value;

    // Simple validation
    if (!displayName) {
      showSettingsMessage("Display name cannot be empty", "error");
      return;
    }

    // If changing password, perform validation
    if (newPassword || confirmNewPassword) {
      if (!currentPassword) {
        showSettingsMessage(
          "Current password is required to set a new password",
          "error"
        );
        return;
      }

      if (newPassword !== confirmNewPassword) {
        showSettingsMessage("New passwords do not match", "error");
        return;
      }

      if (newPassword && newPassword.length < 8) {
        showSettingsMessage(
          "New password must be at least 8 characters long",
          "error"
        );
        return;
      }
    }

    // Disable button during request to prevent double submission
    saveSettingsButton.disabled = true;

    try {
      // Note: This endpoint doesn't exist yet - we'll need to create it
      const response = await fetch(
        "/quizmint/api/auth.php?action=update_user",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            display_name: displayName,
            current_password: currentPassword,
            new_password: newPassword || null,
          }),
        }
      );

      const data = await response.json();

      if (data.error) {
        showSettingsMessage(data.error, "error");
      } else {
        showSettingsMessage("Settings updated successfully", "success");

        // Clear password fields
        currentPasswordInput.value = "";
        if (newPasswordInput) newPasswordInput.value = "";
        if (confirmNewPasswordInput) confirmNewPasswordInput.value = "";

        // Reload user data to show updated information
        loadUserData();
      }
    } catch (error) {
      showSettingsMessage("An error occurred while saving settings", "error");
      console.error("Settings update error:", error);
    }

    saveSettingsButton.disabled = false;
  }

  /**
   * Show settings form message
   */
  function showSettingsMessage(message, type) {
    if (!settingsMessageElement) return;

    settingsMessageElement.textContent = message;
    settingsMessageElement.className = `settings-message ${type}`;
  }

  /**
   * Switch between dashboard sections
   */
  function switchSection(sectionId) {
    // Update navigation
    navItems.forEach((item) => {
      if (item.getAttribute("data-target") === sectionId) {
        item.classList.add("active");
      } else {
        item.classList.remove("active");
      }
    });

    // Show selected section, hide others
    sections.forEach((section) => {
      if (section.id === sectionId) {
        section.classList.add("active");
      } else {
        section.classList.remove("active");
      }
    });
  }

  /**
   * Handle user logout
   */
  async function handleLogout() {
    try {
      await fetch("/quizmint/api/auth.php?action=logout");
      window.location.href = "login.php";
    } catch (error) {
      console.error("Logout failed:", error);
    }
  }
});
