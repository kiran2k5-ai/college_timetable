<?php
require '../config/db.php';

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$years = [1, 2, 3, 4];
$sections = ['A', 'B', 'C'];

$response = [];

// Load teachers and their load
$teachers = [];
$teacher_load = []; // teacher_id => [year => count]
$res = $conn->query("SELECT * FROM teachers");
while ($row = $res->fetch_assoc()) {
    $teachers[$row['id']] = $row;
    $teacher_load[$row['id']] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
}

// Map teacher to subject
$teacher_subject_map = [];
$res = $conn->query("SELECT teacher_id, subject_id FROM teacher_subjects");
while ($row = $res->fetch_assoc()) {
    $teacher_subject_map[$row['subject_id']] = $row['teacher_id'];
}

// Subjects grouped by year
$subjects_by_year = [];
$subject_weekly_count = []; // subject_id => count

$res = $conn->query("SELECT * FROM subjects");
while ($row = $res->fetch_assoc()) {
    $subjects_by_year[$row['year']][] = $row;
    $subject_weekly_count[$row['id']] = 0;
}

// Helper: Get assignable subject
function getAssignableSubject($year, $assigned_ids, $type = 'any') {
    global $subjects_by_year, $teacher_subject_map, $teachers, $teacher_load, $subject_weekly_count;

    $subjects = $subjects_by_year[$year];
    shuffle($subjects); // random order

    foreach ($subjects as $subject) {
        if ($subject_weekly_count[$subject['id']] >= 4) continue;
        if ($type === 'lab' && !$subject['is_lab']) continue;
        if ($type === 'theory' && $subject['is_lab']) continue;

        $teacher_id = $teacher_subject_map[$subject['id']] ?? null;
        if (!$teacher_id || $teacher_load[$teacher_id][$year] >= $teachers[$teacher_id]['max_load']) continue;

        // Passed all checks
        $subject_weekly_count[$subject['id']]++;
        $teacher_load[$teacher_id][$year]++;
        return [$subject, $teacher_id];
    }

    return [null, null];
}

// Build timetable
foreach ($years as $year) {
    $max_periods = ($year == 1 || $year == 3) ? 8 : 7;

    foreach ($sections as $section) {
        $timetable = [];
        $assigned_subjects = [];

        foreach ($days as $day) {
            $p = 1;
            $timetable[$day] = [];

            while ($p <= $max_periods) {
                $slot_filled = false;

                // Determine required lab span (3 for 1st/3rd, 2 for 2nd/4th)
                $lab_span = in_array($year, [1, 3]) ? 3 : 2;

                // Try to assign lab
                if (rand(0, 10) < 3 && $p <= $max_periods - $lab_span + 1) {
                    list($lab, $teacher_id) = getAssignableSubject($year, $assigned_subjects, 'lab');
                    if ($lab) {
                        $can_assign = true;
                        for ($i = 0; $i < $lab_span; $i++) {
                            if (isset($timetable[$day][$p + $i])) {
                                $can_assign = false;
                                break;
                            }
                        }
                        if ($can_assign) {
                            for ($i = 0; $i < $lab_span; $i++) {
                                $timetable[$day][$p + $i] = [
                                    'subject' => $lab['name'],
                                    'teacher' => $teachers[$teacher_id]['name'],
                                    'type' => 'lab'
                                ];
                            }
                            $assigned_subjects[] = $lab['id'];
                            $p += $lab_span;
                            $slot_filled = true;
                            continue;
                        }
                    }
                }

                // Try to assign theory
                list($theory, $teacher_id) = getAssignableSubject($year, $assigned_subjects, 'theory');
                if ($theory) {
                    $timetable[$day][$p] = [
                        'subject' => $theory['name'],
                        'teacher' => $teachers[$teacher_id]['name'],
                        'type' => 'theory'
                    ];
                    $assigned_subjects[] = $theory['id'];
                    $p++;
                    $slot_filled = true;
                }

                // If nothing assigned
                if (!$slot_filled) {
                    $timetable[$day][$p] = [
                        'subject' => 'Free',
                        'teacher' => '-',
                        'type' => 'free'
                    ];
                    $p++;
                }
            }
        }

        $response["Year $year"]["Section $section"] = $timetable;
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
file_put_contents(__DIR__ . '/../data/generate_timetable.json', json_encode($response, JSON_PRETTY_PRINT));

