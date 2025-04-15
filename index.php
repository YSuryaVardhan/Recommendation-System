<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Recommender</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Course Recommender System</h1>
        <p>Find the perfect course tailored to your needs!</p>
    </header>
    <div class="container">
        <form action="results.php" method="POST" class="search-form">
            <div class="form-group">
                <label for="category">Category:</label>
                <select name="category" id="category">
                    <option value="">Any</option>
                    <option value="Artificial Intelligence">Artificial Intelligence</option>
                    <option value="Software Engineering">Software Engineering</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Business Analysis">Business Analysis</option>
                    <option value="Cybersecurity">Cybersecurity</option>
                    <option value="Data Science">Data Science</option>
                </select>
            </div>

            <div class="form-group">
                <label for="skill">Skills:</label>
                <input type="text" name="skill" id="skill" placeholder="e.g., Python, SQL">
            </div>

            <div class="form-group">
                <label for="difficulty">Difficulty Level:</label>
                <select name="difficulty" id="difficulty">
                    <option value="">Any</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>
            </div>

            <div class="form-group">
                <label for="trending">Trending:</label>
                <select name="trending" id="trending">
                    <option value="">Any</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                </select>
            </div>

            <div class="form-group">
                <label for="objective">Learning Objective:</label>
                <select name="objective" id="objective">
                    <option value="">Any</option>
                    <option value="Optimize performance">Optimize Performance</option>
                    <option value="Analyze data effectively">Analyze Data Effectively</option>
                    <option value="Implement advanced techniques">Implement Advanced Techniques</option>
                    <option value="Understand core concepts">Understand Core Concepts</option>
                    <option value="Develop real-world projects">Develop Real-World Projects</option>
                    <option value="Secure systems efficiently">Secure Systems Efficiently</option>
                </select>
            </div>

            <button type="submit" class="btn-primary">Search Courses</button>
        </form>
    </div>
    <footer>
        <p>&copy; 2025 Course Recommender by xAI</p>
    </footer>
</body>
</html>