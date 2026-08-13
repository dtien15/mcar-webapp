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
            trim($_POST['route_name']),
            (float)$_POST['price_16c_company'], (float)$_POST['price_7c_company'], (float)$_POST['price_4c_company'],
            (float)$_POST['price_16c_external'], (float)$_POST['price_7c_external'], (float)$_POST['price_4c_external'],
            trim($_POST['note']),
        ];
        if ($id > 0) {
            $pdo->prepare("UPDATE price_list SET route_name=?, price_16c_company=?, price_7c_company=?, price_4c_company=?, price_16c_external=?, price_7c_external=?, price_4c_external=?, note=? WHERE id=?")->execute([...$data, $id]);
            flash_set('Đã cập nhật bảng giá.');
        } else {
            $pdo->prepare("INSERT INTO price_list (route_name, price_16c_company, price_7c_company, price_4c_company, price_16c_external, price_7c_external, price_4c_external, note) VALUES (?,?,?,?,?,?,?,?)")->execute($data);
            flash_set('Đã thêm bảng giá.');
        }
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM price_list WHERE id=?")->execute([(int)$_POST['id']]);
        flash_set('Đã xóa.');
    }
    redirect('price_list.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM price_list WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$rows = $pdo->query("SELECT * FROM price_list ORDER BY id")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<h4 class="mb-3">Bảng giá tour</h4>
<div class="card mb-3">
  <div class="card-header"><?= $edit ? 'Sửa bảng giá' : 'Thêm tuyến mới' ?></div>
  <div class="card-body">
    <form method="post" class="row g-2">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="id" value="<?= e($edit['id'] ?? '') ?>">
      <div class="col-md-4"><label class="form-label">Tên tuyến</label>
        <input class="form-control" name="route_name" required value="<?= e($edit['route_name'] ?? '') ?>"></div>
      <div class="col-md-2"><label class="form-label">16c (Cty)</label>
        <input type="number" class="form-control" name="price_16c_company" value="<?= e($edit['price_16c_company'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">7c (Cty)</label>
        <input type="number" class="form-control" name="price_7c_company" value="<?= e($edit['price_7c_company'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">4c (Cty)</label>
        <input type="number" class="form-control" name="price_4c_company" value="<?= e($edit['price_4c_company'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">16c (Ngoài)</label>
        <input type="number" class="form-control" name="price_16c_external" value="<?= e($edit['price_16c_external'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">7c (Ngoài)</label>
        <input type="number" class="form-control" name="price_7c_external" value="<?= e($edit['price_7c_external'] ?? 0) ?>"></div>
      <div class="col-md-2"><label class="form-label">4c (Ngoài)</label>
        <input type="number" class="form-control" name="price_4c_external" value="<?= e($edit['price_4c_external'] ?? 0) ?>"></div>
      <div class="col-md-6"><label class="form-label">Ghi chú</label>
        <input class="form-control" name="note" value="<?= e($edit['note'] ?? '') ?>"></div>
      <div class="col-md-6 d-flex align-items-end">
        <button class="btn btn-primary me-2"><?= $edit ? 'Cập nhật' : 'Thêm mới' ?></button>
        <?php if ($edit): ?><a href="price_list.php" class="btn btn-secondary">Hủy</a><?php endif; ?>
      </div>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-body p-0">
    <table class="table table-sm mb-0 text-nowrap">
      <thead><tr><th>Tuyến</th><th>16c Cty</th><th>7c Cty</th><th>4c Cty</th><th>16c Ngoài</th><th>7c Ngoài</th><th>4c Ngoài</th><th>Ghi chú</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['route_name']) ?></td>
          <td class="text-end"><?= money($r['price_16c_company']) ?></td>
          <td class="text-end"><?= money($r['price_7c_company']) ?></td>
          <td class="text-end"><?= money($r['price_4c_company']) ?></td>
          <td class="text-end"><?= money($r['price_16c_external']) ?></td>
          <td class="text-end"><?= money($r['price_7c_external']) ?></td>
          <td class="text-end"><?= money($r['price_4c_external']) ?></td>
          <td><?= e($r['note']) ?></td>
          <td class="text-end">
            <a href="price_list.php?edit=<?= $r['id'] ?>" class="btn btn-sm btn-outline-primary">Sửa</a>
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
<?php require_once __DIR__ . '/includes/footer.php'; ?>
