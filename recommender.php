<?php
function getRecommendations($results, $preferredSkill, $preferredDifficulty, $preferredObjective) {
    if (empty($results)) {
        return [];
    }

    $scoredCourses = [];
    foreach ($results as $course) {
        $score = 0;

        // Skill match
        if (!empty($preferredSkill) && stripos($course['Skills_Required'], $preferredSkill) !== false) {
            $score += 4;
        }

        // Difficulty match
        if (!empty($preferredDifficulty) && $course['Difficulty_Level'] === $preferredDifficulty) {
            $score += 3;
        }

        // Objective match
        if (!empty($preferredObjective) && $course['Learning_Objectives'] === $preferredObjective) {
            $score += 3;
        }

        // Trending bonus
        if ($course['Trending'] === 'Yes') {
            $score += 2;
        }

        $scoredCourses[] = ['course' => $course, 'score' => $score];
    }

    // Sort by score
    usort($scoredCourses, function($a, $b) {
        return $b['score'] - $a['score'];
    });

    // Return top recommendations
    return array_column($scoredCourses, 'course');
}
?>