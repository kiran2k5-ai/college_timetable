<?php
require 'config/db.php'; // contains mysqli connection

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
$years = [1, 2, 3, 4];
$sections = ['A', 'B', 'C'];

$response = [];

// Load all teachers and their loads
$teachers = [];
$teacher_load = [];
$res = $conn->query("SELECT * FROM teachers");
while ($row = $res->fetch_assoc()) {
    $teachers[$row['id']] = $row;
    $teacher_load[$row['id']] = 0;
}

// Load teacher-subject assignments
$teacher_subject_map = [];
$res = $conn->query("SELECT teacher_id, subject_id FROM teacher_subjects");
while ($row = $res->fetch_assoc()) {
    $teacher_subject_map[$row['subject_id']] = $row['teacher_id'];
}

// Load all subjects
$subjects_by_year = [];
$res = $conn->query("SELECT * FROM subjects");
while ($row = $res->fetch_assoc()) {
    $subjects_by_year[$row['year']][] = $row;
}

// Function to get a subject not overloaded and fits criteria
function getAssignableSubject($year, $assigned, $type = 'any') {
    global $subjects_by_year, $teacher_subject_map, $teachers, $teacher_load;

    foreach ($subjects_by_year[$year] as $subject) {
        if (in_array($subject['id'], $assigned)) continue;
        if ($type === 'lab' && !$subject['is_lab']) continue;
        if ($type === 'theory' && $subject['is_lab']) continue;

        $teacher_id = $teacher_subject_map[$subject['id']] ?? null;
        if (!$teacher_id) continue;
        if ($teacher_load[$teacher_id] >= $teachers[$teacher_id]['max_load']) continue;

        return $subject;
    }
    return null;
}

// Generate timetable
foreach ($years as $year) {
    $max_periods = ($year == 1 || $year == 3) ? 8 : 7;

    foreach ($sections as $section) {
        $assigned_subjects = [];
        $timetable = [];

        foreach ($days as $day) {
            $timetable[$day] = [];

            $p = 1;
            while ($p <= $max_periods) {
                // Randomly assign lab (2 or 3 periods)
                if (rand(0, 6) < 2 && $p <= $max_periods - 1) {
                    $lab = getAssignableSubject($year, $assigned_subjects, 'lab');
                    if ($lab) {
                        $teacher_id = $teacher_subject_map[$lab['id']];
                        $period_span = rand(2, min(3, $max_periods - $p + 1));

                        for ($i = 0; $i < $period_span; $i++) {
                            $timetable[$day][$p] = [
                                'subject' => $lab['name'],
                                'teacher' => $teachers[$teacher_id]['name'],
                                'type' => 'lab'
                            ];
                            $p++;
                        }
                        $assigned_subjects[] = $lab['id'];
                        $teacher_load[$teacher_id] += $period_span;
                        continue;
                    }
                }

                // Assign theory
                $subject = getAssignableSubject($year, $assigned_subjects, 'theory');
                if ($subject) {
                    $teacher_id = $teacher_subject_map[$subject['id']];
                    $timetable[$day][$p] = [
                        'subject' => $subject['name'],
                        'teacher' => $teachers[$teacher_id]['name'],
                        'type' => 'theory'
                    ];
                    $assigned_subjects[] = $subject['id'];
                    $teacher_load[$teacher_id]++;
                } else {
                    // Free period or unable to assign
                    $timetable[$day][$p] = ['subject' => 'Free', 'teacher' => '-', 'type' => 'free'];
                }
                $p++;
            }
        }

        $response["Year {$year}"]["Section {$section}"] = $timetable;
    }
}

header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
