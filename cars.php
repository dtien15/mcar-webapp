<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin', 'ketoan']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            trim($_POST['name']),
            trim($_POST['plate_number']),
            trim($_POST['seats']),
            $_POST['start_date'] ?: null,
            trim($_POST['company']),
            $_POST['status'],
            trim($_POST['note']),
        ];
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE cars SET name=?, plate_number=?, seats=?, start_date=?, company=?, status=?, note=? WHERE id=?");
            $stmt->execute([...$data, $id]);
            flash_set('Đã cập nhật xe.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO cars (name, plate_number, seats, start_date, company, status, note) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute($data);
            flash_set('Đã thêm xe mới.');
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM cars WHERE id=?")->execute([$id]);
        flash_set('Đã xóa xe.');
    }
    redirect('cars.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$cars = $pdo->query("SELECT * FROM cars ORDER BY id")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<h4 class="mb-3">Danh mục Xe</h4>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><?= $edit ? 'Sửa xe' : 'Thêm xe mới' ?></div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
          <div class="mb-2"><label class="form-label">Dòng xe</label>
            <input class="form-control" name="name" required value="<?= e($edit['name'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Biển số</label>
            <input class="form-control" name="plate_number" value="<?= e($edit['plate_number'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Số chỗ</label>
            <select class="form-select" name="seats">
              <?php foreach (['4c','7c','16c'] as $s): ?>
                <option <?= ($edit['seats'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="mb-2"><label class="form-label">Ngày bắt đầu chạy</label>
            <input type="date" class="form-control" name="start_date" value="<?= e($edit['start_date'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Công ty quản lý</label>
            <input class="form-control" name="company" value="<?= e($edit['company'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Trạng thái</label>
            <select class="form-select" name="status">
              <?php foreach (['active'=>'Hoạt động','maintenance'=>'Bảo dưỡng','inactive'=>'Ngừng'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= ($edit['status'] ?? 'active')===$k?'selected':'' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="mb-2"><label class="form-label">Ghi chú</label>
            <textarea class="form-control" name="note"><?= e($edit['note'] ?? '') ?></textarea></div>
          <button class="btn btn-primary"><?= $edit ? 'Cập nhật' : 'Thêm mới' ?></button>
          <?php if ($edit): ?><a href="cars.php" class="btn btn-secondary">Hủy</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Dòng xe</th><th>Biển số</th><th>Chỗ</th><th>Công ty</th><th>Trạng thái</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($cars as $c): ?>
            <tr>
              <td><?= e($c['name']) ?></td>
              <td><?= e($c['plate_number']) ?></td>
              <td><?= e($c['seats']) ?></td>
              <td><?= e($c['company']) ?></td>
              <td><span class="badge bg-<?= $c['status']==='active'?'success':($c['status']==='maintenance'?'warning':'secondary') ?>"><?= e($c['status']) ?></span></td>
              <td class="text-end">
                <a href="cars.php?edit=<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Xóa xe này?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $c['id'] ?>">
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
