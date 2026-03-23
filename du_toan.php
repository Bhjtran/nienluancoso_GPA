<?php
session_start();
include 'config.php';
$mssv = $_SESSION['mssv'];

$current_page = 'du_toan';
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

    
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --ctu-blue: #0d6efd; --ctu-dark: #1e293b; --bg: #f8fafc; }
        body { 
    background-color: #f8f9fa; 
    font-family: 'Segoe UI', Arial, sans-serif; 
    color: #334155; 
}
       
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

    <style>
    /* Style cơ bản cho các mục menu */
    .custom-nav-item {
        color: #6c757d !important; /* Màu xám cho các trang không chọn */
        font-weight: 500;
        font-size: 0.95rem;
        padding: 8px 20px !important; /* Tạo độ rộng cho nút */
        transition: all 0.3s ease;
        border-radius: 50px; /* Bo tròn hoàn toàn giống trong ảnh */
        text-decoration: none;
    }

    /* Hiệu ứng khi di chuột vào (Hover) */
    .custom-nav-item:hover {
        color: #0d6efd !important;
        background-color: #f8f9fa;
    }

    /* TRANG ĐANG CHỌN (ACTIVE) - Giống hệt ảnh mẫu */
.custom-nav-item.active {
        background-color: #4e73df !important; /* Màu xanh đậm */
        color: white !important; /* Chữ trắng */
        border-radius: 50px;
        font-weight: bold;
    }
</style>
</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

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
                <div class="p-3 fw-bold">🔵 Kịch bản KHÔNG CẢI THIỆN</div>
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


<script>
const MSSV = "<?= $_SESSION['mssv'] ?>";
let listCT = [];
const GOC = { tc: <?= $tong_tc ?>, diem: <?= $tong_diem ?> };


// Thang điểm CTU
const GRADE_SET = [
    { label: "A",  val: 4.0 },
    { label: "B+", val: 3.5 },
    { label: "B",  val: 3.0 },
    { label: "C+", val: 2.5 },
    { label: "C",  val: 2.0 },
    { label: "D+", val: 1.5 },
    { label: "D",  val: 1.0 }
];

function generatePlansExact(TC, GPA_target, bonus = 0) {
    let sols = [];

    for (let A = 0; A <= TC; A++)
    for (let Bp = 0; Bp <= TC - A; Bp++)
    for (let B = 0; B <= TC - A - Bp; B++)
    for (let Cp = 0; Cp <= TC - A - Bp - B; Cp++)
    for (let C = 0; C <= TC - A - Bp - B - Cp; C++) {

        let Dp = TC - (A + Bp + B + Cp + C);
        if (Dp < 0) continue;

        let total =
            4*A + 3.5*Bp + 3*B +
            2.5*Cp + 2*C + 1.5*Dp;

        let GPA_final =
            (GOC.diem + total + bonus) / 140;

        let GPA_round =
            Math.round(GPA_final * 100) / 100;

        if (GPA_round === GPA_target) {
            sols.push({
                A, Bp, B, Cp, C, Dp,
                GPA_final: GPA_round,
                score:
                    50*A + 10*Bp + 3*B + 1*Cp
            });
        }
    }

    sols.sort((a, b) => a.score - b.score);
    return sols;
}






function splitSolutions(solutions, targetRem) {
    const EPS = 1e-9;
    return {
        vuaDu: solutions.find(s => s.GPA_rem + EPS >= targetRem && s.GPA_rem < targetRem + 0.02),
        anToan: solutions.find(s => s.GPA_rem >= targetRem + 0.02 && s.GPA_rem < targetRem + 0.1),
        du: solutions.find(s => s.GPA_rem >= targetRem + 0.1)
    };
}








function validate(input) {
    const btn = document.getElementById('btn-clear');
    const msg = document.getElementById('msg-error');
    btn.style.display = input.value.length > 0 ? 'block' : 'none';
    const val = parseFloat(input.value);
    if (val > 4.0 || (input.value !== "" && val < 2.0)) {
        msg.style.display = 'block';
        input.classList.add('is-invalid');
    } else {
        msg.style.display = 'none';
        input.classList.remove('is-invalid');
    }
}


function clearGPA() {
    const input = document.getElementById('target-gpa');
    input.value = ''; validate(input); input.focus();
}


function setGPA(val) {
    const input = document.getElementById('target-gpa');
    input.value = val.toFixed(2); validate(input);
}


function addSubject() {
    const s = document.getElementById('select-hp');
    const o = s.options[s.selectedIndex];
    if(!o.value || listCT.find(x => x.id == o.value)) return;
    listCT.push({ id: o.value, ten: o.dataset.ten, tc: parseInt(o.dataset.tc), goc: parseFloat(o.dataset.goc), target: parseFloat(document.getElementById('target-grade').value) });
    renderTags();
}


function renderTags() {
    document.getElementById('selected-tags').innerHTML = listCT.map(m => `
        <div class="tag-mon shadow-sm">
            <span><strong>${m.ten}</strong> (${m.tc} TC) <i class="fas fa-arrow-right mx-2 text-primary"></i> Mục tiêu: ${m.target == 4 ? 'A' : 'B+'}</span>
            <i class="fas fa-times text-danger" onclick="removeSub('${m.id}')" style="cursor:pointer; padding: 5px;"></i>
        </div>`).join('');
}


function removeSub(id) { listCT = listCT.filter(x => x.id != id); renderTags(); }



function analyzeStrategy(targetGPA, remTC, bonus = 0) {
    const plans = generatePlansExact(remTC, targetGPA, bonus);
    if (!plans.length) return null;

    return {
        pa1: plans[0],
        pa2: plans[Math.floor(plans.length / 2)],
        pa3: plans[plans.length - 1]
    };
}



function renderResult(id, k) {
    if (!k) return;

    document.getElementById(id).innerHTML = `
    <tr>
        <td class="ps-4">PA 1</td>
        <td class="text-center">${k.pa1.A}</td>
        <td class="text-center">${k.pa1.Bp}</td>
        <td class="text-center">${k.pa1.B}</td>
        <td class="text-center">${k.pa1.Cp}</td>
        <td class="text-center">${k.pa1.C}</td>
        <td class="text-center">${k.pa1.Dp}</td>
        <td class="text-center fw-bold">${k.pa1.GPA_final.toFixed(2)}</td>
    </tr>

    <tr>
        <td class="ps-4">PA 2</td>
        <td class="text-center">${k.pa2.A}</td>
        <td class="text-center">${k.pa2.Bp}</td>
        <td class="text-center">${k.pa2.B}</td>
        <td class="text-center">${k.pa2.Cp}</td>
        <td class="text-center">${k.pa2.C}</td>
        <td class="text-center">${k.pa2.Dp}</td>
        <td class="text-center fw-bold">${k.pa2.GPA_final.toFixed(2)}</td>
    </tr>

    <tr class="table-success">
        <td class="ps-4">PA 3</td>
        <td class="text-center">${k.pa3.A}</td>
        <td class="text-center">${k.pa3.Bp}</td>
        <td class="text-center">${k.pa3.B}</td>
        <td class="text-center">${k.pa3.Cp}</td>
        <td class="text-center">${k.pa3.C}</td>
        <td class="text-center">${k.pa3.Dp}</td>
        <td class="text-center fw-bold">${k.pa3.GPA_final.toFixed(2)}</td>
    </tr>
    `;
}





function runAnalysis() {
    const targetGPA = parseFloat(document.getElementById('target-gpa').value);
    if (!targetGPA || targetGPA > 4.0 || targetGPA < 2.0) {
        alert("Nhập mục tiêu đúng quy định!");
        return;
    }


    const TOTAL_TC = 140;
const remTC = TOTAL_TC - GOC.tc;

// Tổng điểm cần khi ra trường
const S_need = targetGPA * TOTAL_TC;

// Điểm còn thiếu
const S_rem = S_need - GOC.diem;

if (S_rem <= 0) {
    alert("Bạn đã đạt hoặc vượt GPA mục tiêu 🎉");
    return;
}

// GPA trung bình cần cho phần còn lại
const GPA_rem = S_rem / remTC;

// 👉 MỚI ĐÚNG
const k1 = analyzeStrategy(targetGPA, remTC, 0);


    let gain = 0;
    listCT.forEach(m => gain += (m.target - m.goc) * m.tc);


    const k2 = listCT.length > 0
    ? analyzeStrategy(targetGPA, remTC, gain)
    : null;



    // HIỆN KẾT QUẢ
  document.getElementById('result-box').classList.remove('d-none');

renderResult("res-keep", k1);
if (k2) renderResult("res-improve", k2);

saveAnalysis(targetGPA, k1, k2);


if (!k1) {
    alert("Không tìm được lộ trình phù hợp với mục tiêu này.");
    return;
}

if (k2 && !k2) {
    alert("Kịch bản cải thiện không khả thi với mục tiêu này.");
    return;
}


   
}

function saveAnalysis(targetGPA, k1, k2) {
    const data = {
        targetGPA,
        listCT,
        k1,
        k2
    };

    localStorage.setItem(
        "analysis_" + MSSV,
        JSON.stringify(data)
    );
}


function clearAnalysis() {
    if (!confirm("Xóa toàn bộ kết quả phân tích?")) return;

localStorage.removeItem("analysis_" + MSSV);

    document.getElementById('result-box').classList.add('d-none');
    document.getElementById('res-keep').innerHTML = "";
    document.getElementById('res-improve').innerHTML = "";
}

window.addEventListener("load", () => {
    if (!MSSV) return;

    const raw = localStorage.getItem("analysis_" + MSSV);
    if (!raw) return;

    const data = JSON.parse(raw);

    // Khôi phục input GPA
    document.getElementById("target-gpa").value =
        data.targetGPA.toFixed(2);

    // Khôi phục môn cải thiện
    listCT = data.listCT || [];
    renderTags();

    // Hiện box kết quả
    document.getElementById('result-box')
        .classList.remove('d-none');

    // Render bảng
    renderResult("res-keep", data.k1);
    if (data.k2) renderResult("res-improve", data.k2);
});


</script>


<script>
async function exportPDF() {
    const { jsPDF } = window.jspdf;

    const element = document.getElementById("result-box");
    if (!element) {
        alert("Không có dữ liệu để xuất PDF");
        return;
    }

    // 👉 LẤY MSSV (đổi lại nếu bạn lấy từ chỗ khác)
    const mssvInput = document.getElementById("mssv");
    const mssv = mssvInput ? mssvInput.value.trim() : "MSSV";

    const canvas = await html2canvas(element, {
        scale: 2,
        useCORS: true
    });

    const imgData = canvas.toDataURL("image/png");

    const pdf = new jsPDF("p", "mm", "a4");

    // ===== TIÊU ĐỀ =====
    pdf.setFont("helvetica", "bold");
    pdf.setFontSize(14);
    pdf.text(`KQ DU KIEN GPA - MSSV: ${mssv}`, 105, 15, { align: "center" });

    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

    // ===== ẢNH NỘI DUNG =====
    pdf.addImage(imgData, "PNG", 0, 25, pdfWidth, pdfHeight);

    // ===== TÊN FILE =====
    pdf.save(`Kết quả dự kiến GPA_${mssv}.pdf`);
}

</script>



</body>
</html>
