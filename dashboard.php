<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];
$query = isset($_GET['query']) ? strtolower(trim($_GET['query'])) : '';
$categoryFilter = isset($_GET['category']) ? strtolower(trim($_GET['category'])) : '';
$difficultyFilter = isset($_GET['difficulty']) ? strtolower(trim($_GET['difficulty'])) : '';
$skillFilter = isset($_GET['skill']) ? strtolower(trim($_GET['skill'])) : '';

$csvFile = 'learning_content_improved.csv';
$results = [];
$categories = $difficulties = $skills = [];

if (($handle = fopen($csvFile, 'r')) !== FALSE) {
    $header = fgetcsv($handle);
    while (($row = fgetcsv($handle)) !== FALSE) {
        $data = array_map('strtolower', $row);

        // Collecting filter options
        if (!in_array($data[0], $categories)) $categories[] = $data[0];
        if (!in_array($data[4], $difficulties)) $difficulties[] = $data[4];
        foreach (explode(',', $data[3]) as $skill) {
            $skill = trim($skill);
            if (!in_array($skill, $skills)) $skills[] = $skill;
        }

        // Improved search for partial matches
        $combined = implode(' ', $data);
        $matchesQuery = $query === '' || stripos($combined, $query) !== FALSE;
        $matchesCategory = $categoryFilter === '' || stripos($data[0], $categoryFilter) !== FALSE;
        $matchesDifficulty = $difficultyFilter === '' || stripos($data[4], $difficultyFilter) !== FALSE;
        $matchesSkill = $skillFilter === '' || stripos($data[3], $skillFilter) !== FALSE;

        if ($matchesQuery && $matchesCategory && $matchesDifficulty && $matchesSkill) {
            $results[] = $row;
        }
    }
    fclose($handle);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script src="script.js"></script>
</head>
<body>
    <div class="header">
        <p>Welcome, <strong><?php echo ucfirst($username); ?></strong>!</p>
        <a href="user_profile.php">Profile</a> | 
        <a href="recommendations.php">Recommendations</a> | 
        <a href="user_analytics.php">My Analytics</a> | 
        <a href="admin_analytics.php">Admin View</a> | 
        <a href="logout.php">Logout</a>
        <h2>Education Recommendation System</h2>
    </div>

    <div class="filter-container">
        <form action="dashboard.php" method="GET" class="filter-form">
            <input type="text" name="query" id="search-bar" placeholder="Search keywords..." onkeyup="showSuggestions(this.value)">
            <div id="suggestions" class="suggestions-box"></div>

            <select name="category">
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category ?>" <?= $categoryFilter == $category ? 'selected' : '' ?>><?= ucfirst($category) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="difficulty">
                <option value="">Select Difficulty</option>
                <?php foreach ($difficulties as $difficulty): ?>
                    <option value="<?= $difficulty ?>" <?= $difficultyFilter == $difficulty ? 'selected' : '' ?>><?= ucfirst($difficulty) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="skill">
                <option value="">Select Skill</option>
                <?php foreach ($skills as $skill): ?>
                    <option value="<?= $skill ?>" <?= $skillFilter == $skill ? 'selected' : '' ?>><?= ucfirst($skill) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="results-container">
        <h3>Results Found: <?php echo count($results); ?></h3>
        <div class="results-grid">
            <?php foreach ($results as $result): ?>
                <div class="result-card">
                    <strong>Category:</strong> <?= ucfirst($result[0]) ?><br>
                    <strong>Subcategory:</strong> <?= ucfirst($result[1]) ?><br>
                    <strong>Description:</strong> <?= ucfirst($result[2]) ?><br>
                    <strong>Skills:</strong> <?= ucfirst($result[3]) ?><br>
                    <strong>Difficulty:</strong> <?= ucfirst($result[4]) ?><br>
                    <a href="<?= $result[7] ?>" target="_blank" class="learn-more">Learn More</a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($results)) echo "<p>No results found. Try refining your filters.</p>"; ?>
        </div>
    </div>
</body>
</html>
