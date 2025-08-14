<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user'];

// Tambah tugas
if (isset($_POST['add'])) {
    $title = htmlspecialchars($_POST['title']);
    $desc = htmlspecialchars($_POST['description']);
    $priority = $_POST['priority'];
    $due = $_POST['due_date'];

    $conn->query("INSERT INTO tasks (user_id, title, description, priority, due_date) 
                  VALUES ($user_id, '$title', '$desc', '$priority', '$due')");
    header("Location: index.php");
    exit;
}

// Update tugas
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = htmlspecialchars($_POST['title']);
    $desc = htmlspecialchars($_POST['description']);
    $priority = $_POST['priority'];
    $status = $_POST['status'];
    $due = $_POST['due_date'];

    $conn->query("UPDATE tasks SET title='$title', description='$desc', priority='$priority', 
                  status='$status', due_date='$due' WHERE id=$id AND user_id=$user_id");
    header("Location: index.php");
    exit;
}

// Hapus tugas
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM tasks WHERE id=$id AND user_id=$user_id");
    header("Location: index.php");
    exit;
}

// Ambil semua tugas
$tasks = $conn->query("SELECT * FROM tasks WHERE user_id=$user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>To-Do List</title>
<style>
    body {
        background: #f1f5f9;
        font-family: 'Segoe UI', sans-serif;
        margin: 0; padding: 0;
    }
    header {
        background: #2563eb;
        color: white;
        padding: 20px;
        text-align: center;
        position: relative;
    }
    .logout {
        position: absolute;
        right: 20px;
        top: 20px;
    }
    .logout a {
        color: white;
        background: #ef4444;
        padding: 6px 12px;
        border-radius: 6px;
        text-decoration: none;
    }
    .logout a:hover {
        background: #dc2626;
    }

    .container {
        max-width: 900px;
        background: white;
        margin: 30px auto;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 8px 16px rgba(0,0,0,0.1);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        font-size: 15px;
        color: #374151;
    }

    thead tr {
        background-color: #2563eb;
        color: white;
        font-weight: 700;
        font-size: 16px;
    }

    th, td {
        padding: 14px 16px;
        text-align: left;
        vertical-align: top;
    }

    tbody tr {
        border-bottom: 1px solid #e5e7eb;
        transition: background-color 0.3s ease;
    }

    tbody tr:hover {
        background-color: #e0e7ff;
        cursor: pointer;
    }

    .actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .actions button, .actions a {
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        color: white;
        font-size: 14px;
    }

    .actions button {
        background: #2563eb;
    }
    .actions button:hover {
        background: #1d4ed8;
    }
    .actions a.delete-btn {
        background: #ef4444;
    }
    .actions a.delete-btn:hover {
        background: #dc2626;
    }

    .btn-add {
        background: #2563eb;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        border: none;
        font-weight: bold;
        cursor: pointer;
        margin-bottom: 20px;
        font-size: 16px;
    }
    .btn-add:hover {
        background: #1d4ed8;
    }

    .modal {
        display: none;
        position: fixed; 
        z-index: 10; 
        left: 0;
        top: 0;
        width: 100%; 
        height: 100%; 
        overflow: auto;
        background-color: rgba(0,0,0,0.5);
    }

    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 30px;
        border-radius: 12px;
        max-width: 450px;
        position: relative;
        box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }

    .close-btn {
        position: absolute;
        top: 12px;
        right: 16px;
        font-size: 24px;
        font-weight: bold;
        color: #555;
        cursor: pointer;
    }
    .close-btn:hover {
        color: #000;
    }

    .modal-content input,
    .modal-content select,
    .modal-content textarea {
        width: 100%;
        padding: 10px;
        margin: 10px 0 20px 0;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
    }

    .modal-content button {
        width: 100%;
        background-color: #2563eb;
        color: white;
        border: none;
        padding: 12px;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
    }

    .modal-content button:hover {
        background-color: #1d4ed8;
    }
</style>
</head>
<body>

<header>
    <h2>Selamat Datang, <?= htmlspecialchars($username) ?>!</h2>
    <div class="logout"><a href="logout.php">Logout</a></div>
</header>

<div class="container">
    <button class="btn-add" id="openAddModalBtn">+ Tambah Tugas Baru</button>

    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Prioritas</th>
                <th>Jatuh Tempo</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($task = $tasks->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($task['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($task['description'])) ?></td>
                <td><?= ucfirst(str_replace('_', ' ', $task['status'])) ?></td>
                <td><?= ucfirst($task['priority']) ?></td>
                <td><?= $task['due_date'] ?></td>
                <td class="actions">
                    <button class="editBtn" 
                        data-id="<?= $task['id'] ?>"
                        data-title="<?= htmlspecialchars($task['title'], ENT_QUOTES) ?>"
                        data-description="<?= htmlspecialchars($task['description'], ENT_QUOTES) ?>"
                        data-status="<?= $task['status'] ?>"
                        data-priority="<?= $task['priority'] ?>"
                        data-duedate="<?= $task['due_date'] ?>"
                    >🖉 Edit</button>
                    <a class="delete-btn" href="?delete=<?= $task['id'] ?>" onclick="return confirm('Hapus tugas ini?')">🗑️ Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeAddModalBtn">&times;</span>
    <h2>Tambah Tugas Baru</h2>
    <form method="POST" id="addForm">
        <input type="text" name="title" placeholder="Judul tugas" required>
        <textarea name="description" placeholder="Deskripsi (opsional)"></textarea>
        <select name="priority" required>
            <option value="low">Rendah</option>
            <option value="medium" selected>Sedang</option>
            <option value="high">Tinggi</option>
        </select>
        <input type="date" name="due_date" required>
        <button type="submit" name="add">Tambah</button>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span class="close-btn" id="closeEditModalBtn">&times;</span>
    <h2>Edit Tugas</h2>
    <form method="POST" id="editForm">
        <input type="hidden" name="id" id="edit-id" />
        <input type="text" name="title" id="edit-title" placeholder="Judul tugas" required>
        <textarea name="description" id="edit-description" placeholder="Deskripsi (opsional)"></textarea>
        <select name="priority" id="edit-priority" required>
            <option value="low">Rendah</option>
            <option value="medium">Sedang</option>
            <option value="high">Tinggi</option>
        </select>
        <select name="status" id="edit-status" required>
            <option value="pending">Pending</option>
            <option value="in_progress">Proses</option>
            <option value="done">Selesai</option>
        </select>
        <input type="date" name="due_date" id="edit-duedate" required>
        <button type="submit" name="update">Simpan</button>
    </form>
  </div>
</div>

<script>
    const addModal = document.getElementById('addModal');
    const openAddBtn = document.getElementById('openAddModalBtn');
    const closeAddBtn = document.getElementById('closeAddModalBtn');

    openAddBtn.onclick = () => addModal.style.display = 'block';
    closeAddBtn.onclick = () => addModal.style.display = 'none';

    const editModal = document.getElementById('editModal');
    const closeEditBtn = document.getElementById('closeEditModalBtn');

    closeEditBtn.onclick = () => editModal.style.display = 'none';

    window.onclick = (e) => {
        if (e.target === addModal) addModal.style.display = 'none';
        if (e.target === editModal) editModal.style.display = 'none';
    }

    const editButtons = document.querySelectorAll('.editBtn');
    editButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('edit-id').value = btn.dataset.id;
            document.getElementById('edit-title').value = btn.dataset.title;
            document.getElementById('edit-description').value = btn.dataset.description;
            document.getElementById('edit-status').value = btn.dataset.status;
            document.getElementById('edit-priority').value = btn.dataset.priority;
            document.getElementById('edit-duedate').value = btn.dataset.duedate;
            editModal.style.display = 'block';
        });
    });
</script>

</body>
</html>
