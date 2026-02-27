import cv2
import pytesseract
import sys
import json
import re
import sys
sys.stdout.reconfigure(encoding='utf-8')

pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

if len(sys.argv) < 2:
    print("[]")
    sys.exit()

image_path = sys.argv[1]

img = cv2.imread(image_path)
if img is None:
    print("[]")
    sys.exit()

# ===== Preprocess =====
img = cv2.resize(img, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

# tăng tương phản nhẹ + nhị phân
gray = cv2.convertScaleAbs(gray, alpha=1.6, beta=-60)
th = cv2.adaptiveThreshold(
    gray, 255,
    cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
    cv2.THRESH_BINARY,
    31, 7
)

H, W = th.shape

# ===== Crop vùng bảng (tùy ảnh bạn, chỉnh 0.20/0.80 nếu cần) =====
table = th[int(H*0.20):int(H*0.82), int(W*0.05):int(W*0.95)]
tH, tW = table.shape

# Debug vùng bảng
cv2.imwrite("/uploads/debug_table.png", table)

# ===== OCR to data (lấy tọa độ từng token) =====
# Whitelist ở đây: Tesseract vẫn đọc tiếng Việt nhưng token số/điểm sẽ sạch hơn
config = "--oem 3 --psm 6"
data = pytesseract.image_to_data(table, lang="vie", config=config, output_type=pytesseract.Output.DICT)

# ===== Chia cột theo tỉ lệ X trong table =====
# Bạn có thể tinh chỉnh các mốc này nếu cột lệch.
# [0] MãHP | [1] TênHP | [2] Nhóm | [3] Tín chỉ | [4] Điểm chữ | [5] Điểm số
x_ma_max   = 0.16
x_ten_max  = 0.66
x_nhom_max = 0.74
x_tc_max   = 0.80
x_dchu_max = 0.90
# còn lại là điểm số

def which_col(x_center_ratio: float) -> int:
    if x_center_ratio <= x_ma_max:
        return 0
    if x_center_ratio <= x_ten_max:
        return 1
    if x_center_ratio <= x_nhom_max:
        return 2
    if x_center_ratio <= x_tc_max:
        return 3
    if x_center_ratio <= x_dchu_max:
        return 4
    return 5

# ===== Gom token theo hàng dựa trên y (cluster đơn giản) =====
tokens = []
n = len(data["text"])
for i in range(n):
    txt = (data["text"][i] or "").strip()
    conf = float(data["conf"][i]) if str(data["conf"][i]).strip() != "-1" else -1
    if not txt or conf < 30:   # lọc rác OCR
        continue

    x = data["left"][i]
    y = data["top"][i]
    w = data["width"][i]
    h = data["height"][i]

    x_center = x + w/2
    y_center = y + h/2
    x_ratio = x_center / tW

    col = which_col(x_ratio)

    tokens.append({
        "text": txt,
        "x": x,
        "y": y,
        "yc": y_center,
        "col": col
    })

# sort theo Y rồi X
tokens.sort(key=lambda t: (t["yc"], t["x"]))

# cluster rows by y distance
rows_tokens = []
row_tol = 18  # độ nhạy tách dòng, ảnh bạn resize*3 nên 15-25 ok

for tok in tokens:
    placed = False
    for r in rows_tokens:
        if abs(tok["yc"] - r["yc_mean"]) <= row_tol:
            r["items"].append(tok)
            # update mean
            r["yc_mean"] = (r["yc_mean"] * r["count"] + tok["yc"]) / (r["count"] + 1)
            r["count"] += 1
            placed = True
            break
    if not placed:
        rows_tokens.append({"yc_mean": tok["yc"], "count": 1, "items": [tok]})

# sort row clusters by y
rows_tokens.sort(key=lambda r: r["yc_mean"])
import cv2
import pytesseract
import sys
import json
import re

pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

if len(sys.argv) < 2:
    print("[]")
    sys.exit()

image_path = sys.argv[1]

img = cv2.imread(image_path)
if img is None:
    print("[]")
    sys.exit()

# ===== Preprocess =====
img = cv2.resize(img, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

# tăng tương phản nhẹ + nhị phân
gray = cv2.convertScaleAbs(gray, alpha=1.6, beta=-60)
th = cv2.adaptiveThreshold(
    gray, 255,
    cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
    cv2.THRESH_BINARY,
    31, 7
)

H, W = th.shape

# ===== Crop vùng bảng (tùy ảnh bạn, chỉnh 0.20/0.80 nếu cần) =====
table = th[int(H*0.20):int(H*0.82), int(W*0.05):int(W*0.95)]
tH, tW = table.shape

# Debug vùng bảng
cv2.imwrite("/uploads/debug_table.png", table)

# ===== OCR to data (lấy tọa độ từng token) =====
# Whitelist ở đây: Tesseract vẫn đọc tiếng Việt nhưng token số/điểm sẽ sạch hơn
config = "--oem 3 --psm 6"
data = pytesseract.image_to_data(table, lang="vie", config=config, output_type=pytesseract.Output.DICT)

# ===== Chia cột theo tỉ lệ X trong table =====
# Bạn có thể tinh chỉnh các mốc này nếu cột lệch.
# [0] MãHP | [1] TênHP | [2] Nhóm | [3] Tín chỉ | [4] Điểm chữ | [5] Điểm số
# Thử bộ thông số mới này xem sao:
x_ma_max   = 0.13   # Giảm xuống để không liếm sang tên HP
x_ten_max  = 0.61   # Giảm xuống để không dính cột Nhóm
x_nhom_max = 0.68   
x_tc_max   = 0.76
x_dchu_max = 0.86
x_dchu_max = 0.90
# còn lại là điểm số

def which_col(x_center_ratio: float) -> int:
    if x_center_ratio <= x_ma_max:
        return 0
    if x_center_ratio <= x_ten_max:
        return 1
    if x_center_ratio <= x_nhom_max:
        return 2
    if x_center_ratio <= x_tc_max:
        return 3
    if x_center_ratio <= x_dchu_max:
        return 4
    return 5

# ===== Gom token theo hàng dựa trên y (cluster đơn giản) =====
tokens = []
n = len(data["text"])
for i in range(n):
    txt = (data["text"][i] or "").strip()
    conf = float(data["conf"][i]) if str(data["conf"][i]).strip() != "-1" else -1
    if not txt or conf < 30:   # lọc rác OCR
        continue

    x = data["left"][i]
    y = data["top"][i]
    w = data["width"][i]
    h = data["height"][i]

    x_center = x + w/2
    y_center = y + h/2
    x_ratio = x_center / tW

    col = which_col(x_ratio)

    tokens.append({
        "text": txt,
        "x": x,
        "y": y,
        "yc": y_center,
        "col": col
    })

# sort theo Y rồi X
tokens.sort(key=lambda t: (t["yc"], t["x"]))

# cluster rows by y distance
rows_tokens = []
row_tol = 18  # độ nhạy tách dòng, ảnh bạn resize*3 nên 15-25 ok

for tok in tokens:
    placed = False
    for r in rows_tokens:
        if abs(tok["yc"] - r["yc_mean"]) <= row_tol:
            r["items"].append(tok)
            # update mean
            r["yc_mean"] = (r["yc_mean"] * r["count"] + tok["yc"]) / (r["count"] + 1)
            r["count"] += 1
            placed = True
            break
    if not placed:
        rows_tokens.append({"yc_mean": tok["yc"], "count": 1, "items": [tok]})

# sort row clusters by y
rows_tokens.sort(key=lambda r: r["yc_mean"])

# ===== Parse từng row cluster thành object =====
map_diem4 = {"A":4.0,"A+":4.0,"B+":3.5,"B":3.0,"C+":2.5,"C":2.0,"D+":1.5,"D":1.0,"F":0.0}

results = []
for r in rows_tokens:
    # gom text theo cột
    cols = {0: [], 1: [], 2: [], 3: [], 4: [], 5: []}
    for it in sorted(r["items"], key=lambda t: (t["col"], t["x"])):
        cols[it["col"]].append(it["text"])

    ma_raw  = " ".join(cols[0]).strip()
    ten_raw = " ".join(cols[1]).strip()
    tc_raw  = " ".join(cols[3]).strip()
    dchu_raw = " ".join(cols[4]).strip()
    d10_raw  = " ".join(cols[5]).strip()

    # bỏ các dòng header (không có CTxxx)
    ma_match = re.search(r"\bCT\d{3}\b", ma_raw)
    if not ma_match:
        continue

    ma_hp = ma_match.group()

    # Tên học phần: dọn rác nếu bị dính ký tự lạ
    ten_hp = re.sub(r"\s+", " ", ten_raw).strip()

    # Tín chỉ: ưu tiên số trong cột tín chỉ, fallback = 3
    tc_match = re.search(r"\b\d+\b", tc_raw)
    so_tc = int(tc_match.group()) if tc_match else 3

    # Điểm chữ: bắt theo regex (ưu tiên token đúng)
    dchu_match = re.search(r"\b(A\+|A|B\+|B|C\+|C|D\+|D|F)\b", dchu_raw.replace(" ", ""))
    diem_chu = dchu_match.group(1) if dchu_match else None

    # Điểm số: bắt dạng 9.0 / 8.8 ...
    d10_match = re.search(r"\b\d{1,2}\.\d\b", d10_raw.replace(",", "."))
    diem_10 = float(d10_match.group()) if d10_match else None

    results.append({
        "ma_hp": ma_hp,
        "ten_hp": ten_hp,
        "so_tc": so_tc,
        "diem_4": map_diem4.get(diem_chu) if diem_chu else None,
        "diem_10": diem_10
    })

print(json.dumps(results, ensure_ascii=False))