<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin', 'ketoan']);

$month = (int)($_GET['month'] ?? date('n'));
$year = (int)($_GET['year'] ?? date('Y'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'calc_all') {
        $drivers = $pdo->query("SELECT id FROM drivers WHERE status='active'")->fetchAll();
        foreach ($drivers as $d) {
            recalc_payroll($pdo, $d['id'], $month, $year);
        }
        flash_set('Đã tính lại lương cho tất cả tài xế trong kỳ.');
    } elseif ($action === 'calc_one') {
        recalc_payroll($pdo, (int)$_POST['driver_id'], $month, $year);
        flash_set('Đã tính lại lương tài xế.');
    } elseif ($action === 'update_paid') {
        $pdo->prepare("UPDATE payroll SET company_paid=?, remaining = total_salary + prev_balance - total_collected + total_refund - ?, status=?, note=? WHERE id=?")
            ->execute([
                (float)$_POST['company_paid'], (float)$_POST['company_paid'],
                trim($_POST['status']), trim($_POST['note']), (int)$_POST['id']
            ]);
        flash_set('Đã cập nhật thanh toán.');
    }
    redirect("payroll.php?month=$month&year=$year");
}

$stmt = $pdo->prepare("
    SELECT p.*, d.full_name, d.short_name
    FROM payroll p JOIN drivers d ON d.id = p.driver_id
    WHERE p.month = ? AND p.year = ?
    ORDER BY d.full_name
");
$stmt->execute([$month, $year]);
$rows = $stmt->fetchAll();

$drivers = $pdo->query("SELECT id, full_name FROM drivers ORDER BY full_name")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Bảng lương tài xế</h4>
  <form class="d-flex gap-2" method="get">
    <select name="month" class="form-select form-select-sm">
      <?php for ($m=1;$m<=12;$m++): ?><option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>>Tháng <?= $m ?></option><?php endfor; ?>
    </select>
    <select name="year" class="form-select form-select-sm">
      <?php for ($y=date('Y')-1;$y<=date('Y')+1;$y++): ?><option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
    </select>
    <button class="btn btn-sm btn-primary">Xem</button>
  </form>
</div>

<form method="post" class="mb-3">
  <input type="hidden" name="action" value="calc_all">
  <button class="btn btn-success btn-sm">🔄 Tính lại lương tất cả tài xế (kỳ <?= $month ?>/<?= $year ?>)</button>
</form>

<div class="card">
  <div class="card-body p-0" style="overflow-x:auto;">
    <table class="table table-sm mb-0 text-nowrap">
      <thead><tr>
        <th>Tài xế</th><th class="text-end">Số cuốc</th><th class="text-end">Lưu đêm</th>
        <th class="text-end">Tiền tài</th><th class="text-end">Phạt</th><th class="text-end">Thu khách</th>
        <th class="text-end">Kỳ trước</th><th class="text-end">Tổng lương</th>
        <th class="text-end">Cty đã trả</th><th class="text-end">Còn lại</th><th>Trạng thái</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['full_name']) ?></td>
          <td class="text-end"><?= $r['trip_count'] ?></td>
          <td class="text-end"><?= money($r['total_overnight']) ?></td>
          <td class="text-end"><?= money($r['total_fee']) ?></td>
          <td class="text-end"><?= money($r['total_fine']) ?></td>
          <td class="text-end"><?= money($r['total_collected']) ?></td>
          <td class="text-end"><?= money($r['prev_balance']) ?></td>
          <td class="text-end fw-bold"><?= money($r['total_salary']) ?></td>
          <td class="text-end"><?= money($r['company_paid']) ?></td>
          <td class="text-end fw-bold <?= $r['remaining']<0?'text-danger':'text-success' ?>"><?= money($r['remaining']) ?></td>
          <td><?= e($r['status']) ?></td>
          <td>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#pay<?= $r['id'] ?>">Cập nhật</button>
          </td>
        </tr>
        <div class="modal fade" id="pay<?= $r['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <form method="post" class="modal-content">
              <input type="hidden" name="action" value="update_paid">
              <input type="hidden" name="id" value="<?= $r['id'] ?>">
              <div class="modal-header"><h5 class="modal-title">Cập nhật thanh toán - <?= e($r['full_name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body">
                <div class="mb-2"><label class="form-label">Công ty đã trả</label>
                  <input type="number" class="form-control" name="company_paid" value="<?= e($r['company_paid']) ?>"></div>
                <div class="mb-2"><label class="form-label">Trạng thái</label>
                  <input class="form-control" name="status" value="<?= e($r['status']) ?>"></div>
                <div class="mb-2"><label class="form-label">Ghi chú</label>
                  <textarea class="form-control" name="note"><?= e($r['note']) ?></textarea></div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-primary">Lưu</button>
              </div>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (!$rows): ?>
        <tr><td colspan="11" class="text-center text-muted py-3">Chưa có dữ liệu lương kỳ này. Bấm "Tính lại lương" ở trên.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
