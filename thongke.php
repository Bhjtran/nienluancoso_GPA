<?php
include 'config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mssv = $_SESSION['mssv'] ?? 'B2306595';

//
// 🔥 1. TÍN CHỈ TÍCH LŨY
//
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

//
// 🔥 2. BIỂU ĐỒ CỘT = TÍN CHỈ THEO ĐIỂM
//
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
    <title>Thống kê</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body style="font-family: Arial; text-align:center;">

<h2>📊 THỐNG KÊ HỌC TẬP</h2>

<!-- Tín chỉ -->
<p><b><?= $tin_chi ?></b> / <?= $max_tc ?> tín chỉ (<?= round($percent) ?>%)</p>

<div style="width:300px; margin:auto; background:#ddd; border-radius:10px;">
    <div style="width: <?= $percent ?>%; background:green; color:white;">
        <?= round($percent) ?>%
    </div>
</div>

<br><br>

<!-- Biểu đồ cột -->
<h4>📊 Tín chỉ theo mức điểm</h4>

<div style="width:800px; height:400px; margin:auto;">
    <canvas id="barChart"></canvas>
</div>

<script>
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels_bar); ?>,
        datasets: [{
            label: 'Số tín chỉ',
            data: <?= json_encode($data_bar); ?>,
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

</body>
</html>