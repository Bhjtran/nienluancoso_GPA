<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$mssv = $_SESSION['mssv'] ?? 'B2306595';

$self = basename($_SERVER['PHP_SELF']);

switch ($self) {
    case 'index.php':
        $current_page = 'trang_chu';
        break;
    case 'thongke.php':
        $current_page = 'thong_ke';
        break;
    case 'du_toan.php':
        $current_page = 'du_toan';
        break;
    default:
        $current_page = '';
}

// 1. TÍN CHỈ TÍCH LŨY
$sql_tc = "
SELECT SUM(m.so_tc) AS tong_tc
FROM v_bang_diem_max v
JOIN mon_hoc m ON v.ma_hp = m.ma_hp
WHERE v.mssv = '$mssv'
AND v.diem_4_max >= 1.0
AND m.loai_hp != 'DieuKien'
";

$res_tc = mysqli_query($conn, $sql_tc);
$row_tc = mysqli_fetch_assoc($res_tc);

$tin_chi = (int)($row_tc['tong_tc'] ?? 0);
$max_tc = 140; 
$percent = ($tin_chi / $max_tc) * 100;

// 2. BIỂU ĐỒ CỘT
$sql_diem = "
SELECT 
    CASE
        WHEN v.diem_4_max = 4.0 THEN 'A'
        WHEN v.diem_4_max = 3.5 THEN 'B+'
        WHEN v.diem_4_max = 3.0 THEN 'B'
        WHEN v.diem_4_max = 2.5 THEN 'C+'
        WHEN v.diem_4_max = 2.0 THEN 'C'
        WHEN v.diem_4_max = 1.5 THEN 'D+'
        WHEN v.diem_4_max = 1.0 THEN 'D'
        ELSE 'F'
    END AS diem_chu,
    SUM(m.so_tc) AS tong_tc
FROM v_bang_diem_max v
JOIN mon_hoc m ON v.ma_hp = m.ma_hp
WHERE v.mssv = '$mssv'
AND m.loai_hp != 'DieuKien'
GROUP BY diem_chu
";

$res_diem = mysqli_query($conn, $sql_diem);
$grades = ['A','B+','B','C+','C','D+','D','F'];
$data_map = array_fill_keys($grades, 0);

while ($row = mysqli_fetch_assoc($res_diem)) {
    $data_map[$row['diem_chu']] = (int)$row['tong_tc'];
}

$labels_bar = array_keys($data_map);
$data_bar = array_values($data_map);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê kết quả học tập</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', Arial, sans-serif; }
        .custom-nav-item {
            color: #6c757d !important;
            font-weight: 500;
            padding: 8px 20px !important;
            transition: all 0.3s ease;
            border-radius: 50px;
            text-decoration: none;
        }
        .custom-nav-item:hover { color: #4e73df !important; background-color: #f8f9fa; }
        .custom-nav-item.active { background-color: #4e73df !important; color: white !important; }
        
        .stat-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            background: white;
        }
        .progress { height: 12px; border-radius: 10px; background-color: #e9ecef; }
        .chart-container {
            background: white;
            padding: 25px;
            border-radius: 20px;
        }

        /* --- PHẦN SỬA LỖI HIỂN THỊ TIẾNG VIỆT --- */
       #reportContent { 
    padding: 50px !important; 
    background-color: white !important;
    /* Dùng font-family an toàn nhất cho tiếng Việt */
    font-family: Arial, "Helvetica Neue", Helvetica, sans-serif !important;
}

#reportContent h2, #reportContent h5 {
    line-height: 1.6;
    margin-bottom: 20px;
    font-weight: bold;
    /* Khử răng cưa giúp font sắc nét hơn trên canvas */
    -webkit-font-smoothing: antialiased;
}
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top bg-white mb-4 shadow-sm py-2">
    <div class="container d-flex justify-content-between align-items-center">
        
        <div class="d-flex align-items-center">
            <a class="navbar-brand d-flex align-items-center me-4" href="index.php">
                <h4 class="text-primary fw-bold m-0">
                    <i class="fas fa-graduation-cap me-2"></i>CTU SCORE
                </h4>
            </a>

            <div class="d-flex align-items-center">
                <a href="index.php" class="nav-link px-3 custom-nav-item <?php echo ($current_page == 'trang_chu') ? 'active' : ''; ?>">
                    Trang chủ
                </a>
                
                <a href="thongke.php" class="nav-link px-3 custom-nav-item <?php echo ($current_page == 'thong_ke') ? 'active' : ''; ?>">
                    Thống kê
                </a>

                <a href="du_toan.php" class="nav-link px-3 custom-nav-item <?php echo ($current_page == 'du_toan') ? 'active' : ''; ?>">
                    Dự đoán điểm
                </a>
            </div>
        </div>

        <div class="d-flex align-items-center">
            <div class="vr mx-3 d-none d-md-block" style="height: 24px; opacity: 0.15;"></div>

            <span class="fw-bold me-3 text-dark d-none d-md-inline-block" style="font-size: 0.9rem;">
                <i class="fas fa-user-circle me-1 text-secondary"></i> <?php echo $mssv; ?>
            </span>

            <a href="login.php" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" style="font-size: 0.8rem;">
                Thoát
            </a>
        </div>
    </div>
</nav>

<div class="container py-4">
   

    <div id="reportContent">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-uppercase" style="color: #333; letter-spacing: 1px;">Báo cáo thống kê kết quả học tập</h2>
             <div class="d-flex justify-content-end mb-3">
        <button id="btnExport" onclick="exportToPDF()" class="btn btn-danger rounded-pill px-4 shadow-sm">
            <i class="fas fa-file-pdf me-2"></i>Xuất PDF
        </button>
    </div>
           
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card stat-card h-100 p-4 border shadow-none">
                    <h6 class="text-muted text-uppercase fw-bold mb-3 small">Tiến độ tích lũy</h6>
                    <h2 class="fw-bold text-primary mb-2"><?= $tin_chi ?> <small class="text-muted fs-6">/ <?= $max_tc ?> TC</small></h2>
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                             role="progressbar" style="width: <?= $percent ?>%"></div>
                    </div>
                    <p class="text-muted small mb-0">Hoàn thành <b><?= round($percent, 1) ?>%</b> chương trình đào tạo.</p>
                </div>
            </div>

            <div class="col-md-8">
                <div class="chart-container border shadow-none">
                    <h5 class="fw-bold mb-4 text-center"><i class="fas fa-chart-bar text-warning me-2"></i>Phân bổ tín chỉ theo điểm chữ</h5>
                    <div style="height: 320px; position: relative;">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('barChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels_bar); ?>,
        datasets: [{
            label: 'Số tín chỉ',
            data: <?= json_encode($data_bar); ?>,
            backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#fd7e14', '#e74a3b', '#858796', '#5a5c69'],
            borderRadius: 5
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: false, 
        plugins: { legend: { display: false } },
        scales: {
            y: { 
                beginAtZero: true, 
                ticks: { 
                    stepSize: 2,
                    color: '#000',
                    font: { size: 13, weight: 'bold' }
                } 
            },
            x: { 
                grid: { display: false },
                ticks: {
                    color: '#000',
                    font: { size: 13, weight: 'bold' }
                }
            }
        }
    }
});
async function exportToPDF() {
    const btn = document.getElementById('btnExport');
    const { jsPDF } = window.jspdf;
    const element = document.getElementById('reportContent');

    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang xử lý...';
    btn.disabled = true;

    try {
        const canvas = await html2canvas(element, {
            scale: 2, 
            useCORS: true,
            backgroundColor: "#ffffff",
            // Quan trọng: Giúp render font tiếng Việt chuẩn hơn
            letterRendering: true,
            // Đảm bảo không bị ảnh hưởng bởi thanh cuộn
            windowWidth: element.scrollWidth,
            windowHeight: element.scrollHeight,
            onclone: (clonedDoc) => {
                // Ép font lần cuối trong bản clone để chụp cho chuẩn
                clonedDoc.getElementById('reportContent').style.fontFamily = 'Arial';
            }
        });

        const imgData = canvas.toDataURL('image/png', 1.0);
        const pdf = new jsPDF({
            orientation: 'landscape',
            unit: 'mm',
            format: 'a4'
        });

        const pdfWidth = pdf.internal.pageSize.getWidth();
        const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

        pdf.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight);
        pdf.save("Thongke_Diem_<?= $mssv ?>.pdf");

    } catch (error) {
        console.error(error);
        alert("Có lỗi khi tạo PDF!");
    } finally {
        btn.innerHTML = '<i class="fas fa-file-pdf me-2"></i>Xuất PDF';
        btn.disabled = false;
    }
}
</script>

</body>
</html>