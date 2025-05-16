<?php
$timetable = json_decode(file_get_contents('../data/generate_timetable.json'), true);

// Build teacher-wise timetable
$teacher_timetable = [];

foreach ($timetable as $year => $sections) {
    foreach ($sections as $section => $days) {
        foreach ($days as $day => $periods) {
            foreach ($periods as $period => $data) {
                if ($data['type'] !== 'free') {
                    $teacher = $data['teacher'];
                    $teacher_timetable[$teacher][$day][$period] = [
                        'year' => $year,
                        'section' => $section,
                        'subject' => $data['subject'],
                        'type' => $data['type']
                    ];
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>College Timetable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-4 text-center">📅 College Timetable</h1>

    <!-- Year Tabs -->
    <ul class="nav nav-tabs" id="yearTabs" role="tablist">
        <?php $i = 0; foreach ($timetable as $year => $sections): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $i === 0 ? 'active' : '' ?>" id="tab-<?= $i ?>" data-bs-toggle="tab" data-bs-target="#year<?= $i ?>" type="button" role="tab">
                    <?= $year ?>
                </button>
            </li>
        <?php $i++; endforeach; ?>
    </ul>

    <!-- Year Content -->
    <div class="tab-content mt-3">
        <?php $i = 0; foreach ($timetable as $year => $sections): ?>
            <div class="tab-pane fade <?= $i === 0 ? 'show active' : '' ?>" id="year<?= $i ?>" role="tabpanel">
                <?php foreach ($sections as $section => $days): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <strong><?= $section ?></strong>
                        </div>
                        <div class="card-body p-0 table-responsive">
                            <table class="table table-bordered table-striped mb-0">
                                <thead class="table-secondary">
                                <tr>
                                    <th>Day / Period</th>
                                    <?php for ($j = 1; $j <= (in_array(substr($year, -1), ['1', '3']) ? 8 : 7); $j++): ?>
                                        <th><?= $j ?></th>
                                    <?php endfor; ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($days as $day => $periods): ?>
                                    <tr>
                                        <td><strong><?= $day ?></strong></td>
                                        <?php for ($j = 1; $j <= (in_array(substr($year, -1), ['1', '3']) ? 8 : 7); $j++): ?>
                                            <td class="<?= $periods[$j]['type'] ?? '' ?>">
                                                <?php if (isset($periods[$j])): ?>
                                                    <?= $periods[$j]['subject'] ?><br>
                                                    <small class="text-muted"><?= $periods[$j]['teacher'] ?></small>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php $i++; endforeach; ?>
    </div>

    <!-- Individual Teacher View -->
    <div class="card mt-5 shadow-sm">
        <div class="card-header bg-success text-white">
            <strong>👨‍🏫 View Individual Teacher Timetable</strong>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <select id="teacherSelect" class="form-select" onchange="showTeacherTimetable()">
                    <option value="">-- Select a Teacher --</option>
                    <?php foreach (array_keys($teacher_timetable) as $teacherName): ?>
                        <option value="<?= htmlspecialchars($teacherName) ?>"><?= htmlspecialchars($teacherName) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="teacherTimetableContainer"></div>
            <button onclick="downloadTeacherPDF()" class="btn btn-danger mt-3" style="display: none;" id="pdfBtn">⬇ Download PDF</button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const teacherData = <?= json_encode($teacher_timetable) ?>;

    function showTeacherTimetable() {
        const teacher = document.getElementById('teacherSelect').value;
        const container = document.getElementById('teacherTimetableContainer');
        const pdfBtn = document.getElementById('pdfBtn');
        container.innerHTML = '';
        pdfBtn.style.display = 'none';

        if (!teacher || !teacherData[teacher]) {
            container.innerHTML = '<p class="text-muted">Select a teacher to view their timetable.</p>';
            return;
        }

        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        let html = `<div id="teacherTable"><h5 class="mb-3">🧑‍🏫 Timetable for <b>${teacher}</b></h5>`;
        html += `<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Day</th>`;

        for (let i = 1; i <= 8; i++) {
            html += `<th>${i}</th>`;
        }

        html += '</tr></thead><tbody>';

        days.forEach(day => {
            html += `<tr><td><strong>${day}</strong></td>`;
            for (let i = 1; i <= 8; i++) {
                const entry = teacherData[teacher][day]?.[i];
                if (entry) {
                    html += `<td><b>${entry.subject}</b><br><small class="text-muted">${entry.year} - ${entry.section} (${entry.type})</small></td>`;
                } else {
                    html += `<td>-</td>`;
                }
            }
            html += '</tr>';
        });

        html += '</tbody></table></div></div>';
        container.innerHTML = html;
        pdfBtn.style.display = 'inline-block';
    }

    function downloadTeacherPDF() {
        const element = document.getElementById('teacherTable');
        const teacher = document.getElementById('teacherSelect').value;
        if (!teacher) return alert("Select a teacher first.");

        const opt = {
            margin: 0.5,
            filename: `Timetable_${teacher.replace(/\s+/g, '_')}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
</body>
</html>
