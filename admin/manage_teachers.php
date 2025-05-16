<?php
session_start();
require_once("../config/db.php");

// Add teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_teacher'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $max_load = (int)$_POST['max_load'];

    if (!empty($name) && !empty($email)) {
        $stmt = $conn->prepare("INSERT INTO teachers (name, email, max_load) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $email, $max_load);
        $stmt->execute();
        $stmt->close();
    }
}

// Edit teacher
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_teacher'])) {
    $id = (int)$_POST['teacher_id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $max_load = (int)$_POST['max_load'];

    $stmt = $conn->prepare("UPDATE teachers SET name = ?, email = ?, max_load = ? WHERE id = ?");
    $stmt->bind_param("ssii", $name, $email, $max_load, $id);
    $stmt->execute();
    $stmt->close();
}

// Delete teacher
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM teachers WHERE id = $delete_id");
}
?>

<link rel="stylesheet" href="..\admin\css\manage_teachers.css">

<div class="container">
    <h2>👨‍🏫 Manage Teachers</h2>

    <!-- Add Teacher -->
    <div class="table-container">
        <form method="POST" class="form-row mb-3">
            <input type="text" name="name" placeholder="Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="number" name="max_load" placeholder="Max Load" min="1" required>
            <button type="submit" name="add_teacher" class="btn btn-success">➕ Add</button>
        </form>
    </div>

    <!-- Teacher List -->
    <div class="table-container">
        <table class="table text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Max Load</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $teachers = $conn->query("SELECT * FROM teachers ORDER BY id DESC");
                if ($teachers && $teachers->num_rows > 0):
                    while ($teacher = $teachers->fetch_assoc()):
                ?>
                <tr>
                    <form method="POST">
                        <td><?= $teacher['id']; ?></td>
                        <td><input type="text" name="name" value="<?= htmlspecialchars($teacher['name']); ?>"></td>
                        <td><input type="email" name="email" value="<?= htmlspecialchars($teacher['email']); ?>"></td>
                        <td><input type="number" name="max_load" value="<?= (int)$teacher['max_load']; ?>"></td>
                        <td>
                            <input type="hidden" name="teacher_id" value="<?= $teacher['id']; ?>">
                            <button type="submit" name="edit_teacher" class="btn btn-primary btn-sm">💾 Save</button>
                            <a href="?delete=<?= $teacher['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this teacher?');">🗑️ Delete</a>
                        </td>
                    </form>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5">No teachers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

