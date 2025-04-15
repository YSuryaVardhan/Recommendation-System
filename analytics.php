<?php
function generateAnalytics($pdo, $category, $skill, $difficulty) {
    $analytics = [];

    // Category Distribution
    $catStmt = $pdo->query("SELECT Category, COUNT(*) as count FROM courses GROUP BY Category");
    $analytics['category_distribution'] = [];
    while ($row = $catStmt->fetch(PDO::FETCH_ASSOC)) {
        $analytics['category_distribution'][$row['Category']] = $row['count'];
    }

    // Difficulty Distribution
    $diffStmt = $pdo->query("SELECT Difficulty_Level, COUNT(*) as count FROM courses GROUP BY Difficulty_Level");
    $analytics['difficulty_distribution'] = [];
    while ($row = $diffStmt->fetch(PDO::FETCH_ASSOC)) {
        $analytics['difficulty_distribution'][$row['Difficulty_Level']] = $row['count'];
    }

    return $analytics;
}
?>  