<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$month = (int)($_GET['month'] ?? date('n'));
$year = (int)($_GET['year'] ?? date('Y'));
$from = sprintf('%04d-%02d-01', $year, $month);
$to = date('Y-m-t', strtotime($from));

$role = current_user()['role'];
$driverFilter = '';
$params = [$from, $to];
if ($role === 'taixe') {
    $driverFilter = ' AND driver_id = ?';
    $params[] = current_user()['driver_id'];
}

$stmt = $pdo->prepare("
    SELECT COUNT(*) trip_count, COALESCE(SUM(revenue_vnd),0) revenue,
           COALESCE(SUM(trip_fee),0) trip_fee, COALESCE(SUM(fuel_cost),0) fuel_cost
    FROM trips WHERE trip_date BETWEEN ? AND ? $driverFilter
");
$stmt->execute($params);
$stat = $stmt->fetch();

$carStats = [];
if ($role !== 'taixe') {
    $stmt = $pdo->prepare("
        SELECT c.name, c.plate_number, COUNT(t.id) trip_count, COALESCE(SUM(t.revenue_vnd),0) revenue
        FROM cars c LEFT JOIN trips t ON t.car_id = c.id AND t.trip_date BETWEEN ? AND ?
        GROUP BY c.id ORDER BY revenue DESC
    ");
    $stmt->execute([$from, $to]);
    $carStats = $stmt->fetchAll();
}
?>
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4>Tổng quan - <?= month_name($month) ?>/<?= $year ?></h4>
  <form class="d-flex gap-2" method="get">
    <select name="month" class="form-select form-select-sm">
      <?php for ($m=1;$m<=12;$m++): ?>
        <option value="<?= $m ?>" <?= $m==$month?'selected':'' ?>>Tháng <?= $m ?></option>
      <?php endfor; ?>
    </select>
    <select name="year" class="form-select form-select-sm">
      <?php for ($y=date('Y')-1;$y<=date('Y')+1;$y++): ?>
        <option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option>
      <?php endfor; ?>
    </select>
    <button class="btn btn-sm btn-primary">Xem</button>
  </form>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card card-stat"><div class="card-body">
      <div class="text-muted small">Số cuốc xe</div>
      <div class="fs-4 fw-bold"><?= $stat['trip_count'] ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat"><div class="card-body">
      <div class="text-muted small">Doanh thu (VNĐ)</div>
      <div class="fs-4 fw-bold"><?= money($stat['revenue']) ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat"><div class="card-body">
      <div class="text-muted small">Tiền tài (chi phí)</div>
      <div class="fs-4 fw-bold"><?= money($stat['trip_fee']) ?></div>
    </div></div>
  </div>
  <div class="col-md-3">
    <div class="card card-stat"><div class="card-body">
      <div class="text-muted small">Chi phí xăng dầu</div>
      <div class="fs-4 fw-bold"><?= money($stat['fuel_cost']) ?></div>
    </div></div>
  </div>
</div>

<?php if ($role !== 'taixe'): ?>
<div class="card">
  <div class="card-header">Doanh thu theo xe</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead><tr><th>Xe</th><th>Biển số</th><th>Số cuốc</th><th class="text-end">Doanh thu (VNĐ)</th></tr></thead>
      <tbody>
        <?php foreach ($carStats as $c): ?>
        <tr>
          <td><?= e($c['name']) ?></td>
          <td><?= e($c['plate_number']) ?></td>
          <td><?= $c['trip_count'] ?></td>
          <td class="text-end"><?= money($c['revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
