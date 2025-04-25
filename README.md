# QuizMint - Interactive Quiz Application

![QuizMint Banner](https://via.placeholder.com/800x200?text=QuizMint+Interactive+Quiz)

## 📚 Overview

QuizMint is a modern, interactive quiz application built with PHP, JavaScript, and XML. It offers a rich, engaging quiz experience across multiple knowledge categories with a sleek, responsive user interface.

### Features

- **Multiple Quiz Categories**: Geography, Science, History, Technology, Programming, and Trivia
- **Interactive UI**: Clean and modern interface with smooth transitions
- **Progress Tracking**: Visual progress bar and timer
- **Score Analysis**: Detailed performance breakdowns by category and difficulty
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Difficulty Levels**: Questions with easy, medium, and hard difficulty ratings
- **Informative Feedback**: Learn from each question with detailed explanations

## 🖼️ Screenshots

![Category Selection Screen](https://via.placeholder.com/400x225?text=Category+Selection)
![Question Interface](https://via.placeholder.com/400x225?text=Question+Interface)
![Results Screen](https://via.placeholder.com/400x225?text=Results+Screen)

## 🔧 Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP
- **Data Storage**: XML
- **UI Design**: Custom CSS with responsive design
- **Font**: Google Fonts (Inter)

## 🚀 Getting Started

### Prerequisites

- Web server with PHP 7.4+ support (Apache/Nginx)
- Modern web browser

### Installation

1. Clone the repository:

```bash
git clone https://github.com/yourusername/quizmint.git
```

2. Move files to your web server directory:

```bash
mv quizmint /path/to/your/webserver/public_folder
```

3. Ensure proper permissions:

```bash
chmod -R 755 /path/to/your/webserver/public_folder/quizmint
```

4. Access the application:

```
http://localhost/quizmint/
```

## 📁 Project Structure

```
quizmint/
├── index.html          # Main application HTML
├── style.css           # Application styling
├── script.js           # Frontend JavaScript functionality
├── quiz.php            # Backend API handler
├── quiz_data.xml       # Main quiz data file
├── geography.xml       # Geography category questions
├── history.xml         # History category questions
├── programming.xml     # Programming category questions
├── science.xml         # Science category questions
├── technology.xml      # Technology category questions
└── trivia.xml          # Trivia category questions
```

## 🧩 How It Works

1. **Category Selection**: Users begin by selecting a quiz category
2. **Quiz Session**: Questions are presented one by one with multiple-choice options
3. **Immediate Feedback**: After answering, users receive feedback on correctness and explanations
4. **Progress Tracking**: A progress bar shows completion status and a timer tracks quiz duration
5. **Results Analysis**: Upon completion, users get detailed performance statistics

## ✨ Features In Detail

### Quiz Categories

- **Geography**: Countries, capitals, landmarks, and geographical features
- **Science**: Physics, chemistry, biology, and astronomy questions
- **History**: Past events, civilizations, and important figures
- **Technology**: Gadgets, innovations, and tech companies
- **Programming**: Web development, coding languages, and frameworks
- **Trivia**: General knowledge across various topics

### Performance Metrics

- Total score and percentage
- Correct/incorrect answer counts
- Completion time
- Category-specific performance analysis
- Difficulty level performance breakdown

## 🛠️ Customization

### Adding New Questions

Add questions to existing categories by editing the corresponding XML file:

```xml
<question>
    <text>Your Question Here?</text>
    <options>
        <option>Option A</option>
        <option>Option B</option>
        <option>Option C</option>
        <option>Option D</option>
    </options>
    <answer>Correct Answer Here</answer>
    <difficulty>medium</difficulty>
    <category>category_id</category>
    <feedback>Explanation to show users</feedback>
</question>
```

### Creating New Categories

1. Create a new XML file following the existing structure
2. Define category metadata
3. Add questions
4. Update the available categories in `quiz.php`
5. Add the category card in `index.html`

## 📝 License

[MIT License](LICENSE)

## 👥 Contact

For questions or feedback, please [open an issue](https://github.com/durjoydutta/quizmint/issues) or contact us at example@quizmint.com.

---

Made with ❤️ by @durjoydutta
