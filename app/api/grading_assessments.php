<?php
/** API: Load assessments for a course (for report dropdowns) */
header('Content-Type: application/json');
$u = require_login();
$courseId = (int)($_GET['course'] ?? 0);
$assessments = Database::all(
    "SELECT a.id, a.title, a.type_slug, a.max_mark, ats.label AS type_label
     FROM assessments a LEFT JOIN assessment_types ats ON ats.slug = a.type_slug
     WHERE a.course_id = ? AND a.status = 'published' ORDER BY ats.sort_order", [$courseId]);
json_out(['assessments' => $assessments]);
