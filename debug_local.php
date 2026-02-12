<?php
include 'includes/db.php';

echo "DEBUG INFO\n";
echo "Active AY: " . ($_SESSION['active_ay_name'] ?? 'None') . " (ID: " . ($_SESSION['active_ay_id'] ?? 'None') . ")\n";
echo "Active Sem: " . ($_SESSION['active_sem_now'] ?? 'None') . " (ID: " . ($_SESSION['active_sem_id'] ?? 'None') . ")\n";

$courseRes = $conn->query("SELECT course_id, course_code FROM courses WHERE course_code = 'BSIS'");
$bsis = $courseRes->fetch_assoc();
$bsis_id = $bsis['course_id'] ?? 0;
echo "BSIS Course ID: $bsis_id\n";

$res = $conn->query("SELECT COUNT(*) as count FROM admissions WHERE course_id = $bsis_id AND academic_year_id = " . ($_SESSION['active_ay_id'] ?? 0) . " AND semester_id = " . ($_SESSION['active_sem_id'] ?? 0));
$row = $res->fetch_assoc();
echo "Admissions for BSIS in active term: " . $row['count'] . "\n";

$resAll = $conn->query("SELECT c.course_code, COUNT(*) as count FROM admissions a JOIN courses c ON a.course_id = c.course_id GROUP BY a.course_id");
echo "\nGeneral Admission Distribution by Course:\n";
while($r = $resAll->fetch_assoc()) {
    echo $r['course_code'] . ": " . $r['count'] . "\n";
}
