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

// Update teacher
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            padding: 30px;
        }

        .table-container {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
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
    <h2>👨‍🏫 Manage Teachers</h2>

    <!-- Add Teacher -->
    <div class="table-container mb-4">
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Teacher Name" required>
            </div>
            <div class="col-md-4">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="max_load" class="form-control" placeholder="Max Load" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="add_teacher" class="btn btn-success w-100">➕ Add</button>
            </div>
        </form>
    </div>

    <!-- Search Bar -->
    <div class="table-container">
        <div class="d-flex justify-content-end mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="🔍 Search by name or email..." onkeyup="filterTeachers()">
        </div>

        <!-- Teacher List -->
        <table class="table table-bordered text-center align-middle">
            <thead>
                <tr class="table-dark">
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Max Load</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM teachers ORDER BY id DESC");
                if ($result && $result->num_rows > 0):
                    while ($teacher = $result->fetch_assoc()):
                ?>
                <tr>
                    <form method="POST">
                        <td><?= $teacher['id']; ?></td>
                        <td><input type="text" name="name" value="<?= htmlspecialchars($teacher['name']); ?>"></td>
                        <td><input type="email" name="email" value="<?= htmlspecialchars($teacher['email']); ?>"></td>
                        <td><input type="number" name="max_load" value="<?= $teacher['max_load']; ?>"></td>
                        <td>
                            <input type="hidden" name="teacher_id" value="<?= $teacher['id']; ?>">
                            <button type="submit" name="edit_teacher" class="btn btn-sm btn-primary">💾 Save</button>
                            <a href="?delete=<?= $teacher['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this teacher?');">🗑️ Delete</a>
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

<script>
function filterTeachers() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach(row => {
        const name = row.querySelector("input[name='name']").value.toLowerCase();
        const email = row.querySelector("input[name='email']").value.toLowerCase();

        if (name.includes(input) || email.includes(input)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>

</body>
</html>
