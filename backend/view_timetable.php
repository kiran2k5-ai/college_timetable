<?php
// Load JSON (or fetch from DB if stored)
$timetable = json_decode(file_get_contents('../data/generate_timetable.json'), true);
?>

<h2>Timetable</h2>
<?php foreach ($timetable as $year => $sections): ?>
    <h3><?= $year ?></h3>
    <?php foreach ($sections as $section => $days): ?>
        <h4><?= $section ?></h4>
        <table border="1" cellpadding="6" cellspacing="0">
            <tr>
                <th>Day / Period</th>
                <?php for ($i = 1; $i <= (in_array(substr($year, -1), ['1','3']) ? 8 : 7); $i++): ?>
                    <th><?= $i ?></th>
                <?php endfor; ?>
            </tr>
            <?php foreach ($days as $day => $periods): ?>
                <tr>
                    <td><?= $day ?></td>
                    <?php for ($i = 1; $i <= (in_array(substr($year, -1), ['1','3']) ? 8 : 7); $i++): ?>
                        <td>
                            <?php if (isset($periods[$i])): ?>
                                <?= $periods[$i]['subject'] ?><br>
                                <small><?= $periods[$i]['teacher'] ?></small>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php endforeach; ?>
