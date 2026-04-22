<?php
require_once __DIR__ . '/src/TaskManager.php';

$manager = new TaskManager();
$message = '';
$error   = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create':
            $title = trim($_POST['title'] ?? '');
            $desc  = trim($_POST['description'] ?? '');
            if ($title === '') {
                $error = 'A cím nem lehet üres!';
            } else {
                $manager->create($title, $desc);
                $message = 'Feladat sikeresen hozzáadva!';
            }
            break;

        case 'toggle':
            $manager->toggleComplete((int) $_POST['id']);
            break;

        case 'delete':
            $manager->delete((int) $_POST['id']);
            $message = 'Feladat törölve.';
            break;

        case 'delete_completed':
            $count = $manager->deleteCompleted();
            $message = "$count befejezett feladat törölve.";
            break;

        case 'update':
            $id    = (int) $_POST['id'];
            $title = trim($_POST['title'] ?? '');
            $desc  = trim($_POST['description'] ?? '');
            if ($title === '') {
                $error = 'A cím nem lehet üres!';
            } else {
                $manager->update($id, $title, $desc);
                $message = 'Feladat frissítve!';
            }
            break;
    }
}


$filter = $_GET['filter'] ?? 'all';
$tasks  = $manager->getAll($filter === 'pending');
$stats  = $manager->getStats();

?>
<!DOCTYPE html>
<html lang="hu">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Todo App</title>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg: #f5f5f5;
    --card: #ffffff;
    --accent: #3b82f6;
    --accent-dark: #2563eb;
    --danger: #ef4444;
    --success: #22c55e;
    --text: #1f2937;
    --muted: #6b7280;
    --border: #e5e7eb;
  }

  body { font-family: 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

  header {
    background: var(--accent);
    color: white;
    padding: 20px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  header h1 { font-size: 24px; font-weight: 700; }
  .stats { display: flex; gap: 20px; font-size: 14px; opacity: 0.9; }
  .stat { text-align: center; }
  .stat strong { display: block; font-size: 22px; font-weight: 700; }

  .container { max-width: 760px; margin: 30px auto; padding: 0 20px; }

  .alert {
    padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;
  }
  .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
  .alert-error   { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

  .card { background: var(--card); border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 24px; margin-bottom: 24px; }
  .card h2 { font-size: 16px; font-weight: 600; margin-bottom: 16px; color: var(--text); }

  input[type=text], textarea {
    width: 100%; padding: 10px 14px; border: 1px solid var(--border);
    border-radius: 8px; font-size: 14px; font-family: inherit;
    transition: border-color 0.2s;
  }
  input[type=text]:focus, textarea:focus { outline: none; border-color: var(--accent); }
  textarea { resize: vertical; min-height: 70px; }

  .form-group { margin-bottom: 12px; }
  label { display: block; font-size: 13px; font-weight: 500; margin-bottom: 5px; color: var(--muted); }

  .btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; border-radius: 8px; border: none; cursor: pointer;
    font-size: 14px; font-weight: 500; font-family: inherit; transition: all 0.15s;
    text-decoration: none;
  }
  .btn-primary  { background: var(--accent); color: white; }
  .btn-primary:hover  { background: var(--accent-dark); }
  .btn-danger   { background: var(--danger); color: white; }
  .btn-danger:hover   { background: #dc2626; }
  .btn-ghost    { background: transparent; color: var(--muted); border: 1px solid var(--border); }
  .btn-ghost:hover    { background: var(--bg); }
  .btn-sm { padding: 5px 12px; font-size: 12px; }

  .filters { display: flex; gap: 8px; margin-bottom: 16px; }
  .filter-btn {
    padding: 6px 14px; border-radius: 20px; border: 1px solid var(--border);
    background: white; color: var(--muted); font-size: 13px; cursor: pointer;
    text-decoration: none; transition: all 0.15s;
  }
  .filter-btn.active { background: var(--accent); color: white; border-color: var(--accent); }

  .task-list { display: flex; flex-direction: column; gap: 10px; }

  .task-item {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 14px 16px; border: 1px solid var(--border);
    border-radius: 10px; background: var(--card); transition: box-shadow 0.15s;
  }
  .task-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
  .task-item.done { opacity: 0.6; }
  .task-item.done .task-title { text-decoration: line-through; color: var(--muted); }

  .task-check { margin-top: 2px; width: 18px; height: 18px; cursor: pointer; accent-color: var(--accent); flex-shrink: 0; }

  .task-body { flex: 1; }
  .task-title { font-size: 15px; font-weight: 500; }
  .task-desc  { font-size: 13px; color: var(--muted); margin-top: 3px; }
  .task-date  { font-size: 11px; color: var(--muted); margin-top: 4px; }

  .task-actions { display: flex; gap: 6px; flex-shrink: 0; }

  .empty { text-align: center; padding: 40px; color: var(--muted); font-size: 15px; }

  .modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.4); z-index: 100;
    align-items: center; justify-content: center;
  }
  .modal-overlay.open { display: flex; }
  .modal {
    background: white; border-radius: 12px; padding: 28px;
    width: 100%; max-width: 480px; box-shadow: 0 20px 60px rgba(0,0,0,0.2);
  }
  .modal h3 { margin-bottom: 16px; font-size: 17px; }
  .modal-footer { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }
</style>
</head>
<body>

<header>
  <h1>📝 Todo App</h1>
  <div class="stats">
    <div class="stat"><strong><?= $stats['total'] ?></strong>összes</div>
    <div class="stat"><strong><?= $stats['pending'] ?></strong>folyamatban</div>
    <div class="stat"><strong><?= $stats['done'] ?></strong>kész</div>
  </div>
</header>

<div class="container">

  <?php if ($message): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="card">
    <h2>Új feladat hozzáadása</h2>
    <form method="POST">
      <input type="hidden" name="action" value="create">
      <div class="form-group">
        <label>Cím *</label>
        <input type="text" name="title" placeholder="Feladat neve..." required>
      </div>
      <div class="form-group">
        <label>Leírás</label>
        <textarea name="description" placeholder="Opcionális leírás..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary">+ Hozzáadás</button>
    </form>
  </div>

  <div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
      <h2 style="margin:0;">Feladatok</h2>
      <?php if ($stats['done'] > 0): ?>
      <form method="POST" style="margin:0;">
        <input type="hidden" name="action" value="delete_completed">
        <button type="submit" class="btn btn-ghost btn-sm">🗑 Befejezettek törlése</button>
      </form>
      <?php endif; ?>
    </div>

    <div class="filters">
      <a href="?filter=all"     class="filter-btn <?= $filter === 'all'     ? 'active' : '' ?>">Összes (<?= $stats['total'] ?>)</a>
      <a href="?filter=pending" class="filter-btn <?= $filter === 'pending' ? 'active' : '' ?>">Folyamatban (<?= $stats['pending'] ?>)</a>
    </div>

    <div class="task-list">
      <?php if (empty($tasks)): ?>
        <div class="empty">Nincsenek feladatok 🎉</div>
      <?php else: ?>
        <?php foreach ($tasks as $task): ?>
        <div class="task-item <?= $task->isCompleted() ? 'done' : '' ?>">

          <form method="POST" style="margin:0;">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= $task->getId() ?>">
            <input type="checkbox" class="task-check" onchange="this.form.submit()"
                   <?= $task->isCompleted() ? 'checked' : '' ?>>
          </form>

          <div class="task-body">
            <div class="task-title"><?= htmlspecialchars($task->getTitle()) ?></div>
            <?php if ($task->getDescription()): ?>
              <div class="task-desc"><?= htmlspecialchars($task->getDescription()) ?></div>
            <?php endif; ?>
            <div class="task-date"><?= $task->getCreatedAt() ?></div>
          </div>

          <div class="task-actions">
            <button class="btn btn-ghost btn-sm"
              onclick="openEdit(<?= $task->getId() ?>, '<?= addslashes(htmlspecialchars($task->getTitle())) ?>', '<?= addslashes(htmlspecialchars($task->getDescription())) ?>')">
              
            </button>
            <form method="POST" style="margin:0;" onsubmit="return confirm('Biztosan törlöd?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= $task->getId() ?>">
              <button type="submit" class="btn btn-danger btn-sm">🗑</button>
            </form>
          </div>

        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>

<div class="modal-overlay" id="editModal">
  <div class="modal">
    <h3>Feladat szerkesztése</h3>
    <form method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="editId">
      <div class="form-group">
        <label>Cím *</label>
        <input type="text" name="title" id="editTitle" required>
      </div>
      <div class="form-group">
        <label>Leírás</label>
        <textarea name="description" id="editDesc"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeEdit()">Mégse</button>
        <button type="submit" class="btn btn-primary">Mentés</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openEdit(id, title, desc) {
    document.getElementById('editId').value    = id;
    document.getElementById('editTitle').value = title;
    document.getElementById('editDesc').value  = desc;
    document.getElementById('editModal').classList.add('open');
  }
  function closeEdit() {
    document.getElementById('editModal').classList.remove('open');
  }
  document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
  });
</script>

</body>
</html>
