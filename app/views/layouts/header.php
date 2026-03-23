<?php
session_start();

// 1. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['mssv'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
$mssv = $_SESSION['mssv'];

// 2. CÁC HÀM HỖ TRỢ (Helper Functions)
function tinhGPAChuan($mssv) {
    global $conn;
    $sql = "SELECT m.so_tc, v.diem_4_max
            FROM v_bang_diem_max v
            JOIN mon_hoc m ON v.ma_hp = m.ma_hp
            WHERE v.mssv = '$mssv' AND v.diem_4_max >= 0.0
            AND m.loai_hp != 'DieuKien' AND v.ma_hp NOT LIKE 'QP%'";
    $res = mysqli_query($conn, $sql);
    $tong_diem = 0; $tong_tc = 0;
    while($row = mysqli_fetch_assoc($res)) {
        $tong_diem += $row['diem_4_max'] * $row['so_tc'];
        $tong_tc += $row['so_tc'];
    }
    return ($tong_tc > 0) ? round($tong_diem / $tong_tc, 2) : 0;
}

function convertDiemSoSangChu($diem4) {
    if ($diem4 >= 4.0) return "A";
    if ($diem4 >= 3.5) return "B+";
    if ($diem4 >= 3.0) return "B";
    if ($diem4 >= 2.5) return "C+";
    if ($diem4 >= 2.0) return "C";
    if ($diem4 >= 1.5) return "D+";
    if ($diem4 >= 1.0) return "D";
    return "F";
}

// 3. XÁC ĐỊNH TRANG HIỆN TẠI ĐỂ ACTIVE MENU
$self = basename($_SERVER['PHP_SELF']);
$current_page = '';
if ($self == 'index.php') $current_page = 'trang_chu';
elseif ($self == 'thongke.php') $current_page = 'thong_ke';
elseif ($self == 'du_toan.php') $current_page = 'du_toan';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CTU SCORE - HỆ THỐNG QUẢN LÝ ĐIỂM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .navbar { background: white; border-bottom: 1px solid #eee; }
        .custom-nav-item {
            color: #6c757d !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 8px 20px !important;
            transition: all 0.3s ease;
            border-radius: 50px;
            text-decoration: none;
        }
        .custom-nav-item:hover { color: #0d6efd !important; background-color: #f8f9fa; }
        .custom-nav-item.active {
            background-color: #4e73df !important;
            color: white !important;
            font-weight: bold;
        }
        /* Style cho các card và table từ file gốc */
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .gpa-card { background: #2e59d9; color: white; border-radius: 15px; text-align: center; padding: 30px; }
        .gpa-val { font-size: 5rem; font-weight: 800; line-height: 1; }
        @keyframes flashRow {
            0% { background-color: #fff3cd; }
            100% { background-color: transparent; }
        }
        .row-new > td { animation: flashRow 1.2s ease-in-out 3; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top bg-white mb-4 shadow-sm py-2">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <h4 class="text-primary fw-bold m-0 me-3">
                <i class="fas fa-graduation-cap me-2"></i>CTU SCORE
            </h4>
        </a>

        <div class="d-flex align-items-center ms-auto">
            <div class="d-flex align-items-center me-2 me-md-4">
                <a href="index.php" class="nav-link px-3 custom-nav-item <?php echo ($current_page == 'trang_chu') ? 'active' : ''; ?>">Trang chủ</a>
                <a href="thongke.php" class="nav-link px-3 custom-nav-item <?php echo ($current_page == 'thong_ke') ? 'active' : ''; ?>">Thống kê</a>
                <a href="du_toan.php" class="nav-link px-3 custom-nav-item <?php echo ($current_page == 'du_toan') ? 'active' : ''; ?>">Dự đoán điểm</a>
            </div>

            <div class="vr mx-2 d-none d-md-block" style="height: 24px; opacity: 0.15;"></div>

            <div class="d-flex align-items-center ms-2">
                <span class="fw-bold me-3 text-dark d-none d-md-inline-block" style="font-size: 0.9rem;">
                    <i class="fas fa-user-circle me-1 text-secondary"></i> <?php echo $mssv; ?>
                </span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-size: 0.8rem;">Thoát</a>
            </div>
        </div>
    </div>
</nav>