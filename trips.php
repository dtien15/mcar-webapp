<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$role = current_user()['role'];

// ---- Xử lý lưu / xóa / xác nhận / chốt ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Tài xế nhập chi phí thực tế + xác nhận chuyến xe được giao
    if ($action === 'driver_confirm') {
        require_role(['taixe']);
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("SELECT * FROM trips WHERE id=? AND driver_id=?");
        $stmt->execute([$id, current_user()['driver_id']]);
        $trip = $stmt->fetch();
        if ($trip && $trip['status'] === 'moi') {
            $pdo->prepare("UPDATE trips SET fuel_cost=?, fuel_payer=?, vetc=?, maintenance=?, fine=?,
                    refund_vnd=?, refund_usd=?, cash_advance=?, direct_payment=?, note=?,
                    status='tai_xe_xac_nhan', driver_confirmed_at=NOW() WHERE id=?")
                ->execute([
                    (float)$_POST['fuel_cost'], trim($_POST['fuel_payer']),
                    (float)$_POST['vetc'], (float)$_POST['maintenance'], (float)$_POST['fine'],
                    (float)$_POST['refund_vnd'], (float)$_POST['refund_usd'], (float)$_POST['cash_advance'],
                    (float)$_POST['direct_payment'], trim($_POST['note']), $id,
                ]);
            flash_set('Đã xác nhận chuyến xe.');
        } else {
            flash_set('Chuyến xe không hợp lệ hoặc đã được xác nhận trước đó.', 'danger');
        }
        redirect('trips.php');
    }

    require_role(['admin', 'ketoan']);

    if ($action === 'complete') {
        $pdo->prepare("UPDATE trips SET status='hoan_thanh', completed_at=NOW() WHERE id=?")->execute([(int)$_POST['id']]);
        flash_set('Đã chốt hoàn thành chuyến xe.');
        redirect('trips.php');
    }

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [
            $_POST['trip_date'],
            trim($_POST['pickup_time']),
            trim($_POST['pickup_dropoff']),
            (float)$_POST['revenue_vnd'], (float)$_POST['revenue_usd'], (float)$_POST['revenue_eur'],
            trim($_POST['route']),
            (float)$_POST['overnight_fee'], (float)$_POST['airport_fee'], (float)$_POST['other_fee'],
            (float)$_POST['driver_advance'],
            $_POST['car_id'] ?: null, $_POST['driver_id'] ?: null,
            (float)$_POST['fuel_cost'], trim($_POST['fuel_payer']),
            (float)$_POST['trip_fee'], $_POST['contract_type_id'] ?: null,
            (float)$_POST['vetc'], (float)$_POST['maintenance'], (float)$_POST['fine'],
            (float)$_POST['refund_vnd'], (float)$_POST['refund_usd'], (float)$_POST['cash_advance'],
            (float)$_POST['direct_payment'], trim($_POST['note']),
        ];
        if ($id > 0) {
            $sql = "UPDATE trips SET trip_date=?, pickup_time=?, pickup_dropoff=?, revenue_vnd=?, revenue_usd=?, revenue_eur=?,
                    route=?, overnight_fee=?, airport_fee=?, other_fee=?, driver_advance=?, car_id=?, driver_id=?,
                    fuel_cost=?, fuel_payer=?, trip_fee=?, contract_type_id=?, vetc=?, maintenance=?, fine=?,
                    refund_vnd=?, refund_usd=?, cash_advance=?, direct_payment=?, note=? WHERE id=?";
            $pdo->prepare($sql)->execute([...$data, $id]);
            flash_set('Đã cập nhật chuyến xe.');
        } else {
            $sql = "INSERT INTO trips (trip_date, pickup_time, pickup_dropoff, revenue_vnd, revenue_usd, revenue_eur,
                    route, overnight_fee, airport_fee, other_fee, driver_advance, car_id, driver_id,
                    fuel_cost, fuel_payer, trip_fee, contract_type_id, vetc, maintenance, fine,
                    refund_vnd, refund_usd, cash_advance, direct_payment, note)
                    VALUES (" . implode(',', array_fill(0, 25, '?')) . ")";
            $pdo->prepare($sql)->execute($data);
            flash_set('Đã thêm chuyến xe mới.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM trips WHERE id=?")->execute([(int)$_POST['id']]);
        flash_set('Đã xóa chuyến xe.');
    }
    redirect('trips.php');
}

// ---- Bộ lọc ----
$filterDriver = $_GET['driver_id'] ?? '';
$filterCar = $_GET['car_id'] ?? '';
$filterFrom = $_GET['from'] ?? date('Y-m-01');
$filterTo = $_GET['to'] ?? date('Y-m-t');

if ($role === 'taixe') {
    $filterDriver = current_user()['driver_id'];
}

$where = ['trip_date BETWEEN ? AND ?'];
$params = [$filterFrom, $filterTo];
if ($filterDriver !== '') { $where[] = 'driver_id = ?'; $params[] = $filterDriver; }
if ($filterCar !== '') { $where[] = 'car_id = ?'; $params[] = $filterCar; }
$whereSql = implode(' AND ', $where);

$stmt = $pdo->prepare("
    SELECT t.*, c.name car_name, c.plate_number, d.full_name driver_name, ct.name contract_name
    FROM trips t
    LEFT JOIN cars c ON c.id = t.car_id
    LEFT JOIN drivers d ON d.id = t.driver_id
    LEFT JOIN contract_types ct ON ct.id = t.contract_type_id
    WHERE $whereSql
    ORDER BY t.trip_date DESC, t.id DESC
    LIMIT 500
");
$stmt->execute($params);
$trips = $stmt->fetchAll();

$totalRevenue = array_sum(array_column($trips, 'revenue_vnd'));
$totalFee = array_sum(array_column($trips, 'trip_fee'));

$edit = null;
if (isset($_GET['edit']) && in_array($role, ['admin','ketoan'])) {
    $stmt = $pdo->prepare("SELECT * FROM trips WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$cars = $pdo->query("SELECT id, name, plate_number FROM cars ORDER BY name")->fetchAll();
$drivers = $pdo->query("SELECT id, full_name FROM drivers ORDER BY full_name")->fetchAll();
$contractTypes = $pdo->query("SELECT id, name FROM contract_types ORDER BY name")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<h4 class="mb-3">Chuyến xe</h4>

<?php if (in_array($role, ['admin','ketoan'])): ?>
<div class="card mb-3">
  <div class="card-header"><?= $edit ? 'Sửa chuyến xe #' . $edit['id'] : 'Thêm chuyến xe mới' ?></div>
  <div class="card-body">
    <form method="post" class="row g-2">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">

      <div class="col-md-2"><label class="form-label">Ngày chạy xe</label>
        <input type="date" class="form-control" name="trip_date" required value="<?= e($edit['trip_date'] ?? date('Y-m-d')) ?>"></div>
      <div class="col-md-2"><label class="form-label">Giờ đón</label>
        <input class="form-control" name="pickup_time" value="<?= e($edit['pickup_time'] ?? '') ?>"></div>
      <div class="col-md-4"><label class="form-label">Điểm đón - trả / ghi chú khách</label>
        <input class="form-control" name="pickup_dropoff" value="<?= e($edit['pickup_dropoff'] ?? '') ?>"></div>
      <div class="col-md-2"><label class="form-label">Hành trình</label>
        <input class="form-control" name="route" value="<?= e($edit['route'] ?? '') ?>"></div>
      <div class="col-md-2"><label class="form-label">Xe</label>
        <select class="form-select" name="car_id">
          <option value="">--</option>
          <?php foreach ($cars as $c): ?>
            <option value="<?= $c['id'] ?>" <?= ($edit['car_id'] ?? '')==$c['id']?'selected':'' ?>><?= e($c['name'].' '.$c['plate_number']) ?></option>
          <?php endforeach; ?>
        </select></div>

      <div class="col-md-2"><label class="form-label">Tài xế</label>
        <select class="form-select" name="driver_id">
          <option value="">--</option>
          <?php foreach ($drivers as $d): ?>
            <option value="<?= $d['id'] ?>" <?= ($edit['driver_id'] ?? '')==$d['id']?'selected':'' ?>><?= e($d['full_name']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-md-2"><label class="form-label">Loại kèo</label>
        <select class="form-select" name="contract_type_id">
          <option value="">--</option>
          <?php foreach ($contractTypes as $ct): ?>
            <option value="<?= $ct['id'] ?>" <?= ($edit['contract_type_id'] ?? '')==$ct['id']?'selected':'' ?>><?= e($ct['name']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-md-2"><label class="form-label">Txe thu VNĐ</label>
        <input type="number" class="form-control" name="revenue_vnd" value="<?= e($edit['revenue_vnd'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Txe thu USD</label>
        <input type="number" step="0.01" class="form-control" name="revenue_usd" value="<?= e($edit['revenue_usd'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Txe thu EUR</label>
        <input type="number" step="0.01" class="form-control" name="revenue_eur" value="<?= e($edit['revenue_eur'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Tiền cuốc xe (tiền tài)</label>
        <input type="number" class="form-control" name="trip_fee" value="<?= e($edit['trip_fee'] ?? 0) ?>"></div>

      <div class="col-md-2"><label class="form-label">Lưu đêm</label>
        <input type="number" class="form-control" name="overnight_fee" value="<?= e($edit['overnight_fee'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Phí sân bay</label>
        <input type="number" class="form-control" name="airport_fee" value="<?= e($edit['airport_fee'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Phát sinh khác</label>
        <input type="number" class="form-control" name="other_fee" value="<?= e($edit['other_fee'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Tiền tài ứng</label>
        <input type="number" class="form-control" name="driver_advance" value="<?= e($edit['driver_advance'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Xăng dầu</label>
        <input type="number" class="form-control" name="fuel_cost" value="<?= e($edit['fuel_cost'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Người trả xăng dầu</label>
        <input class="form-control" name="fuel_payer" value="<?= e($edit['fuel_payer'] ?? '') ?>"></div>

      <div class="col-md-2"><label class="form-label">VETC</label>
        <input type="number" class="form-control" name="vetc" value="<?= e($edit['vetc'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Bảo dưỡng</label>
        <input type="number" class="form-control" name="maintenance" value="<?= e($edit['maintenance'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Phạt</label>
        <input type="number" class="form-control" name="fine" value="<?= e($edit['fine'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Hoàn tiền VNĐ</label>
        <input type="number" class="form-control" name="refund_vnd" value="<?= e($edit['refund_vnd'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Hoàn tiền USD</label>
        <input type="number" step="0.01" class="form-control" name="refund_usd" value="<?= e($edit['refund_usd'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">Tạm ứng</label>
        <input type="number" class="form-control" name="cash_advance" value="<?= e($edit['cash_advance'] ?? 0) ?>"></div>

      <div class="col-md-2"><label class="form-label">Khách TT trực tiếp cty</label>
        <input type="number" class="form-control" name="direct_payment" value="<?= e($edit['direct_payment'] ?? 0) ?>"></div>
      <div class="col-md-6"><label class="form-label">Ghi chú</label>
        <input class="form-control" name="note" value="<?= e($edit['note'] ?? '') ?>"></div>
      <div class="col-md-4 d-flex align-items-end">
        <button class="btn btn-primary me-2"><?= $edit ? 'Cập nhật' : 'Thêm mới' ?></button>
        <?php if ($edit): ?><a href="trips.php" class="btn btn-secondary">Hủy</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="card mb-3">
  <div class="card-body">
    <form class="row g-2" method="get">
      <div class="col-md-3"><label class="form-label">Từ ngày</label>
        <input type="date" class="form-control" name="from" value="<?= e($filterFrom) ?>"></div>
      <div class="col-md-3"><label class="form-label">Đến ngày</label>
        <input type="date" class="form-control" name="to" value="<?= e($filterTo) ?>"></div>
      <?php if ($role !== 'taixe'): ?>
      <div class="col-md-3"><label class="form-label">Tài xế</label>
        <select class="form-select" name="driver_id">
          <option value="">Tất cả</option>
          <?php foreach ($drivers as $d): ?>
            <option value="<?= $d['id'] ?>" <?= $filterDriver==$d['id']?'selected':'' ?>><?= e($d['full_name']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <?php endif; ?>
      <div class="col-md-3"><label class="form-label">Xe</label>
        <select class="form-select" name="car_id">
          <option value="">Tất cả</option>
          <?php foreach ($cars as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $filterCar==$c['id']?'selected':'' ?>><?= e($c['name'].' '.$c['plate_number']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-12"><button class="btn btn-primary btn-sm">Lọc</button></div>
    </form>
  </div>
</div>

<div class="mb-2 text-muted">Tổng: <?= count($trips) ?> cuốc xe | Doanh thu: <strong><?= money($totalRevenue) ?></strong> VNĐ | Tổng tiền tài: <strong><?= money($totalFee) ?></strong> VNĐ</div>

<div class="card">
  <div class="card-body p-0" style="overflow-x:auto;">
    <table class="table table-sm mb-0 text-nowrap">
      <thead><tr>
        <th>Ngày</th><th>Hành trình</th><th>Xe</th><th>Tài xế</th><th>Loại kèo</th>
        <th class="text-end">Thu VNĐ</th><th class="text-end">Tiền tài</th><th class="text-end">Xăng dầu</th>
        <th>Trạng thái</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($trips as $t):
        $statusLabel = ['moi' => 'Mới giao', 'tai_xe_xac_nhan' => 'Tài xế đã xác nhận', 'hoan_thanh' => 'Hoàn thành'][$t['status']];
        $statusBadge = ['moi' => 'secondary', 'tai_xe_xac_nhan' => 'warning', 'hoan_thanh' => 'success'][$t['status']];
        $isOwnTrip = $role === 'taixe' && $t['driver_id'] == current_user()['driver_id'];
      ?>
        <tr>
          <td><?= e(date('d/m/Y', strtotime($t['trip_date']))) ?></td>
          <td><?= e($t['route']) ?></td>
          <td><?= e($t['car_name'] . ' ' . $t['plate_number']) ?></td>
          <td><?= e($t['driver_name']) ?></td>
          <td><?= e($t['contract_name']) ?></td>
          <td class="text-end"><?= money($t['revenue_vnd']) ?></td>
          <td class="text-end"><?= money($t['trip_fee']) ?></td>
          <td class="text-end"><?= money($t['fuel_cost']) ?></td>
          <td><span class="badge bg-<?= $statusBadge ?>"><?= $statusLabel ?></span></td>
          <td class="text-end">
            <?php if (in_array($role, ['admin','ketoan'])): ?>
              <a href="trips.php?edit=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
              <?php if ($t['status'] === 'tai_xe_xac_nhan'): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('Chốt hoàn thành chuyến xe này?');">
                  <input type="hidden" name="action" value="complete">
                  <input type="hidden" name="id" value="<?= $t['id'] ?>">
                  <button class="btn btn-sm btn-success">Chốt hoàn thành</button>
                </form>
              <?php endif; ?>
              <form method="post" class="d-inline" onsubmit="return confirm('Xóa chuyến xe này?');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button class="btn btn-sm btn-outline-danger">Xóa</button>
              </form>
            <?php elseif ($isOwnTrip && $t['status'] === 'moi'): ?>
              <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#confirm<?= $t['id'] ?>">Nhập & Xác nhận</button>
            <?php endif; ?>
          </td>
        </tr>

        <?php if ($isOwnTrip && $t['status'] === 'moi'): ?>
        <div class="modal fade" id="confirm<?= $t['id'] ?>" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <form method="post" class="modal-content">
              <input type="hidden" name="action" value="driver_confirm">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <div class="modal-header">
                <h5 class="modal-title">Xác nhận chuyến xe - <?= e(date('d/m/Y', strtotime($t['trip_date']))) ?> (<?= e($t['route']) ?>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p class="text-muted small mb-3">
                  Xe: <strong><?= e($t['car_name'].' '.$t['plate_number']) ?></strong> |
                  Thu khách: <strong><?= money($t['revenue_vnd']) ?> VNĐ</strong> |
                  Tiền cuốc xe: <strong><?= money($t['trip_fee']) ?> VNĐ</strong>
                  (các thông tin này do admin giao, không sửa được — nếu sai liên hệ admin)
                </p>
                <div class="row g-2">
                  <div class="col-md-4"><label class="form-label">Tiền xăng dầu</label>
                    <input type="number" class="form-control" name="fuel_cost" value="0"></div>
                  <div class="col-md-4"><label class="form-label">Người trả xăng dầu</label>
                    <input class="form-control" name="fuel_payer"></div>
                  <div class="col-md-4"><label class="form-label">VETC</label>
                    <input type="number" class="form-control" name="vetc" value="0"></div>
                  <div class="col-md-4"><label class="form-label">Bảo dưỡng</label>
                    <input type="number" class="form-control" name="maintenance" value="0"></div>
                  <div class="col-md-4"><label class="form-label">Phạt</label>
                    <input type="number" class="form-control" name="fine" value="0"></div>
                  <div class="col-md-4"><label class="form-label">Tạm ứng</label>
                    <input type="number" class="form-control" name="cash_advance" value="0"></div>
                  <div class="col-md-4"><label class="form-label">Hoàn tiền VNĐ</label>
                    <input type="number" class="form-control" name="refund_vnd" value="0"></div>
                  <div class="col-md-4"><label class="form-label">Hoàn tiền USD</label>
                    <input type="number" step="0.01" class="form-control" name="refund_usd" value="0"></div>
                  <div class="col-md-4"><label class="form-label">Khách TT trực tiếp cty</label>
                    <input type="number" class="form-control" name="direct_payment" value="0"></div>
                  <div class="col-12"><label class="form-label">Ghi chú</label>
                    <textarea class="form-control" name="note"></textarea></div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button class="btn btn-primary">✔ Xác nhận chuyến xe</button>
              </div>
            </form>
          </div>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
