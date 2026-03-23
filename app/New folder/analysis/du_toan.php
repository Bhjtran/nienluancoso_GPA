<?php include '../layouts/header.php'; ?>
<!-- TOÀN BỘ CODE HTML du_toan.php bạn đã viết -->
 <?php
session_start();
include 'config.php';
$mssv = $_SESSION['mssv'];


$sql = "SELECT b.id, m.so_tc, b.diem_4, m.ten_hp FROM bang_diem b
        JOIN mon_hoc m ON b.ma_hp = m.ma_hp WHERE b.mssv = '$mssv' AND m.loai_hp != 'DieuKien'";
$res = mysqli_query($conn, $sql);


$list_mon = []; $tong_tc = 0; $tong_diem = 0;
while($row = mysqli_fetch_assoc($res)) {
    $list_mon[] = $row;
    $tong_tc += (int)$row['so_tc'];
    $tong_diem += (float)$row['diem_4'] * (int)$row['so_tc'];
}
$gpa_ht_raw = ($tong_tc > 0) ? ($tong_diem / $tong_tc) : 0;
$gpa_ht = number_format($gpa_ht_raw, 2);

?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dự toán chiến thuật</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --ctu-blue: #0d6efd; --ctu-dark: #1e293b; --bg: #f8fafc; }
        body { background-color: var(--bg); font-family: 'Inter', sans-serif; color: #334155; }
       
        .navbar-custom { background: white; border-bottom: 1px solid #e2e8f0; padding: 12px 0; }
        .btn-back { color: #64748b; text-decoration: none; font-weight: 500; font-size: 0.85rem; padding: 8px 12px; border-radius: 8px; }


        .card-custom { border: none; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: #fff; margin-bottom: 24px; }
        .section-title { font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 15px; display: block; letter-spacing: 0.05em; }
       
        .sync-height { height: 48px !important; }
        .form-control-lg { border-radius: 10px; font-size: 0.95rem; border: 2px solid #e2e8f0; font-weight: 600; }
       
        .input-wrapper { position: relative; width: 100%; }
        .clear-btn { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #cbd5e1; cursor: pointer; display: none; background: white; z-index: 5; }


        .btn-analyze {
            background: #1e293b; border: none; color: white; padding: 15px; border-radius: 12px;
            font-weight: 700; width: 100%; transition: 0.2s; letter-spacing: 0.5px;
        }
        .btn-analyze:hover { background: #8f9aa8; }


        .rank-value { background: #f1f5f9; font-weight: 700; padding: 4px 0; border-radius: 6px; width: 110px; text-align: center; font-size: 0.85rem; }
        .gpa-sidebar { background: var(--ctu-blue); color: white; border-radius: 16px; padding: 25px; text-align: center; }
       
        .tag-mon {
            background: #f8fafc; border: 1px solid #e2e8f0; padding: 8px 15px; border-radius: 8px;
            font-size: 0.85rem; display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; width: 100%;
        }


        /* Style cho dòng thông báo mới */
        .advice-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 10px;
            padding: 12px 16px;
            margin-top: 15px;
            display: flex;
            align-items: center;
            color: #166534;
            font-size: 0.9rem;
        }
    </style>
</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<body>


<nav class="navbar-custom mb-4">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
            <i class="fas fa-chart-line fa-lg text-primary me-2"></i>
            <h5 class="mb-0 fw-bold" style="color: var(--ctu-dark);">PHÂN TÍCH CHIẾN THUẬT</h5>
        </div>
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left me-2"></i>Trở về Bảng điểm</a>
    </div>
</nav>


<div class="container">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card-custom p-4">
                <span class="section-title">Bước 1: Thiết lập mục tiêu ĐTBCTL</span>
                <div class="row g-2">
                    <div class="col-md-5">
                        <div class="input-wrapper">
                            <input type="number" id="target-gpa" class="form-control form-control-lg sync-height"
                                   placeholder="Nhập mục tiêu (2.0 - 4.0)" step="0.01" oninput="validate(this)">
                            <i class="fas fa-times-circle clear-btn" id="btn-clear" onclick="clearGPA()"></i>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="btn-group w-100">
                            <button class="btn btn-outline-secondary sync-height fw-medium" style="font-size:0.85rem" onclick="setGPA(3.60)">Xuất sắc</button>
                            <button class="btn btn-outline-secondary sync-height fw-medium" style="font-size:0.85rem" onclick="setGPA(3.20)">Giỏi</button>
                            <button class="btn btn-outline-secondary sync-height fw-medium" style="font-size:0.85rem" onclick="setGPA(2.50)">Khá</button>
                            <button class="btn btn-outline-secondary sync-height fw-medium" style="font-size:0.85rem" onclick="setGPA(2.00)">Trung bình</button>
                        </div>
                    </div>
                </div>
                <div id="msg-error" class="text-danger small mt-2 fw-medium" style="display:none;">Mục tiêu từ 2.0 đến 4.0 nha!</div>
            </div>


            <div class="card-custom p-4">
                <span class="section-title">Bước 2: Cải thiện học phần cũ</span>
                <div class="row g-2">
                    <div class="col-md-7">
                        <select id="select-hp" class="form-select sync-height" style="border-radius:10px;">
                            <option value="">-- Chọn môn cần cải thiện --</option>
                            <?php foreach($list_mon as $m): ?>
                                <option value="<?= $m['id'] ?>" data-ten="<?= $m['ten_hp'] ?>" data-tc="<?= $m['so_tc'] ?>" data-goc="<?= $m['diem_4'] ?>">
                                    <?= $m['ten_hp'] ?> (<?= $m['diem_4'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="target-grade" class="form-select sync-height fw-bold" style="border-radius:10px;">
                            <option value="4.0">Lên A</option>
                            <option value="3.5">Lên B+</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary sync-height w-100" style="border-radius:10px" onclick="addSubject()"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
                <div id="selected-tags" class="mt-3"></div>
            </div>


            <button class="btn btn-analyze mb-4" onclick="runAnalysis()">
                PHÂN TÍCH LỘ TRÌNH TỐI ƯU <i class="fas fa-bolt-lightning ms-2 text-warning"></i>
            </button>
<div class="d-flex gap-2 mb-3">
    <button class="btn btn-outline-danger btn-sm" onclick="clearAnalysis()">
        <i class="fas fa-trash me-1"></i> Xóa kết quả phân tích
    </button>

    <button class="btn btn-outline-primary btn-sm" onclick="exportPDF()">
        <i class="fas fa-file-pdf me-1"></i> Xuất PDF
    </button>
</div>



           <div id="result-box" class="d-none">


            <!-- BẢNG DUY TRÌ -->
            <div class="card-custom overflow-hidden mb-4">
                <div class="p-3 fw-bold">🔵 Kịch bản DUY TRÌ (không cải thiện)</div>
                <table class="table mb-0">
                    <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mức độ</th>
                        <th class="text-center">A</th>
                        <th class="text-center">B+</th>
                        <th class="text-center">B</th>
                        <th class="text-center">C+</th>
                        <th class="text-center">C</th>
                         <th class="text-center">D+</th>
                        <th class="text-center">GPA cuối</th>                    </tr>
                    </thead>


                    <tbody id="res-keep"></tbody>
                </table>
            </div>


            <!-- BẢNG CẢI THIỆN -->
            <div class="card-custom overflow-hidden">
                <div class="p-3 fw-bold text-success">🟢 Kịch bản CẢI THIỆN</div>
                <table class="table mb-0">
                   <thead class="table-light">
                    <tr>
                        <th class="ps-4">Mức độ</th>
                        <th class="text-center">A</th>
                        <th class="text-center">B+</th>
                        <th class="text-center">B</th>
                        <th class="text-center">C+</th>
                        <th class="text-center">C</th>
                        <th class="text-center">D+</th>
                        <th class="text-center">GPA cuối</th>

                    </tr>
                    </thead>


                    <tbody id="res-improve"></tbody>
                </table>
            </div>


    <div class="p-3">
        <div id="analysis-advice" class="advice-box d-none">
            <i class="fas fa-check-circle me-2"></i>
            <span id="advice-text"></span>
        </div>
    </div>


</div>


        </div>


        <div class="col-lg-4">
            <div class="gpa-sidebar shadow-sm mb-4">
                <div class="small text-uppercase fw-bold opacity-75 mb-1">GPA Hiện tại</div>
                <div class="display-4 fw-bold mb-2"><?= $gpa_ht ?></div>
                <div class="progress mb-2" style="height: 6px; background: rgba(255,255,255,0.2);">
                    <div class="progress-bar bg-white" style="width: <?= ($tong_tc/140)*100 ?>%"></div>
                </div>
                <div class="small opacity-80"><?= $tong_tc ?> / 140 Tín chỉ</div>
            </div>


            <div class="card-custom p-4">
                <span class="section-title">Hạng tốt nghiệp</span>
                <div class="d-flex justify-content-between mb-2 small fw-medium"><span>Xuất sắc</span><span class="rank-value text-primary">3.60 - 4.00</span></div>
                <div class="d-flex justify-content-between mb-2 small fw-medium"><span>Giỏi</span><span class="rank-value text-success">3.20 - 3.59</span></div>
                <div class="d-flex justify-content-between mb-2 small fw-medium"><span>Khá</span><span class="rank-value text-warning">2.50 - 3.19</span></div>
                <div class="d-flex justify-content-between mb-2 small fw-medium"><span>Trung bình</span><span class="rank-value text-secondary">2.00 - 2.49</span></div>
            </div>
        </div>
    </div>
</div>





</body>
</html>


<?php include '../layouts/footer.php'; ?>