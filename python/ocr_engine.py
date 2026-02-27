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

img = cv2.resize(img, None, fx=3, fy=3, interpolation=cv2.INTER_CUBIC)
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

h, w = gray.shape  # BẮT BUỘC PHẢI CÓ

# ===== CẮT VÙNG BẢNG =====
table = gray[int(h*0.20):int(h*0.80), int(w*0.05):int(w*0.95)]

th, tw = table.shape

# ===== CẮT CỘT ĐÚNG CÁCH =====
col_ma   = table[:, int(tw*0.00):int(tw*0.15)]
col_ten  = table[:, int(tw*0.15):int(tw*0.60)]
col_dchu = table[:, int(tw*0.75):int(tw*0.85)]
col_d10  = table[:, int(tw*0.85):int(tw*0.98)]

def ocr_column(img_col, config="--psm 6"):
    img_col = cv2.convertScaleAbs(img_col, alpha=2.0, beta=-150)

    th = cv2.adaptiveThreshold(
        img_col,
        255,
        cv2.ADAPTIVE_THRESH_GAUSSIAN_C,
        cv2.THRESH_BINARY,
        21,
        5
    )

    text = pytesseract.image_to_string(th, lang="vie", config=config)

    lines = [l.strip() for l in text.split("\n") if l.strip()]
    return lines

ma_list = ocr_column(
    col_ma,
    "--psm 6 -c tessedit_char_whitelist=CT0123456789"
)
ten_list = ocr_column(col_ten)
dchu_list = ocr_column(
    col_dchu,
    "--psm 6 -c tessedit_char_whitelist=ABCD+F"
)
d10_list = ocr_column(
    col_d10,
    "--psm 6 -c tessedit_char_whitelist=0123456789."
)
count = min(len(ma_list), len(d10_list), len(dchu_list))

map_diem4 = {
    "A":4.0,"A+":4.0,
    "B+":3.5,"B":3.0,
    "C+":2.5,"C":2.0,
    "D+":1.5,"D":1.0,
    "F":0
}

rows = []

for i in range(count):
    rows.append({
        "ma_hp": ma_list[i],
        "ten_hp": ten_list[i] if i < len(ten_list) else "",
        "so_tc": 3,
        "diem_4": map_diem4.get(dchu_list[i], None),
        "diem_10": d10_list[i]
    })

print(json.dumps(rows, ensure_ascii=False))

cv2.imwrite("debug_table.png", table)
cv2.imwrite("debug_ma.png", col_ma)
cv2.imwrite("debug_ten.png", col_ten)
cv2.imwrite("debug_dchu.png", col_dchu)
cv2.imwrite("debug_d10.png", col_d10)