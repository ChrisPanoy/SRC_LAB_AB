<?php
include 'includes/db.php';

echo "<h1>🔍 Live Server Diagnostic</h1>";

// 1. Check Course IDs
echo "<h3>1. Checking Courses Table:</h3>";
$res = $conn->query("SELECT course_id, course_code, course_name FROM courses");
$found_bsis = false;
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['course_id'] . " | Code: " . $row['course_code'] . " | Name: " . $row['course_name'] . "<br>";
    if ($row['course_code'] == 'BSIS') $found_bsis = $row['course_id'];
}
if (!$found_bsis) echo "<b style='color:red;'>⚠️ WARNING: No course with code 'BSIS' found!</b><br>";

// 2. Check Session
echo "<h3>2. Checking Current Session:</h3>";
echo "Active AY ID: " . ($_SESSION['active_ay_id'] ?? 'NULL') . " (" . ($_SESSION['active_ay_name'] ?? 'N/A') . ")<br>";
echo "Active Sem ID: " . ($_SESSION['active_sem_id'] ?? 'NULL') . " (" . ($_SESSION['active_sem_now'] ?? 'N/A') . ")<br>";

// 3. Check General Admissions
echo "<h3>3. Total Admissions on Live Server:</h3>";
$count = $conn->query("SELECT COUNT(*) as total FROM admissions")->fetch_assoc()['total'];
echo "Total rows in admissions table: $count <br>";

if ($count > 0) {
    echo "Displaying distribution by Course ID:<br>";
    $dist = $conn->query("SELECT course_id, COUNT(*) as qty FROM admissions GROUP BY course_id");
    while($d = $dist->fetch_assoc()) {
        echo "Course ID " . $d['course_id'] . ": " . $d['qty'] . " students<br>";
    }
}

// 4. THE AUTO-FIX ATTEMPT
if ($found_bsis && isset($_SESSION['active_ay_id'])) {
    echo "<h3>4. Attempting Auto-Fix:</h3>";
    $ay = $_SESSION['active_ay_id'];
    $sem = $_SESSION['active_sem_id'];
    
    // This finds any admissions that are NOT marked as BSIS but should be
    // We assume if they have NO course or Course ID 1, they might be the missing BSIS ones
    $sql = "UPDATE admissions SET course_id = $found_bsis 
            WHERE academic_year_id = $ay 
            AND semester_id = $sem 
            AND course_id != $found_bsis";
    
    if ($conn->query($sql)) {
        echo "<b style='color:green;'>✅ Success! Updated " . $conn->affected_rows . " records to BSIS (ID: $found_bsis).</b><br>";
    }
}
?>