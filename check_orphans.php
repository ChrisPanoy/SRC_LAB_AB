<?php
include 'includes/db.php';

$ay_id = (int)($_SESSION['active_ay_id'] ?? 0);
$sem_id = (int)($_SESSION['active_sem_id'] ?? 0);

echo "Checking BSIS (Course ID 2) for term AY $ay_id, Sem $sem_id\n";

$res = $conn->query("SELECT a.admission_id, a.student_id, a.section_id, a.year_level_id 
                     FROM admissions a 
                     WHERE a.course_id = 2 AND a.academic_year_id = $ay_id AND a.semester_id = $sem_id");
$count = 0;
while($r = $res->fetch_assoc()) {
    $count++;
    $sid = $r['student_id'];
    $sec = $r['section_id'];
    $yl = $r['year_level_id'];
    
    // Check if student exists
    $sCheck = $conn->query("SELECT 1 FROM students WHERE student_id = '$sid'")->num_rows;
    if($sCheck === 0) echo "Admission {$r['admission_id']}: Student $sid MISSING\n";
    
    // Check if section exists
    if($sec) {
        $secCheck = $conn->query("SELECT 1 FROM sections WHERE section_id = $sec")->num_rows;
        if($secCheck === 0) echo "Admission {$r['admission_id']}: Section $sec MISSING\n";
    }
    
    // Check if year level exists
    if($yl) {
        $ylCheck = $conn->query("SELECT 1 FROM year_levels WHERE year_id = $yl")->num_rows;
        if($ylCheck === 0) echo "Admission {$r['admission_id']}: Year Level $yl MISSING\n";
    }
}
echo "Checked $count admissions.\n";
