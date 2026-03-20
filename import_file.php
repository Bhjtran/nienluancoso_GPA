<?php
if (!isset($_FILES['bangdiem'])) {
    echo json_encode([]);
    exit;
}

$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$tmp = $_FILES['bangdiem']['tmp_name'];
$name = time() . "_" . basename($_FILES['bangdiem']['name']);
$path = $uploadDir . $name;

move_uploaded_file($tmp, $path);

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

if (in_array($ext, ['xls', 'xlsx'])) {
    // xử lý Excel
$cmd = '"python" "' . __DIR__ . '/python/read_excel.py" ' . escapeshellarg($path) . " 2>&1";
} else {
    // xử lý OCR ảnh/PDF
$cmd = '"python" "' . __DIR__ . '/python/ocr_engine.py" ' . escapeshellarg($path) . " 2>&1";
}

$output = shell_exec($cmd);

header('Content-Type: application/json');
echo $output;
exit;