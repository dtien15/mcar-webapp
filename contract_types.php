<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin', 'ketoan']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [trim($_POST['name']), trim($_POST['description']), (float)$_POST['revenue_share_percent']];
        if ($id > 0) {
            $pdo->prepare("UPDATE contract_types SET name=?, description=?, revenue_share_percent=? WHERE id=?")->execute([...$data, $id]);
            flash_set('Đã cập nhật loại kèo.');
        } else {
            $pdo->prepare("INSERT INTO contract_types (name, description, revenue_share_percent) VALUES (?,?,?)")->execute($data);
            flash_set('Đã thêm loại kèo.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM contract_types WHERE id=?")->execute([(int)$_POST['id']]);
        flash_set('Đã xóa.');
    }
    redirect('contract_types.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM contract_types WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query("SELECT * FROM contract_types ORDER BY id")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<h4 class="mb-3">Danh mục Loại kèo</h4>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><?= $edit ? 'Sửa' : 'Thêm mới' ?></div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
          <div class="mb-2"><label class="form-label">Tên loại kèo</label>
            <input class="form-control" name="name" required value="<?= e($edit['name'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Diễn giải</label>
            <input class="form-control" name="description" value="<?= e($edit['description'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">% ăn chia (nếu có)</label>
            <input type="number" step="0.01" class="form-control" name="revenue_share_percent" value="<?= e($edit['revenue_share_percent'] ?? 0) ?>"></div>
          <button class="btn btn-primary"><?= $edit ? 'Cập nhật' : 'Thêm mới' ?></button>
          <?php if ($edit): ?><a href="contract_types.php" class="btn btn-secondary">Hủy</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Tên</th><th>Diễn giải</th><th>% chia</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= e($r['name']) ?></td>
              <td><?= e($r['description']) ?></td>
              <td><?= e($r['revenue_share_percent']) ?>%</td>
              <td class="text-end">
                <a href="contract_types.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Xóa?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $r['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
