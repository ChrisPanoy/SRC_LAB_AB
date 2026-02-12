<?php
/**
 * UNIVERSAL FIX SCRIPT FOR BSIS STUDENTS
 * This script works on both localhost and live server
 * It will diagnose and fix the BSIS student data issue
 */

include 'includes/db.php';

echo "<!DOCTYPE html><html><head><title>BSIS Fix Script</title>";
echo "<style>body{font-family:Arial;padding:20px;} h2{color:#0066cc;} .success{color:green;} .error{color:red;} .info{color:#666;}</style>";
echo "</head><body>";

echo "<h1>🔧 BSIS Students Fix Script</h1>";

// Step 1: Check environment
echo "<h2>Step 1: Environment Check</h2>";
$dbName = $conn->query("SELECT DATABASE() as db")->fetch_assoc()['db'];
echo "<p class='info'>Database: <strong>$dbName</strong></p>";

// Step 2: Check courses
echo "<h2>Step 2: Course Check</h2>";
$courseRes = $conn->query("SELECT course_id, course_code, course_name FROM courses");
$bsis_id = null;
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Code</th><th>Name</th></tr>";
while ($c = $courseRes->fetch_assoc()) {
    echo "<tr><td>{$c['course_id']}</td><td>{$c['course_code']}</td><td>{$c['course_name']}</td></tr>";
    if ($c['course_code'] == 'BSIS') {
        $bsis_id = $c['course_id'];
    }
}
echo "</table>";

if (!$bsis_id) {
    echo "<p class='error'>❌ ERROR: No BSIS course found! Please create it first.</p>";
    exit;
}
echo "<p class='success'>✅ BSIS Course ID: $bsis_id</p>";

// Step 3: Check active academic settings
echo "<h2>Step 3: Active Academic Year & Semester</h2>";
$ay_id = $_SESSION['active_ay_id'] ?? null;
$sem_id = $_SESSION['active_sem_id'] ?? null;
$ay_name = $_SESSION['active_ay_name'] ?? 'N/A';
$sem_name = $_SESSION['active_sem_now'] ?? 'N/A';

echo "<p class='info'>Active AY: <strong>$ay_name</strong> (ID: $ay_id)</p>";
echo "<p class='info'>Active Semester: <strong>$sem_name</strong> (ID: $sem_id)</p>";

if (!$ay_id || !$sem_id) {
    echo "<p class='error'>❌ ERROR: No active academic year/semester set!</p>";
    exit;
}

// Step 4: Check current BSIS admissions
echo "<h2>Step 4: Current BSIS Admissions</h2>";
$checkSql = "SELECT COUNT(*) as count FROM admissions 
             WHERE course_id = $bsis_id 
             AND academic_year_id = $ay_id 
             AND semester_id = $sem_id";
$current = $conn->query($checkSql)->fetch_assoc()['count'];
echo "<p class='info'>Current BSIS students in active term: <strong>$current</strong></p>";

// Step 5: Check all admissions distribution
echo "<h2>Step 5: All Admissions Distribution</h2>";
$distSql = "SELECT c.course_code, COUNT(*) as count 
            FROM admissions a 
            JOIN courses c ON a.course_id = c.course_id 
            WHERE a.academic_year_id = $ay_id AND a.semester_id = $sem_id
            GROUP BY a.course_id";
$distRes = $conn->query($distSql);
echo "<table border='1' cellpadding='5'><tr><th>Course</th><th>Students</th></tr>";
$needsFix = false;
$wrongCourseCount = 0;
while ($d = $distRes->fetch_assoc()) {
    echo "<tr><td>{$d['course_code']}</td><td>{$d['count']}</td></tr>";
    if ($d['course_code'] != 'BSIS' && $d['count'] > 0) {
        $needsFix = true;
        $wrongCourseCount += $d['count'];
    }
}
echo "</table>";

// Step 6: Auto-fix if needed
if ($current == 0 && $wrongCourseCount > 0) {
    echo "<h2>Step 6: Auto-Fix Required</h2>";
    echo "<p class='info'>Found $wrongCourseCount students in wrong courses. Attempting to fix...</p>";
    
    $fixSql = "UPDATE admissions 
               SET course_id = $bsis_id 
               WHERE academic_year_id = $ay_id 
               AND semester_id = $sem_id 
               AND course_id != $bsis_id";
    
    if ($conn->query($fixSql)) {
        $fixed = $conn->affected_rows;
        echo "<p class='success'>✅ SUCCESS! Fixed $fixed student records to BSIS!</p>";
    } else {
        echo "<p class='error'>❌ ERROR: " . $conn->error . "</p>";
    }
} else if ($current > 0) {
    echo "<h2>Step 6: Status</h2>";
    echo "<p class='success'>✅ Everything looks good! $current BSIS students found.</p>";
} else {
    echo "<h2>Step 6: Status</h2>";
    echo "<p class='error'>⚠️ No students found at all. You may need to enroll students first via the Students page.</p>";
}

// Step 7: Final verification
echo "<h2>Step 7: Final Verification</h2>";
$finalCheck = $conn->query($checkSql)->fetch_assoc()['count'];
echo "<p class='info'>BSIS students after fix: <strong>$finalCheck</strong></p>";

if ($finalCheck > 0) {
    echo "<p class='success'>🎉 <strong>SUCCESS!</strong> Your BSISstudents.php page should now work!</p>";
    echo "<p><a href='admin/BSISstudents.php' style='background:#0066cc;color:white;padding:10px 20px;text-decoration:none;border-radius:5px;'>Go to BSIS Students Page</a></p>";
} else {
    echo "<p class='error'>⚠️ Still no BSIS students. Please check if you have enrolled students in the system.</p>";
}

echo "</body></html>";
?>
