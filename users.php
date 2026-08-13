<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $username = trim($_POST['username']);
        $full_name = trim($_POST['full_name']);
        $role = $_POST['role'];
        $driver_id = $_POST['driver_id'] ?: null;
        $status = $_POST['status'];

        if ($id > 0) {
            if (!empty($_POST['password'])) {
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE users SET username=?, password=?, full_name=?, role=?, driver_id=?, status=? WHERE id=?")
                    ->execute([$username, $hash, $full_name, $role, $driver_id, $status, $id]);
            } else {
                $pdo->prepare("UPDATE users SET username=?, full_name=?, role=?, driver_id=?, status=? WHERE id=?")
                    ->execute([$username, $full_name, $role, $driver_id, $status, $id]);
            }
            flash_set('Đã cập nhật người dùng.');
        } else {
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (username, password, full_name, role, driver_id, status) VALUES (?,?,?,?,?,?)")
                ->execute([$username, $hash, $full_name, $role, $driver_id, $status]);
            flash_set('Đã tạo người dùng mới.');
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        if ($id !== current_user()['id']) {
            $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
            flash_set('Đã xóa người dùng.');
        }
    }
    redirect('users.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$users = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll();
$drivers = $pdo->query("SELECT id, full_name FROM drivers ORDER BY full_name")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<h4 class="mb-3">Quản lý người dùng</h4>
<div class="row g-3">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><?= $edit ? 'Sửa người dùng' : 'Thêm người dùng mới' ?></div>
      <div class="card-body">
        <form method="post">
          <input type="hidden" name="action" value="save">
          <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
          <div class="mb-2"><label class="form-label">Tên đăng nhập</label>
            <input class="form-control" name="username" required value="<?= e($edit['username'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Mật khẩu <?= $edit ? '(để trống nếu không đổi)' : '' ?></label>
            <input type="password" class="form-control" name="password" <?= $edit ? '' : 'required' ?>></div>
          <div class="mb-2"><label class="form-label">Họ tên</label>
            <input class="form-control" name="full_name" value="<?= e($edit['full_name'] ?? '') ?>"></div>
          <div class="mb-2"><label class="form-label">Vai trò</label>
            <select class="form-select" name="role">
              <?php foreach (['admin'=>'Quản trị','ketoan'=>'Kế toán','taixe'=>'Tài xế'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= ($edit['role'] ?? '')===$k?'selected':'' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="mb-2"><label class="form-label">Gắn với tài xế (nếu vai trò = Tài xế)</label>
            <select class="form-select" name="driver_id">
              <option value="">--</option>
              <?php foreach ($drivers as $d): ?>
                <option value="<?= $d['id'] ?>" <?= ($edit['driver_id'] ?? '')==$d['id']?'selected':'' ?>><?= e($d['full_name']) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="mb-2"><label class="form-label">Trạng thái</label>
            <select class="form-select" name="status">
              <option value="active" <?= ($edit['status'] ?? 'active')==='active'?'selected':'' ?>>Hoạt động</option>
              <option value="inactive" <?= ($edit['status'] ?? '')==='inactive'?'selected':'' ?>>Khóa</option>
            </select></div>
          <button class="btn btn-primary"><?= $edit ? 'Cập nhật' : 'Thêm mới' ?></button>
          <?php if ($edit): ?><a href="users.php" class="btn btn-secondary">Hủy</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <div class="card">
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Tài khoản</th><th>Họ tên</th><th>Vai trò</th><th>Trạng thái</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?= e($u['username']) ?></td>
              <td><?= e($u['full_name']) ?></td>
              <td><?= e($u['role']) ?></td>
              <td><span class="badge bg-<?= $u['status']==='active'?'success':'secondary' ?>"><?= e($u['status']) ?></span></td>
              <td class="text-end">
                <a href="users.php?edit=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
                <?php if ($u['id'] != current_user()['id']): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('Xóa người dùng này?');">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </form>
                <?php endif; ?>
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
