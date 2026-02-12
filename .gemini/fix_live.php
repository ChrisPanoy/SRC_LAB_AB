<?php
include 'includes/db.php';

echo "<h2>Live Database Data Fix</h2>";

// 1. Find the correct Course ID for BSIS
$courseRes = $conn->query("SELECT course_id FROM courses WHERE course_code = 'BSIS' LIMIT 1");
$course = $courseRes->fetch_assoc();
$bsis_id = $course ? $course['course_id'] : null;

// 2. Find the Active Academic Year and Semester IDs
$ay_id = $_SESSION['active_ay_id'] ?? null;
$sem_id = $_SESSION['active_sem_id'] ?? null;

if (!$bsis_id || !$ay_id || !$sem_id) {
    die("❌ Error: Could not determine IDs. BSIS ID: $bsis_id, AY ID: $ay_id, Sem ID: $sem_id");
}

echo "Detected BSIS ID: $bsis_id <br>";
echo "Detected Active AY ID: $ay_id <br>";
echo "Detected Active Sem ID: $sem_id <br>";

// 3. Update the admissions records
$sql = "UPDATE admissions SET course_id = $bsis_id 
        WHERE academic_year_id = $ay_id 
        AND semester_id = $sem_id 
        AND course_id != $bsis_id";

if ($conn->query($sql)) {
    echo "✅ Successfully updated " . $conn->affected_rows . " student records to BSIS!<br>";
} else {
    echo "❌ Error updating: " . $conn->error;
}

echo "<br><a href='admin/BSISstudents.php'>Go back to Students Page</a>";
?>