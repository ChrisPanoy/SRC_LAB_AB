<?php
include 'includes/db.php';

$ay_id  = (int)($_SESSION['active_ay_id'] ?? 0);
$sem_id = (int)($_SESSION['active_sem_id'] ?? 0);

echo "Testing BSISstudents.php query\n";
echo "AY ID: $ay_id, Sem ID: $sem_id\n\n";

// Get BSIS course IDs
$courseIds = [];
$courseRes = $conn->query("SELECT course_id FROM courses WHERE course_code = 'BSIS' OR course_name LIKE 'Bachelor of Science in Information System%'");
if ($courseRes) {
    while ($c = $courseRes->fetch_assoc()) {
        $courseIds[] = (int)$c['course_id'];
    }
}
if (empty($courseIds)) {
    $courseIds = [-1];
}
$idList = implode(',', $courseIds);
echo "BSIS Course IDs: $idList\n\n";

// Run the exact query from BSISstudents.php
$sql = "SELECT DISTINCT st.student_id, yl.year_name, yl.level, sct.section_name,
               st.first_name, st.middle_name, st.last_name, st.suffix, st.gender
        FROM admissions a
        JOIN students st    ON a.student_id     = st.student_id
        JOIN year_levels yl  ON a.year_level_id  = yl.year_id
        JOIN sections sct    ON a.section_id     = sct.section_id
        JOIN courses c       ON a.course_id      = c.course_id
        WHERE a.course_id IN ($idList)
          AND a.academic_year_id = $ay_id
          AND a.semester_id = $sem_id
        ORDER BY yl.level, sct.section_name, st.last_name, st.first_name, st.student_id";

echo "Running query...\n";
$result = $conn->query($sql);

if (!$result) {
    echo "ERROR: " . $conn->error . "\n";
} else {
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $count++;
        if ($count <= 5) {
            echo "Student: {$row['student_id']} - {$row['last_name']}, {$row['first_name']} - Year {$row['year_name']} Section {$row['section_name']}\n";
        }
    }
    echo "\nTotal students returned: $count\n";
}
