<?php
session_start();
if (!isset($_SESSION['mssv'])) {
    header("Location: login.php");
    exit();
}

$mssv = $_SESSION['mssv'];

if (!isset($_FILES['bangdiem'])) {
    die("Không có file upload");
}

$file = $_FILES['bangdiem'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// thư mục lưu tạm
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir);

$path = $upload_dir . time() . "_" . basename($file['name']);
move_uploaded_file($file['tmp_name'], $path);

/* ====== DEMO PHÂN LOẠI FILE ====== */
$kq = [];

if (in_array($ext, ['xls','xlsx','csv'])) {
    $kq[] = "📊 File Excel/CSV – sẵn sàng đọc dữ liệu";
}
elseif (in_array($ext, ['doc','docx'])) {
    $kq[] = "📄 File Word – sẽ trích text";
}
elseif (in_array($ext, ['jpg','jpeg','png'])) {
    $kq[] = "🖼 Ảnh – sẽ OCR";
}
elseif ($ext === 'pdf') {
    $kq[] = "📕 PDF – sẽ OCR hoặc đọc text";
}
else {
    $kq[] = "❌ Định dạng không hỗ trợ";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kết quả quét</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="card p-4">
        <h5 class="fw-bold mb-3">Kết quả quét file</h5>

        <ul>
            <?php foreach($kq as $dong): ?>
                <li><?php echo $dong; ?></li>
            <?php endforeach; ?>
        </ul>

        <a href="index.php" class="btn btn-primary mt-3">
            Quay lại
        </a>
    </div>
</div>
</body>
</html>