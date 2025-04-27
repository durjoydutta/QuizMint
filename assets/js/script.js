document.addEventListener("DOMContentLoaded", () => {
  // DOM Elements
  const elements = {
    questionText: document.getElementById("question-text"),
    optionsContainer: document.getElementById("options-container"),
    feedbackContainer: document.getElementById("feedback-container"),
    submitButton: document.getElementById("submit-button"),
    nextButton: document.getElementById("next-button"),
    resultContainer: document.getElementById("result-container"),
    finalScore: document.getElementById("final-score"),
    correctAnswers: document.getElementById("correct-answers"),
    incorrectAnswers: document.getElementById("incorrect-answers"),
    completionTime: document.getElementById("completion-time"),
    restartButton: document.getElementById("restart-button"),
    changeCategoryButton: document.getElementById("change-category-button"),
    quizContent: document.getElementById("quiz-content"),
    questionNumber: document.getElementById("question-number"),
    totalQuestions: document.getElementById("total-questions"),
    progressBar: document.getElementById("progress-bar"),
    timerValue: document.getElementById("timer-value"),
    timerElement: document.getElementById("timer"),
    categorySelection: document.getElementById("category-selection"),
  };

  // Quiz State
  const state = {
    currentQuestionData: null,
    selectedOptionButton: null,
    score: 0,
    totalQuestions: 0,
    answeredQuestions: 0,
    quizStartTime: null,
    timerInterval: null,
    secondsElapsed: 0,
    categories: {},
    quizMetadata: {},
    selectedCategory: null,
    currentQuestionAnswered: false, // Track if current question is answered
  };

  /**
   * Timer functionality
   */
  const timer = {
    start() {
      state.quizStartTime = new Date();
      state.secondsElapsed = 0;

      clearInterval(state.timerInterval);
      state.timerInterval = setInterval(() => {
        state.secondsElapsed++;
        this.update();
      }, 1000);
    },

    stop() {
      clearInterval(state.timerInterval);
    },

    update() {
      const minutes = Math.floor(state.secondsElapsed / 60);
      const seconds = state.secondsElapsed % 60;
      elements.timerValue.textContent = `${minutes
        .toString()
        .padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;

      // Change color as time increases
      if (state.secondsElapsed > 120) {
        // 2+ minutes
        elements.timerElement.className = "timer danger";
      } else if (state.secondsElapsed > 60) {
        // 1+ minute
        elements.timerElement.className = "timer warning";
      }
    },

    getFormattedTime() {
      const minutes = Math.floor(state.secondsElapsed / 60);
      const seconds = state.secondsElapsed % 60;
      return `${minutes.toString().padStart(2, "0")}:${seconds
        .toString()
        .padStart(2, "0")}`;
    },
  };

  /**
   * API calls to backend
   */
  const api = {
    async fetchAvailableCategories() {
      try {
        const response = await fetch(
          "api/quiz.php?action=get_available_categories"
        );
        return await response.json();
      } catch (error) {
        console.error("Error fetching available categories:", error);
        throw new Error("Failed to load available categories");
      }
    },

    async setCategory(category) {
      try {
        const response = await fetch(
          "api/quiz.php?action=set_category",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({ category: category }),
          }
        );
        return await response.json();
      } catch (error) {
        console.error("Error setting category:", error);
        throw new Error("Failed to set category");
      }
    },

    async fetchQuizInfo() {
      try {
        const response = await fetch(
          "api/quiz.php?action=get_quiz_info"
        );
        return await response.json();
      } catch (error) {
        console.error("Error fetching quiz info:", error);
        throw new Error("Failed to load quiz information");
      }
    },

    async fetchQuestion() {
      try {
        const response = await fetch(
          "api/quiz.php?action=get_question"
        );
        return await response.json();
      } catch (error) {
        console.error("Error fetching question:", error);
        throw new Error("Failed to load question");
      }
    },

    async submitAnswer(selectedAnswer) {
      try {
        const response = await fetch(
          "api/quiz.php?action=submit_answer",
          {
            method: "POST",
            headers: {
              "Content-Type": "application/json",
            },
            body: JSON.stringify({ answer: selectedAnswer }),
          }
        );
        return await response.json();
      } catch (error) {
        console.error("Error submitting answer:", error);
        throw new Error("Failed to submit answer");
      }
    },

    async restartQuiz() {
      try {
        await fetch("api/quiz.php?action=restart");
      } catch (error) {
        console.error("Error restarting quiz:", error);
        throw new Error("Failed to restart quiz");
      }
    },

    async getStats() {
      try {
        const response = await fetch("api/quiz.php?action=get_stats");
        return await response.json();
      } catch (error) {
        console.error("Error fetching stats:", error);
        throw new Error("Failed to load statistics");
      }
    },
  };

  /**
   * Category Selection UI
   */
  const categorySelectionUI = {
    async initialize() {
      try {
        const categoriesData = await api.fetchAvailableCategories();
        this.renderCategories(categoriesData.categories);
      } catch (error) {
        console.error("Error initializing categories:", error);
        this.showError(
          "Failed to load quiz categories. Please try refreshing the page."
        );
      }
    },

    renderCategories(categories) {
      const categoryCards = document.querySelectorAll(".category-card");

      // Add click listeners to category cards
      categoryCards.forEach((card) => {
        const categoryId = card.getAttribute("data-category");

        card.addEventListener("click", async () => {
          try {
            // Visually select the category
            categoryCards.forEach((c) => c.classList.remove("selected"));
            card.classList.add("selected");

            // Set the category on the server
            const result = await api.setCategory(categoryId);
            state.selectedCategory = categoryId;

            // Start the quiz with the selected category
            setTimeout(() => {
              elements.categorySelection.style.display = "none";
              elements.quizContent.style.display = "block";
              quizUI.initialize();
            }, 500);
          } catch (error) {
            this.showError("Failed to select category. Please try again.");
          }
        });
      });
    },

    show() {
      elements.categorySelection.style.display = "block";
      elements.quizContent.style.display = "none";
      elements.resultContainer.style.display = "none";
    },

    showError(message) {
      // Display error message for category selection
      alert(message);
    },
  };

  /**
   * Quiz UI management
   */
  const quizUI = {
    async initialize() {
      try {
        // Fetch quiz metadata and categories
        const quizInfo = await api.fetchQuizInfo();
        state.categories = quizInfo.categories || {};
        state.quizMetadata = quizInfo.metadata || {};
        state.selectedCategory = quizInfo.selected_category || null;

        // Update page title if metadata is available
        if (state.quizMetadata.title) {
          document.title = `${state.quizMetadata.title} | QuizMint`;
        }

        // Start the quiz
        this.loadQuestion();
      } catch (error) {
        this.showError("Error initializing quiz. Please try again.");
      }
    },

    async loadQuestion() {
      try {
        // Reset the question answered state
        state.currentQuestionAnswered = false;

        const data = await api.fetchQuestion();

        if (data.quiz_complete) {
          this.showResults(data.score, data.total_questions);
        } else {
          state.currentQuestionData = data;
          state.totalQuestions = data.total_questions;
          elements.totalQuestions.textContent = data.total_questions;
          this.displayQuestion(data);

          // Start timer if this is the first question
          if (data.question_number === 1) {
            timer.start();
          }

          // Update progress
          this.updateProgress(data.question_number, data.total_questions);

          // Reset buttons
          elements.submitButton.style.display = "block";
          elements.submitButton.disabled = true;
          elements.nextButton.style.display = "none";
        }
      } catch (error) {
        this.showError("Error loading quiz. Please try again.");
      }
    },

    displayQuestion(data) {
      elements.quizContent.style.display = "block";
      elements.resultContainer.style.display = "none";
      elements.categorySelection.style.display = "none";
      elements.questionText.textContent = data.question;
      elements.optionsContainer.innerHTML = "";
      elements.feedbackContainer.innerHTML = "";
      elements.feedbackContainer.className = "feedback";
      elements.questionNumber.textContent = `Question ${data.question_number}`;
      state.selectedOptionButton = null;

      // Remove any existing metadata
      const existingMeta = document.querySelector(".question-meta");
      if (existingMeta) {
        existingMeta.remove();
      }

      // Add difficulty and category indicators if available
      if (data.difficulty || data.category) {
        const questionMeta = document.createElement("div");
        questionMeta.className = "question-meta";

        if (data.difficulty) {
          const difficultyBadge = document.createElement("span");
          difficultyBadge.className = `difficulty-badge ${data.difficulty}`;
          difficultyBadge.textContent =
            data.difficulty.charAt(0).toUpperCase() + data.difficulty.slice(1);
          questionMeta.appendChild(difficultyBadge);
        }

        if (data.category) {
          const categoryBadge = document.createElement("span");
          categoryBadge.className = "category-badge";
          categoryBadge.textContent = data.category;
          questionMeta.appendChild(categoryBadge);
        }

        elements.questionText.parentNode.insertBefore(
          questionMeta,
          elements.questionText
        );
      }

      // Create option buttons with enhanced styling
      data.options.forEach((option) => {
        const button = document.createElement("button");
        button.classList.add("option-button");

        // Create marker element (circle)
        const marker = document.createElement("span");
        marker.classList.add("option-marker");

        // Create text element
        const text = document.createElement("span");
        text.classList.add("option-text");
        text.textContent = option;

        // Append elements to button
        button.appendChild(marker);
        button.appendChild(text);

        button.addEventListener("click", () =>
          this.selectOption(button, option)
        );
        elements.optionsContainer.appendChild(button);
      });
    },

    selectOption(button, option) {
      // Only allow selection if question hasn't been answered yet
      if (state.currentQuestionAnswered) return;

      // Deselect previous button if one was selected
      if (state.selectedOptionButton) {
        state.selectedOptionButton.classList.remove("selected");
      }

      // Select the new button
      state.selectedOptionButton = button;
      state.selectedOptionButton.classList.add("selected");
      elements.submitButton.disabled = false;
    },

    updateProgress(current, total) {
      const percentage = (current / total) * 100;
      elements.progressBar.style.width = `${percentage}%`;
    },

    async handleAnswerSubmission() {
      if (!state.selectedOptionButton || state.currentQuestionAnswered) return;

      const selectedAnswer =
        state.selectedOptionButton.querySelector(".option-text").textContent;
      elements.submitButton.disabled = true;

      try {
        const result = await api.submitAnswer(selectedAnswer);

        // Mark question as answered
        state.currentQuestionAnswered = true;

        // Disable all option buttons after submitting
        const optionButtons =
          elements.optionsContainer.querySelectorAll(".option-button");
        optionButtons.forEach((btn) => (btn.disabled = true));

        // Show feedback with animation
        if (result.correct) {
          state.selectedOptionButton.classList.add("correct");
          elements.feedbackContainer.textContent = "Correct!";
          elements.feedbackContainer.className = "feedback correct visible";
        } else {
          state.selectedOptionButton.classList.add("incorrect");
          elements.feedbackContainer.textContent = `Incorrect. The correct answer was: ${result.correct_answer}`;
          elements.feedbackContainer.className = "feedback incorrect visible";

          // Highlight the correct answer
          optionButtons.forEach((btn) => {
            if (
              btn.querySelector(".option-text").textContent ===
              result.correct_answer
            ) {
              btn.classList.add("correct");
            }
          });
        }

        // Display additional feedback if available
        if (result.feedback) {
          const feedbackDetailElement = document.createElement("p");
          feedbackDetailElement.className = "feedback-detail";
          feedbackDetailElement.textContent = result.feedback;
          elements.feedbackContainer.appendChild(feedbackDetailElement);
        }

        state.score = result.score;
        state.answeredQuestions++;

        // Decide whether to show next question button or results
        if (result.quiz_complete) {
          // Delay showing results slightly to allow user to see feedback
          setTimeout(
            () => this.showResults(result.score, result.total_questions),
            2000
          );
        } else {
          // Show next button after submission
          elements.submitButton.style.display = "none";
          elements.nextButton.style.display = "block";
        }
      } catch (error) {
        this.showError("Error submitting answer. Please try again.");
        elements.submitButton.disabled = false;
      }
    },

    async showResults(finalScore, totalQuestions) {
      timer.stop();

      elements.quizContent.style.display = "none";
      elements.resultContainer.style.display = "block";
      elements.finalScore.textContent = `${finalScore}/${totalQuestions}`;

      // Use basic calculation for correct answers if not available from the API
      const correctAnswersCount =
        totalQuestions - (totalQuestions - finalScore);
      elements.correctAnswers.textContent = correctAnswersCount;
      elements.incorrectAnswers.textContent =
        totalQuestions - correctAnswersCount;
      elements.completionTime.textContent = timer.getFormattedTime();

      try {
        // Get detailed stats if available
        const stats = await api.getStats();

        // If we have category performance data, display it
        if (
          stats.category_performance &&
          Object.keys(stats.category_performance).length > 0
        ) {
          // Remove any existing category results
          const existingCategoryResults =
            document.querySelector(".category-results");
          if (existingCategoryResults) {
            existingCategoryResults.remove();
          }

          const categoryResults = document.createElement("div");
          categoryResults.className = "category-results";
          categoryResults.innerHTML = "<h3>Performance by Category</h3>";

          const catList = document.createElement("ul");
          catList.className = "category-list";

          Object.entries(stats.category_performance).forEach(
            ([category, data]) => {
              const catItem = document.createElement("li");
              const percent = Math.round((data.correct / data.total) * 100);
              catItem.innerHTML = `
              <span class="category-name">${category}:</span> 
              <span class="category-score">${data.correct}/${data.total} (${percent}%)</span>
              <div class="category-bar">
                <div class="category-progress" style="width: ${percent}%"></div>
              </div>
            `;
              catList.appendChild(catItem);
            }
          );

          categoryResults.appendChild(catList);
          // Insert before the restart button's parent (result-actions)
          const resultActions = document.querySelector(".result-actions");
          elements.resultContainer.insertBefore(categoryResults, resultActions);
        }
      } catch (error) {
        console.error("Could not load detailed statistics", error);
      }

      // Add some animation when showing results
      elements.resultContainer.classList.add("fadeIn");
    },

    showError(message) {
      elements.feedbackContainer.textContent = message;
      elements.feedbackContainer.className = "feedback error visible";
    },

    async restartQuiz() {
      try {
        await api.restartQuiz();

        // Reset local state
        state.score = 0;
        state.answeredQuestions = 0;
        state.currentQuestionData = null;
        state.selectedOptionButton = null;
        state.currentQuestionAnswered = false;

        // Reset UI elements
        elements.resultContainer.classList.remove("fadeIn");

        // Remove any category results if they exist
        const categoryResults = document.querySelector(".category-results");
        if (categoryResults) {
          categoryResults.remove();
        }

        // Start timer again
        timer.start();

        // Load the first question
        this.loadQuestion();
      } catch (error) {
        this.showError("Error restarting quiz. Please try again.");
      }
    },

    changeCategory() {
      // Stop any running timer
      timer.stop();

      // Show category selection screen
      categorySelectionUI.show();
    },
  };

  // Initial setup
  elements.submitButton.addEventListener("click", () =>
    quizUI.handleAnswerSubmission()
  );
  elements.nextButton.addEventListener("click", () => quizUI.loadQuestion());
  elements.restartButton.addEventListener("click", () => quizUI.restartQuiz());
  elements.changeCategoryButton.addEventListener("click", () =>
    quizUI.changeCategory()
  );

  // Initialize the category selection UI
  categorySelectionUI.initialize();
});
