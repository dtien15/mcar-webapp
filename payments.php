<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin', 'ketoan']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [$_POST['payment_date'], trim($_POST['content']), (float)$_POST['amount'], trim($_POST['category']), trim($_POST['note'])];
        if ($id > 0) {
            $pdo->prepare("UPDATE payments SET payment_date=?, content=?, amount=?, category=?, note=? WHERE id=?")->execute([...$data, $id]);
            flash_set('Đã cập nhật khoản chi.');
        } else {
            $pdo->prepare("INSERT INTO payments (payment_date, content, amount, category, note) VALUES (?,?,?,?,?)")->execute($data);
            flash_set('Đã thêm khoản chi.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM payments WHERE id=?")->execute([(int)$_POST['id']]);
        flash_set('Đã xóa.');
    }
    redirect('payments.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$rows = $pdo->query("SELECT * FROM payments ORDER BY payment_date DESC, id DESC LIMIT 300")->fetchAll();
$total = array_sum(array_column($rows, 'amount'));

// Công nợ tài xế còn thiếu/thừa (từ payroll kỳ gần nhất mỗi tài xế)
$debts = $pdo->query("
    SELECT p.*, d.full_name FROM payroll p
    JOIN drivers d ON d.id = p.driver_id
    WHERE p.id IN (
        SELECT MAX(id) FROM payroll GROUP BY driver_id
    )
    ORDER BY p.remaining ASC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<h4 class="mb-3">Thanh toán & Công nợ</h4>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab1">Khoản chi công ty</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab2">Công nợ tài xế</a></li>
</ul>

<div class="tab-content">
<div class="tab-pane fade show active" id="tab1">
  <div class="card mb-3">
    <div class="card-header"><?= $edit ? 'Sửa khoản chi' : 'Thêm khoản chi mới' ?></div>
    <div class="card-body">
      <form method="post" class="row g-2">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
        <div class="col-md-2"><label class="form-label">Ngày</label>
          <input type="date" class="form-control" name="payment_date" required value="<?= e($edit['payment_date'] ?? date('Y-m-d')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Nội dung</label>
          <input class="form-control" name="content" required value="<?= e($edit['content'] ?? '') ?>"></div>
        <div class="col-md-2"><label class="form-label">Số tiền</label>
          <input type="number" class="form-control" name="amount" value="<?= e($edit['amount'] ?? 0) ?>"></div>
        <div class="col-md-2"><label class="form-label">Loại</label>
          <input class="form-control" name="category" value="<?= e($edit['category'] ?? '') ?>"></div>
        <div class="col-md-2"><label class="form-label">Ghi chú</label>
          <input class="form-control" name="note" value="<?= e($edit['note'] ?? '') ?>"></div>
        <div class="col-12">
          <button class="btn btn-primary btn-sm"><?= $edit ? 'Cập nhật' : 'Thêm mới' ?></button>
          <?php if ($edit): ?><a href="payments.php" class="btn btn-secondary btn-sm">Hủy</a><?php endif; ?>
        </div>
      </form>
    </div>
  </div>
  <div class="mb-2 text-muted">Tổng cộng: <strong><?= money($total) ?></strong> VNĐ</div>
  <div class="card">
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>Ngày</th><th>Nội dung</th><th class="text-end">Số tiền</th><th>Loại</th><th>Ghi chú</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= e(date('d/m/Y', strtotime($r['payment_date']))) ?></td>
            <td><?= e($r['content']) ?></td>
            <td class="text-end"><?= money($r['amount']) ?></td>
            <td><?= e($r['category']) ?></td>
            <td><?= e($r['note']) ?></td>
            <td class="text-end">
              <a href="payments.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
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

<div class="tab-pane fade" id="tab2">
  <div class="card">
    <div class="card-header">Số dư công nợ mới nhất theo tài xế (âm = tài xế còn thiếu công ty, dương = công ty còn thiếu tài xế)</div>
    <div class="card-body p-0">
      <table class="table table-sm mb-0">
        <thead><tr><th>Tài xế</th><th>Kỳ</th><th class="text-end">Còn lại</th><th>Trạng thái</th></tr></thead>
        <tbody>
        <?php foreach ($debts as $d): ?>
          <tr>
            <td><?= e($d['full_name']) ?></td>
            <td><?= $d['month'] ?>/<?= $d['year'] ?></td>
            <td class="text-end fw-bold <?= $d['remaining']<0?'text-danger':'text-success' ?>"><?= money($d['remaining']) ?></td>
            <td><?= e($d['status']) ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
