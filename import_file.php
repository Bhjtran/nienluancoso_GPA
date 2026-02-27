<?php
if (!isset($_FILES['bangdiem'])) {
    die("Không có file upload");
}

$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$tmp = $_FILES['bangdiem']['tmp_name'];
$name = time() . "_" . basename($_FILES['bangdiem']['name']);
$path = $uploadDir . $name;

move_uploaded_file($tmp, $path);

// ===== GỌI PYTHON =====
$python = "python";

$cmd = '"' . $python . '" "' . __DIR__ . '\\python\\ocr_engine.py" ' . escapeshellarg($path) . " 2>&1";

echo "<pre>";
echo "CMD:\n" . $cmd . "\n\n";

$output = shell_exec($cmd);

echo "OUTPUT:\n";
var_dump($output);
exit;