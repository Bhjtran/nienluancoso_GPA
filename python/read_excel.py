import pandas as pd
import sys
import json

file = sys.argv[1]

df = pd.read_excel(file)

# Chuẩn hóa tên cột (tránh lỗi viết hoa, space)
df.columns = df.columns.str.strip().str.lower()

result = []

for _, row in df.iterrows():
    try:
        ma_hp = str(row.get("ma_hp", "")).strip()
        ten_hp = str(row.get("ten_hp", "")).strip()

        # xử lý null
        so_tc = int(row["so_tc"]) if pd.notna(row.get("so_tc")) else None
        diem_4 = float(row["diem_4"]) if pd.notna(row.get("diem_4")) else None
        diem_10 = float(row["diem_10"]) if pd.notna(row.get("diem_10")) else None

        # ❗ bỏ dòng rác
        if not ma_hp or ma_hp.lower() == "nan":
            continue

        result.append({
            "ma_hp": ma_hp,
            "ten_hp": ten_hp,
            "so_tc": so_tc,
            "diem_4": diem_4,
            "diem_10": diem_10
        })

    except Exception as e:
        continue

print(json.dumps(result, ensure_ascii=False))