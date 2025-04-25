document.addEventListener("DOMContentLoaded", () => {
  const questionTextElement = document.getElementById("question-text");
  const optionsContainer = document.getElementById("options-container");
  const feedbackContainer = document.getElementById("feedback-container");
  const nextButton = document.getElementById("next-button");
  const resultContainer = document.getElementById("result-container");
  const finalScoreElement = document.getElementById("final-score");
  const restartButton = document.getElementById("restart-button");
  const quizContent = document.getElementById("quiz-content");

  let currentQuestionData = null;
  let selectedOptionButton = null;
  let score = 0;
  let totalQuestions = 0;

  async function fetchQuestion() {
    try {
      const response = await fetch("quiz.php?action=get_question");
      const data = await response.json();

      if (data.quiz_complete) {
        showResults(data.score, data.total_questions);
      } else {
        currentQuestionData = data;
        displayQuestion(data);
      }
    } catch (error) {
      console.error("Error fetching question:", error);
      feedbackContainer.textContent = "Error loading quiz. Please try again.";
      feedbackContainer.className = "feedback error";
    }
  }

  function displayQuestion(data) {
    quizContent.style.display = "block";
    resultContainer.style.display = "none";
    questionTextElement.textContent = `Q${data.question_number}: ${data.question}`;
    optionsContainer.innerHTML = ""; // Clear previous options
    feedbackContainer.innerHTML = ""; // Clear previous feedback
    feedbackContainer.className = "feedback"; // Reset feedback style
    nextButton.disabled = true; // Disable next button until an answer is selected
    selectedOptionButton = null;

    data.options.forEach((option) => {
      const button = document.createElement("button");
      button.textContent = option;
      button.classList.add("option-button");
      button.addEventListener("click", () => selectOption(button, option));
      optionsContainer.appendChild(button);
    });
  }

  function selectOption(button, option) {
    // Deselect previous button if one was selected
    if (selectedOptionButton) {
      selectedOptionButton.classList.remove("selected");
    }
    // Select the new button
    selectedOptionButton = button;
    selectedOptionButton.classList.add("selected");
    nextButton.disabled = false; // Enable next button
  }

  async function submitAnswer() {
    if (!selectedOptionButton) return; // Only proceed if an option is selected

    const selectedAnswer = selectedOptionButton.textContent;
    nextButton.disabled = true; // Disable button while processing

    try {
      const response = await fetch("quiz.php?action=submit_answer", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ answer: selectedAnswer }),
      });
      const result = await response.json();

      // Disable all option buttons after submitting
      const optionButtons = optionsContainer.querySelectorAll(".option-button");
      optionButtons.forEach((btn) => (btn.disabled = true));

      // Provide feedback
      if (result.correct) {
        selectedOptionButton.classList.add("correct");
        feedbackContainer.textContent = "Correct!";
        feedbackContainer.className = "feedback correct";
      } else {
        selectedOptionButton.classList.add("incorrect");
        feedbackContainer.textContent = `Incorrect. The correct answer was: ${result.correct_answer}`;
        feedbackContainer.className = "feedback incorrect";
        // Highlight the correct answer
        optionButtons.forEach((btn) => {
          if (btn.textContent === result.correct_answer) {
            btn.classList.add("correct");
          }
        });
      }

      score = result.score; // Update score locally if needed, though PHP session handles it

      // Decide whether to show next question or results
      if (result.quiz_complete) {
        totalQuestions = result.total_questions;
        // Delay showing results slightly to allow user to see feedback
        setTimeout(() => showResults(score, totalQuestions), 1500);
      } else {
        // Re-enable next button to proceed
        nextButton.textContent = "Next Question";
        nextButton.disabled = false;
        // Change button action to fetch next question
        nextButton.onclick = fetchQuestion; // Reassign click handler
      }
    } catch (error) {
      console.error("Error submitting answer:", error);
      feedbackContainer.textContent =
        "Error submitting answer. Please try again.";
      feedbackContainer.className = "feedback error";
      nextButton.disabled = false; // Re-enable button on error
    }
  }

  function showResults(finalScore, total) {
    quizContent.style.display = "none";
    resultContainer.style.display = "block";
    finalScoreElement.textContent = `${finalScore} / ${total}`;
  }

  async function restartQuiz() {
    try {
      await fetch("quiz.php?action=restart");
      score = 0;
      totalQuestions = 0;
      currentQuestionData = null;
      selectedOptionButton = null;
      nextButton.textContent = "Next Question"; // Reset button text
      nextButton.onclick = submitAnswer; // Reset button action
      fetchQuestion(); // Fetch the first question again
    } catch (error) {
      console.error("Error restarting quiz:", error);
      feedbackContainer.textContent =
        "Error restarting quiz. Please try again.";
      feedbackContainer.className = "feedback error";
    }
  }

  // Initial setup
  nextButton.addEventListener("click", submitAnswer); // Initial action is submit
  restartButton.addEventListener("click", restartQuiz);

  // Load the first question when the page loads
  fetchQuestion();
});
