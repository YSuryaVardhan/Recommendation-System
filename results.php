<?php
include 'db_connect.php';
include 'recommender.php';
include 'analytics.php';

session_start();

// Initial search parameters
$category = $_POST['category'] ?? '';
$skill = $_POST['skill'] ?? '';
$difficulty = $_POST['difficulty'] ?? '';
$trending = $_POST['trending'] ?? '';
$objective = $_POST['objective'] ?? '';

// Additional filters from results page
$sort = $_GET['sort'] ?? 'relevance';
$page = (int)($_GET['page'] ?? 1);
$perPage = 10;

// Build SQL query
$query = "SELECT * FROM courses WHERE 1=1";
$params = [];

if (!empty($category)) {
    $query .= " AND Category = :category";
    $params[':category'] = $category;
}
if (!empty($skill)) {
    $query .= " AND Skills_Required LIKE :skill";
    $params[':skill'] = "%$skill%";
}
if (!empty($difficulty)) {
    $query .= " AND Difficulty_Level = :difficulty";
    $params[':difficulty'] = $difficulty;
}
if (!empty($trending)) {
    $query .= " AND Trending = :trending";
    $params[':trending'] = $trending;
}
if (!empty($objective)) {
    $query .= " AND Learning_Objectives = :objective";
    $params[':objective'] = $objective;
}

// Sorting
if ($sort === 'difficulty') {
    $query .= " ORDER BY Difficulty_Level ASC";
} elseif ($sort === 'trending') {
    $query .= " ORDER BY Trending DESC";
} else {
    $query .= " ORDER BY id"; // Default relevance
}

// Pagination
$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE " . substr($query, strpos($query, "WHERE")));
$totalStmt->execute($params);
$totalCourses = $totalStmt->fetchColumn();
$totalPages = ceil($totalCourses / $perPage);
$offset = ($page - 1) * $perPage;

$query .= " LIMIT :offset, :perPage";
$params[':offset'] = $offset;
$params[':perPage'] = $perPage;

// Execute query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recommendations
$recommendations = getRecommendations($results, $skill, $difficulty, $objective);

// Analytics
$analytics = generateAnalytics($pdo, $category, $skill, $difficulty);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="style.css">
    <script src="assets/chart.js"></script>
</head>
<body>
    <header>
        <h1>Course Recommendations</h1>
        <a href="index.php" class="btn-secondary">New Search</a>
    </header>

    <div class="container results-page">
        <div class="sidebar">
            <h3>Refine Results</h3>
            <form method="GET" action="results.php">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <input type="hidden" name="skill" value="<?php echo htmlspecialchars($skill); ?>">
                <input type="hidden" name="difficulty" value="<?php echo htmlspecialchars($difficulty); ?>">
                <input type="hidden" name="trending" value="<?php echo htmlspecialchars($trending); ?>">
                <input type="hidden" name="objective" value="<?php echo htmlspecialchars($objective); ?>">

                <label>Sort By:</label>
                <select name="sort" onchange="this.form.submit()">
                    <option value="relevance" <?php echo $sort === 'relevance' ? 'selected' : ''; ?>>Relevance</option>
                    <option value="difficulty" <?php echo $sort === 'difficulty' ? 'selected' : ''; ?>>Difficulty</option>
                    <option value="trending" <?php echo $sort === 'trending' ? 'selected' : ''; ?>>Trending</option>
                </select>
            </form>

            <h3>Analytics</h3>
            <canvas id="categoryChart" width="200" height="200"></canvas>
            <canvas id="difficultyChart" width="200" height="200"></canvas>
        </div>

        <div class="main-content">
            <h2>Recommended Courses (<?php echo count($recommendations); ?> found)</h2>
            <?php if (!empty($recommendations)): ?>
                <div class="course-list">
                    <?php foreach ($recommendations as $course): ?>
                        <div class="course-card">
                            <h3><?php echo htmlspecialchars($course['Subcategory']); ?></h3>
                            <p><?php echo htmlspecialchars($course['Description']); ?></p>
                            <p><strong>Skills:</strong> <?php echo htmlspecialchars($course['Skills_Required']); ?></p>
                            <p><strong>Difficulty:</strong> <?php echo htmlspecialchars($course['Difficulty_Level']); ?></p>
                            <p><strong>Objective:</strong> <?php echo htmlspecialchars($course['Learning_Objectives']); ?></p>
                            <p><strong>Trending:</strong> <?php echo htmlspecialchars($course['Trending']); ?></p>
                            <a href="<?php echo htmlspecialchars($course['Link']); ?>" target="_blank" class="btn-primary">Enroll Now</a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&sort=<?php echo $sort; ?>&category=<?php echo urlencode($category); ?>&skill=<?php echo urlencode($skill); ?>&difficulty=<?php echo urlencode($difficulty); ?>&trending=<?php echo urlencode($trending); ?>&objective=<?php echo urlencode($objective); ?>" class="btn-secondary">Previous</a>
                    <?php endif; ?>
                    <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&sort=<?php echo $sort; ?>&category=<?php echo urlencode($category); ?>&skill=<?php echo urlencode($skill); ?>&difficulty=<?php echo urlencode($difficulty); ?>&trending=<?php echo urlencode($trending); ?>&objective=<?php echo urlencode($objective); ?>" class="btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No courses match your criteria. Try adjusting your filters.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 Course Recommender by xAI</p>
    </footer>

    <script>
        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($analytics['category_distribution'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($analytics['category_distribution'])); ?>,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                }]
            },
            options: { title: { display: true, text: 'Category Distribution' } }
        });

        // Difficulty Distribution Chart
        const difficultyCtx = document.getElementById('difficultyChart').getContext('2d');
        new Chart(difficultyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($analytics['difficulty_distribution'])); ?>,
                datasets: [{
                    label: 'Courses',
                    data: <?php echo json_encode(array_values($analytics['difficulty_distribution'])); ?>,
                    backgroundColor: '#36A2EB'
                }]
            },
            options: { title: { display: true, text: 'Difficulty Distribution' } }
        });
    </script>
</body>
</html>