<?php
function money($n) {
    return number_format((float)$n, 0, ',', '.');
}

function e($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function flash_set($msg, $type = 'success') {
    $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
}

function flash_get() {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

function month_name($m) {
    return 'Tháng ' . (int)$m;
}

// Tự tính lại bảng lương 1 tài xế trong 1 tháng, dựa trên dữ liệu trips
function recalc_payroll(PDO $pdo, $driver_id, $month, $year) {
    $from = sprintf('%04d-%02d-01', $year, $month);
    $to = date('Y-m-t', strtotime($from));

    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS trip_count,
            COALESCE(SUM(overnight_fee),0) AS total_overnight,
            COALESCE(SUM(trip_fee),0) AS total_fee,
            COALESCE(SUM(fine),0) AS total_fine,
            COALESCE(SUM(revenue_vnd),0) AS total_collected,
            COALESCE(SUM(refund_vnd),0) AS total_refund
        FROM trips
        WHERE driver_id = ? AND trip_date BETWEEN ? AND ?
    ");
    $stmt->execute([$driver_id, $from, $to]);
    $agg = $stmt->fetch();

    // Số dư tháng trước (remaining của kỳ liền trước)
    $prevMonth = $month == 1 ? 12 : $month - 1;
    $prevYear = $month == 1 ? $year - 1 : $year;
    $stmt = $pdo->prepare("SELECT remaining FROM payroll WHERE driver_id=? AND month=? AND year=?");
    $stmt->execute([$driver_id, $prevMonth, $prevYear]);
    $prev = $stmt->fetchColumn();
    $prev_balance = $prev !== false ? (float)$prev : 0;

    $stmt = $pdo->prepare("SELECT base_salary FROM drivers WHERE id=?");
    $stmt->execute([$driver_id]);
    $base_salary = (float)$stmt->fetchColumn();

    $total_salary = $base_salary + $agg['total_overnight'] + $agg['total_fee'] - $agg['total_fine'];
    $remaining = $total_salary + $prev_balance - $agg['total_collected'] + $agg['total_refund'];

    $stmt = $pdo->prepare("
        INSERT INTO payroll (driver_id, month, year, from_date, to_date, trip_count, total_overnight,
            total_fee, total_fine, total_collected, total_refund, prev_balance, total_salary, remaining)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
            from_date=VALUES(from_date), to_date=VALUES(to_date), trip_count=VALUES(trip_count),
            total_overnight=VALUES(total_overnight), total_fee=VALUES(total_fee), total_fine=VALUES(total_fine),
            total_collected=VALUES(total_collected), total_refund=VALUES(total_refund),
            prev_balance=VALUES(prev_balance), total_salary=VALUES(total_salary), remaining=VALUES(remaining)
    ");
    $stmt->execute([
        $driver_id, $month, $year, $from, $to, $agg['trip_count'], $agg['total_overnight'],
        $agg['total_fee'], $agg['total_fine'], $agg['total_collected'], $agg['total_refund'],
        $prev_balance, $total_salary, $remaining
    ]);
}
