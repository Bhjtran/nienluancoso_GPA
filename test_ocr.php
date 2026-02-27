<?php
require __DIR__ . '/vendor/autoload.php';
use thiagoalessio\TesseractOCR\TesseractOCR;

/**
 * Ảnh -> upscale + grayscale + contrast + threshold (binarize)
 * Trả về path ảnh tạm đã xử lý để OCR ổn hơn.
 */
function preprocess_for_ocr(string $srcPath): string {
    if (!extension_loaded('gd')) {
        // Nếu không có GD thì cứ trả về ảnh gốc (nhưng OCR sẽ kém hơn)
        return $srcPath;
    }

    $img = @imagecreatefrompng($srcPath);
    if (!$img) return $srcPath;

    // upscale 2.5x - 3x giúp OCR ra space tốt hơn
    $w = imagesx($img); $h = imagesy($img);
    $scale = 3;
    $big = imagescale($img, $w * $scale, $h * $scale, IMG_BICUBIC);
    imagedestroy($img);

    // grayscale
    imagefilter($big, IMG_FILTER_GRAYSCALE);

    // tăng tương phản (giá trị âm => tăng contrast)
    imagefilter($big, IMG_FILTER_CONTRAST, -40);

    // threshold (binarize)
    // ngưỡng bạn có thể thử 160-210 tùy ảnh
    $threshold = 190;
    $bw_white = imagecolorallocate($big, 255, 255, 255);
    $bw_black = imagecolorallocate($big, 0, 0, 0);

    $bw = imagecreatetruecolor(imagesx($big), imagesy($big));
    imagefill($bw, 0, 0, $bw_white);

    for ($y = 0; $y < imagesy($big); $y++) {
        for ($x = 0; $x < imagesx($big); $x++) {
            $rgb = imagecolorat($big, $x, $y);
            $gray = $rgb & 0xFF; // grayscale => R=G=B
            imagesetpixel($bw, $x, $y, ($gray < $threshold) ? $bw_black : $bw_white);
        }
    }
    imagedestroy($big);

    $tmp = sys_get_temp_dir() . '/ocr_' . uniqid() . '.png';
    imagepng($bw, $tmp, 0);
    imagedestroy($bw);

    return $tmp;
}

/**
 * Chuẩn hóa line: tạo space giữa chữ-số để tránh "CT182Ngônngữ...053A9.0"
 */
function normalize_line(string $line): string {
    $line = trim($line);
    if ($line === '') return '';

    // bỏ ký tự rác OCR hay chèn
    $line = str_replace(["\t", "|", "—", "–", "•"], " ", $line);

    // chuẩn hóa dấu phẩy -> dấu chấm (điểm)
    $line = str_replace(",", ".", $line);

    // thêm space giữa chữ và số (unicode)
    $line = preg_replace('/([A-Za-zÀ-ỹĐđ])(\d)/u', '$1 $2', $line);
    $line = preg_replace('/(\d)([A-Za-zÀ-ỹĐđ])/u', '$1 $2', $line);

    // gom nhiều khoảng trắng
    $line = preg_replace('/\s+/u', ' ', $line);

    // fix case OCR dính "053" => "05 3" (nhóm + tín chỉ)
    // chỉ áp dụng nếu token toàn số, dài 3, và chữ số cuối 0-9
    $tokens = explode(' ', $line);
    $fixed = [];
    foreach ($tokens as $t) {
        if (preg_match('/^\d{3}$/', $t)) {
            $fixed[] = substr($t, 0, 2);
            $fixed[] = substr($t, 2, 1);
        } else {
            $fixed[] = $t;
        }
    }

    return trim(implode(' ', $fixed));
}

/**
 * Parse 1 dòng thành row chuẩn:
 * ma_hp, ten_hp, nhom, so_tc, diem_chu, diem_so
 */
function parse_row_from_line(string $line): ?array {

    // Bắt pattern chính xác theo bảng của bạn
    if (!preg_match(
        '/\b(CT|TC|XH|QP)\d{3}\b/u',
        $line,
        $maMatch
    )) return null;

    $ma = $maMatch[0];

    // Nhóm (2 chữ số)
    if (!preg_match('/\b\d{2}\b/', $line, $nhomMatch))
        return null;
    $nhom = $nhomMatch[0];

    // Tín chỉ (thường là 3)
    if (!preg_match('/\b3\b/', $line, $tcMatch))
        return null;
    $so_tc = $tcMatch[0];

    // Điểm chữ
    if (!preg_match('/\b(A\+|B\+|C\+|D\+|A|B|C|D|F)\b/u', $line, $gradeMatch))
        return null;
    $diemChu = $gradeMatch[0];

    // Điểm số (80, 88, 90 hoặc 9.0)
    if (!preg_match('/\b\d{2,3}(\.\d)?\b/', $line, $scoreMatch))
        return null;

    $rawScore = $scoreMatch[0];

    // Nếu là 90, 88, 80 => chia 10
    if ((int)$rawScore > 10) {
        $diemSo = number_format(((int)$rawScore) / 10, 1);
    } else {
        $diemSo = $rawScore;
    }

    // Tên học phần = phần giữa mã và nhóm
    $pattern = '/'.$ma.'\s+(.*?)\s+'.$nhom.'/u';
    if (preg_match($pattern, $line, $tenMatch)) {
        $ten = trim($tenMatch[1]);
    } else {
        $ten = '';
    }

    return [
        'ma_hp' => $ma,
        'ten_hp' => $ten,
        'nhom' => $nhom,
        'so_tc' => $so_tc,
        'diem_chu' => $diemChu,
        'diem_so' => $diemSo
    ];
}
function parse_blocks(string $text): array {

    $rows = [];

    preg_match_all('/CT\d{3}|[ABCD][\+]?|F|\d{2,3}(\.\d+)?/i', $text, $matches);
    $tokens = $matches[0];

    for ($i = 0; $i < count($tokens); $i++) {

        $token = strtoupper($tokens[$i]);

        if (preg_match('/^CT\d{3}$/', $token)) {

            $ma = $token;
            $nhom = '';
            $so_tc = 3;
            $diemChu = '';
            $diemSo = '';

            for ($j = $i + 1; $j < $i + 8 && $j < count($tokens); $j++) {

                $t = strtoupper($tokens[$j]);

                if ($nhom === '' && preg_match('/^\d{2}$/', $t)) {
                    $nhom = $t;
                    continue;
                }

                if ($t == '3') {
                    $so_tc = 3;
                    continue;
                }

                if (preg_match('/^[ABCD][\+]?$/', $t) || $t == 'F') {
                    $diemChu = $t;
                    continue;
                }

                if (preg_match('/^\d{2,3}(\.\d+)?$/', $t)) {

                    $val = floatval($t);

                    if ($val > 10) {
                        $val = $val / 10;
                    }

                    $diemSo = number_format($val, 1);
                }
            }

            $rows[] = [
                'ma_hp' => $ma,
                'nhom' => $nhom,
                'so_tc' => $so_tc,
                'diem_chu' => $diemChu,
                'diem_so' => $diemSo
            ];
        }
    }

    return $rows;
}
/** OCR + parse toàn ảnh */
function ocr_parse_image(string $imagePath): array {

    $text = (new TesseractOCR($imagePath))
        ->executable('C:\Program Files\Tesseract-OCR\tesseract.exe')
        ->lang('vie+eng')
        ->psm(4)
        ->oem(1)
        ->config('preserve_interword_spaces', 1)
        ->run();

    echo "<pre>RAW OCR:\n$text\n====================\n</pre>";

    return parse_blocks($text);
}

// ================== DEMO chạy thử ==================
$image = __DIR__ . '/Capture.PNG'; // hoặc hinh.PNG
$rows = ocr_parse_image($image);

header('Content-Type: text/plain; charset=utf-8');
print_r($rows);