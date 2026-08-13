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
            trim($_POST['full_name']),
            trim($_POST['short_name']),
            trim($_POST['phone']),
            trim($_POST['bank_name']),
            trim($_POST['bank_account']),
            (float)$_POST['base_salary'],
            (float)$_POST['insurance'],
            trim($_POST['managing_company']),
            $_POST['status'],
            trim($_POST['note']),
        ];
        if ($id > 0) {
            $stmt = $pdo->prepare("UPDATE drivers SET full_name=?, short_name=?, phone=?, bank_name=?, bank_account=?, base_salary=?, insurance=?, managing_company=?, status=?, note=? WHERE id=?");
            $stmt->execute([...$data, $id]);
            flash_set('Đã cập nhật tài xế.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO drivers (full_name, short_name, phone, bank_name, bank_account, base_salary, insurance, managing_company, status, note) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute($data);
            flash_set('Đã thêm tài xế mới.');
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $pdo->prepare("DELETE FROM drivers WHERE id=?")->execute([$id]);
        flash_set('Đã xóa tài xế.');
    }
    redirect('drivers.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM drivers WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$drivers = $pdo->query("SELECT * FROM drivers ORDER BY id")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<h4 class="mb-3">Danh mục Tài xế</h4>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><?= $edit ? 'Sửa tài xế' : 'Thêm tài xế mới' ?></div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
          <div class="mb-2"><label class="form-label">Họ tên</label>
            <input class="form-control" name="full_name" required value="<?= e($edit['full_name'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Tên gọi</label>
            <input class="form-control" name="short_name" value="<?= e($edit['short_name'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Điện thoại</label>
            <input class="form-control" name="phone" value="<?= e($edit['phone'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Ngân hàng</label>
            <input class="form-control" name="bank_name" value="<?= e($edit['bank_name'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Số tài khoản</label>
            <input class="form-control" name="bank_account" value="<?= e($edit['bank_account'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Lương cơ bản (LCB)</label>
            <input type="number" class="form-control" name="base_salary" value="<?= e($edit['base_salary'] ?? 0) ?>"></div>
          <div class="mb-2"><label class="form-label">BH/BHXH</label>
            <input type="number" class="form-control" name="insurance" value="<?= e($edit['insurance'] ?? 0) ?>"></div>
          <div class="mb-2"><label class="form-label">Công ty quản lý</label>
            <input class="form-control" name="managing_company" value="<?= e($edit['managing_company'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Trạng thái</label>
            <select class="form-select" name="status">
              <?php foreach (['active'=>'Đang chạy','inactive'=>'Nghỉ'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= ($edit['status'] ?? 'active')===$k?'selected':'' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="mb-2"><label class="form-label">Ghi chú</label>
            <textarea class="form-control" name="note"><?= e($edit['note'] ?? '') ?></textarea></div>
          <button class="btn btn-primary"><?= $edit ? 'Cập nhật' : 'Thêm mới' ?></button>
          <?php if ($edit): ?><a href="drivers.php" class="btn btn-secondary">Hủy</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Họ tên</th><th>Ngân hàng</th><th>Lương CB</th><th>Trạng thái</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($drivers as $d): ?>
            <tr>
              <td><?= e($d['full_name']) ?> <span class="text-muted">(<?= e($d['short_name']) ?>)</span></td>
              <td><?= e($d['bank_name']) ?> - <?= e($d['bank_account']) ?></td>
              <td class="text-end"><?= money($d['base_salary']) ?></td>
              <td><span class="badge bg-<?= $d['status']==='active'?'success':'secondary' ?>"><?= e($d['status']) ?></span></td>
              <td class="text-end">
                <a href="drivers.php?edit=<?= $d['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Xóa tài xế này?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $d['id'] ?>">
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
