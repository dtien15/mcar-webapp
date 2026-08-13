<?php
$page = basename($_SERVER['SCRIPT_NAME']);
function navlink($file, $label, $page) {
    $active = $page === $file ? 'active' : '';
    echo '<a class="nav-link ' . $active . '" href="' . $file . '">' . $label . '</a>';
}
$role = current_user()['role'];
?>
<nav class="sidebar bg-light border-end p-3" style="width:220px; min-height: calc(100vh - 56px);">
  <div class="nav flex-column nav-pills">
    <?php navlink('index.php', '📊 Tổng quan', $page); ?>
    <?php navlink('trips.php', '🚕 Chuyến xe', $page); ?>
    <?php if (in_array($role, ['admin','ketoan'])): ?>
      <?php navlink('payroll.php', '💰 Bảng lương', $page); ?>
      <?php navlink('payments.php', '🧾 Thanh toán / công nợ', $page); ?>
      <?php navlink('reports.php', '📈 Báo cáo doanh thu', $page); ?>
      <hr>
      <?php navlink('cars.php', '🚙 Danh mục Xe', $page); ?>
      <?php navlink('drivers.php', '🧑‍✈️ Danh mục Tài xế', $page); ?>
      <?php navlink('contract_types.php', '📋 Loại kèo', $page); ?>
      <?php navlink('price_list.php', '💵 Bảng giá', $page); ?>
    <?php endif; ?>
    <?php if ($role === 'admin'): ?>
      <hr>
      <?php navlink('users.php', '👤 Người dùng', $page); ?>
    <?php endif; ?>
  </div>
</nav>
