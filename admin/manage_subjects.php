<?php
session_start();
require_once("../config/db.php");

// Add subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subject'])) {
    $name = trim($_POST['name']);
    $year = trim($_POST['year']);
    $is_lab = isset($_POST['is_lab']) ? 1 : 0;

    if (!empty($name) && !empty($year)) {
        $stmt = $conn->prepare("INSERT INTO subjects (name, year, is_lab) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $year, $is_lab);
        $stmt->execute();
        $stmt->close();
    }
}

// Update subject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_subject'])) {
    $id = (int)$_POST['subject_id'];
    $name = trim($_POST['name']);
    $year = trim($_POST['year']);
    $is_lab = isset($_POST['is_lab']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE subjects SET name = ?, year = ?, is_lab = ? WHERE id = ?");
    $stmt->bind_param("ssii", $name, $year, $is_lab, $id);
    $stmt->execute();
    $stmt->close();
}

// Delete subject
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM subjects WHERE id = $delete_id");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #eef1f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 30px;
        }

        .table-container {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 12px rgba(0,0,0,0.05);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 6px 10px;
        }

        .btn-sm {
            font-size: 0.8rem;
            padding: 5px 10px;
        }

        .search-bar input {
            width: 300px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📘 Manage Subjects</h2>

    <!-- Add Subject -->
    <div class="table-container mb-4">
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Subject Name" required>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select" required>
                    <option value="">Select Year</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-center">
                <input type="checkbox" name="is_lab" id="is_lab" class="form-check-input me-2">
                <label for="is_lab" class="form-check-label">Lab Subject</label>
            </div>
            <div class="col-md-2">
                <button type="submit" name="add_subject" class="btn btn-success w-100">➕ Add</button>
            </div>
        </form>
    </div>

    <!-- Search Bar -->
    <div class="table-container">
        <div class="d-flex justify-content-end mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search by name or year..." onkeyup="filterSubjects()">
        </div>

        <!-- Subject List -->
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr class="table-dark">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Year</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM subjects ORDER BY id DESC");
                if ($result && $result->num_rows > 0):
                    while ($subject = $result->fetch_assoc()):
                ?>
                <tr>
                    <form method="POST">
                        <td><?= $subject['id']; ?></td>
                        <td><input type="text" name="name" value="<?= htmlspecialchars($subject['name']); ?>"></td>
                        <td>
                            <select name="year" class="form-select">
                                <option value="1st Year" <?= $subject['year'] === '1st Year' ? 'selected' : '' ?>>1st Year</option>
                                <option value="2nd Year" <?= $subject['year'] === '2nd Year' ? 'selected' : '' ?>>2nd Year</option>
                                <option value="3rd Year" <?= $subject['year'] === '3rd Year' ? 'selected' : '' ?>>3rd Year</option>
                                <option value="4th Year" <?= $subject['year'] === '4th Year' ? 'selected' : '' ?>>4th Year</option>
                            </select>
                        </td>
                        <td>
                            <input type="checkbox" name="is_lab" <?= $subject['is_lab'] ? 'checked' : '' ?>> <?= $subject['is_lab'] ? 'Lab' : 'Theory' ?>
                        </td>
                        <td>
                            <input type="hidden" name="subject_id" value="<?= $subject['id']; ?>">
                            <button type="submit" name="edit_subject" class="btn btn-sm btn-primary">💾 Save</button>
                            <a href="?delete=<?= $subject['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this subject?');">🗑️ Delete</a>
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

<script>
function filterSubjects() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        const name = row.querySelector("input[name='name']").value.toLowerCase();
        const year = row.querySelector("select[name='year']").value.toLowerCase();

        if (name.includes(input) || year.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

</body>
</html>
