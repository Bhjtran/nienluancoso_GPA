<?php
session_start();
include "config.php";

header("Content-Type: application/json");

if (!isset($_SESSION['mssv'])) {
    echo json_encode(["status" => "error", "msg" => "Chưa đăng nhập"]);
    exit;
}

$mssv = $_SESSION['mssv'];

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data || !is_array($data)) {
    echo json_encode(["status" => "error", "msg" => "Dữ liệu không hợp lệ"]);
    exit;
}

mysqli_begin_transaction($conn);

try {

    foreach ($data as $row) {

        if (empty($row['ma_hp']) || empty($row['ten_hp']) || empty($row['so_tc'])) {
            continue;
        }

        $ma  = strtoupper(trim($row['ma_hp']));
        $ten = trim($row['ten_hp']);
        $tc  = (int)$row['so_tc'];

        $d4  = isset($row['diem_4']) ? floatval($row['diem_4']) : null;
        $d10 = isset($row['diem_10']) ? floatval($row['diem_10']) : null;

        // validate điểm
        if ($d4 !== null && ($d4 < 0 || $d4 > 4)) $d4 = null;
        if ($d10 !== null && ($d10 < 0 || $d10 > 10)) $d10 = null;

        // xác định loại
        $loai = (
            str_starts_with($ma, 'XH') ||
            str_starts_with($ma, 'QP') ||
            str_starts_with($ma, 'TC')
        ) ? 'DieuKien' : 'ChuyenNganh';

        // dùng prepared statement
        $stmt1 = $conn->prepare("
            INSERT IGNORE INTO mon_hoc(ma_hp, ten_hp, so_tc, loai_hp)
            VALUES (?, ?, ?, ?)
        ");
        $stmt1->bind_param("ssis", $ma, $ten, $tc, $loai);
        $stmt1->execute();

        $stmt2 = $conn->prepare("
            INSERT INTO bang_diem(mssv, ma_hp, diem_4, diem_10)
            VALUES (?, ?, ?, ?)
        ");
        $stmt2->bind_param("ssdd", $mssv, $ma, $d4, $d10);
        $stmt2->execute();
    }

    mysqli_commit($conn);
    echo json_encode(["status" => "ok"]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(["status" => "error", "msg" => "Lỗi hệ thống"]);
}