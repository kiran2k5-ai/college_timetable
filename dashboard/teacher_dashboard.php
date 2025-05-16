<!-- dashboard.php -->
<h2>College Timetable Management</h2>
<form method="post">
    <button name="generate">Generate Timetable</button>
</form>

<?php
if (isset($_POST['generate'])) {
    // Trigger timetable generation
    file_get_contents('../backend/generate_timetable.php');
    echo "<p>✅ Timetable generated.</p>";
}
?>

<a href="/college_timetable/backend/view_timetable.php">📅 View Timetable</a>

