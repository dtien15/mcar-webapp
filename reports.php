<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_role(['admin', 'ketoan']);

$year = (int)($_GET['year'] ?? date('Y'));

// Doanh thu theo tháng trong năm
$stmt = $pdo->prepare("
    SELECT MONTH(trip_date) m, COALESCE(SUM(revenue_vnd),0) revenue, COUNT(*) trip_count
    FROM trips WHERE YEAR(trip_date) = ?
    GROUP BY MONTH(trip_date) ORDER BY m
");
$stmt->execute([$year]);
$byMonth = [];
foreach ($stmt->fetchAll() as $r) $byMonth[(int)$r['m']] = $r;

// Doanh thu theo xe trong năm
$stmt = $pdo->prepare("
    SELECT c.name, c.plate_number, COUNT(t.id) trip_count, COALESCE(SUM(t.revenue_vnd),0) revenue,
           COALESCE(SUM(t.fuel_cost),0) fuel_cost, COALESCE(SUM(t.maintenance),0) maintenance
    FROM cars c LEFT JOIN trips t ON t.car_id = c.id AND YEAR(t.trip_date) = ?
    GROUP BY c.id ORDER BY revenue DESC
");
$stmt->execute([$year]);
$byCar = $stmt->fetchAll();

// Doanh thu theo tài xế trong năm
$stmt = $pdo->prepare("
    SELECT d.full_name, COUNT(t.id) trip_count, COALESCE(SUM(t.revenue_vnd),0) revenue, COALESCE(SUM(t.trip_fee),0) trip_fee
    FROM drivers d LEFT JOIN trips t ON t.driver_id = d.id AND YEAR(t.trip_date) = ?
    GROUP BY d.id ORDER BY revenue DESC
");
$stmt->execute([$year]);
$byDriver = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
$maxRevenue = max(array_map(fn($m) => $m['revenue'] ?? 0, $byMonth) ?: [1]) ?: 1;
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4>Báo cáo doanh thu</h4>
  <form class="d-flex gap-2" method="get">
    <select name="year" class="form-select form-select-sm">
      <?php for ($y=date('Y')-2;$y<=date('Y')+1;$y++): ?><option value="<?= $y ?>" <?= $y==$year?'selected':'' ?>><?= $y ?></option><?php endfor; ?>
    </select>
    <button class="btn btn-sm btn-primary">Xem</button>
  </form>
</div>

<div class="card mb-3">
  <div class="card-header">Doanh thu theo tháng - <?= $year ?></div>
  <div class="card-body">
    <?php for ($m=1;$m<=12;$m++): $rev = $byMonth[$m]['revenue'] ?? 0; $pct = $maxRevenue ? ($rev/$maxRevenue*100) : 0; ?>
      <div class="d-flex align-items-center mb-1">
        <div style="width:70px;">Th <?= $m ?></div>
        <div class="flex-grow-1 bg-light rounded"><div class="bg-primary text-white small px-2 rounded" style="width:<?= max($pct,2) ?>%; white-space:nowrap;"><?= money($rev) ?></div></div>
      </div>
    <?php endfor; ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Doanh thu theo xe</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Xe</th><th class="text-end">Cuốc</th><th class="text-end">Doanh thu</th><th class="text-end">Xăng dầu</th><th class="text-end">Bảo dưỡng</th></tr></thead>
          <tbody>
          <?php foreach ($byCar as $c): ?>
            <tr>
              <td><?= e($c['name'].' '.$c['plate_number']) ?></td>
              <td class="text-end"><?= $c['trip_count'] ?></td>
              <td class="text-end"><?= money($c['revenue']) ?></td>
              <td class="text-end"><?= money($c['fuel_cost']) ?></td>
              <td class="text-end"><?= money($c['maintenance']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">Doanh thu theo tài xế</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Tài xế</th><th class="text-end">Cuốc</th><th class="text-end">Doanh thu</th><th class="text-end">Tiền tài</th></tr></thead>
          <tbody>
          <?php foreach ($byDriver as $d): ?>
            <tr>
              <td><?= e($d['full_name']) ?></td>
              <td class="text-end"><?= $d['trip_count'] ?></td>
              <td class="text-end"><?= money($d['revenue']) ?></td>
              <td class="text-end"><?= money($d['trip_fee']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
