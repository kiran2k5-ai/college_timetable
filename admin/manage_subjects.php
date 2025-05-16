<?php
session_start();
require_once("../config/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $name = trim($_POST['name']);
    $year = (int)$_POST['year'];
    $is_lab = isset($_POST['is_lab']) ? 1 : 0;

    if (!empty($name) && $year > 0) {
        $stmt = $conn->prepare("INSERT INTO subjects (name, year, is_lab) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $name, $year, $is_lab);
        $stmt->execute();
        $stmt->close();
    }
}

// Edit Subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_subject'])) {
    $id = (int)$_POST['subject_id'];
    $name = trim($_POST['name']);
    $year = (int)$_POST['year'];
    $is_lab = isset($_POST['is_lab']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE subjects SET name = ?, year = ?, is_lab = ? WHERE id = ?");
    $stmt->bind_param("siii", $name, $year, $is_lab, $id);
    $stmt->execute();
    $stmt->close();
}

// Delete Subject
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE id = $delete_id");
}
?>
<link rel="stylesheet" href="..\admin\css\manage_subjects.css">

<style>
    
</style>

<div class="container">
    <h2>📘 Manage Subjects</h2>

    <!-- Add Subject -->
    <div class="table-container">
        <form method="POST" class="form-row mb-3">
            <input type="text" name="name" class="form-control" placeholder="Subject Name" required>
            <select name="year" class="form-select" required>
                <option value="">Select Year</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
            </select>
            <div class="form-check">
                <input type="checkbox" name="is_lab" class="form-check-input" id="labCheck">
                <label class="form-check-label" for="labCheck">Is Lab?</label>
            </div>
            <button type="submit" name="add_subject" class="btn btn-success">➕ Add</button>
        </form>
    </div>

    <!-- Subject List -->
    <div class="table-container">
        <table class="table align-middle text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Year</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $subjects = $conn->query("SELECT * FROM subjects ORDER BY id DESC");
                if ($subjects && $subjects->num_rows > 0):
                    while ($subject = $subjects->fetch_assoc()):
                ?>
                <tr>
                    <form method="POST">
                        <td><?= $subject['id']; ?></td>
                        <td><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($subject['name']); ?>"></td>
                        <td>
                            <select name="year" class="form-select">
                                <?php for ($y = 1; $y <= 4; $y++): ?>
                                    <option value="<?= $y ?>" <?= $subject['year'] == $y ? 'selected' : '' ?>><?= $y ?> Year</option>
                                <?php endfor; ?>
                            </select>
                        </td>
                        <td>
                            <div class="form-check d-flex justify-content-center">
                                <input type="checkbox" name="is_lab" class="form-check-input" <?= $subject['is_lab'] ? 'checked' : '' ?>>
                            </div>
                        </td>
                        <td>
                            <input type="hidden" name="subject_id" value="<?= $subject['id']; ?>">
                            <button type="submit" name="edit_subject" class="btn btn-primary btn-sm">💾 Save</button>
                            <a href="?delete=<?= $subject['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this subject?');">🗑️ Delete</a>
                        </td>
                    </form>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5">No subjects found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

