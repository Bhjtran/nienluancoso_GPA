
USE quanlydiem_ctu;

DROP TABLE IF EXISTS bang_diem;
DROP TABLE IF EXISTS mon_hoc;
DROP TABLE IF EXISTS sinh_vien;

-- 1. Sinh viên
CREATE TABLE sinh_vien (
    mssv VARCHAR(10) PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    mat_khau VARCHAR(50) NOT NULL
);

-- 2. Môn học
CREATE TABLE mon_hoc (
    ma_hp VARCHAR(10) PRIMARY KEY,
    ten_hp VARCHAR(100) NOT NULL,
    so_tc INT NOT NULL,
    loai_hp VARCHAR(20) NOT NULL
);

-- 3. Bảng điểm
CREATE TABLE bang_diem (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mssv VARCHAR(10),
    ma_hp VARCHAR(10),
    diem_4 FLOAT,
    diem_10 FLOAT DEFAULT NULL,
    FOREIGN KEY (mssv) REFERENCES sinh_vien(mssv) ON DELETE CASCADE,
    FOREIGN KEY (ma_hp) REFERENCES mon_hoc(ma_hp) ON DELETE CASCADE
);

-- 4. Tài khoản test
INSERT INTO sinh_vien VALUES 
('B2306595','Pham Huyen Tran','123456');

-- 5. VIEW: lấy điểm CAO NHẤT mỗi môn
CREATE OR REPLACE VIEW v_bang_diem_max AS
SELECT 
    mssv,
    ma_hp,
    MAX(diem_4)  AS diem_4_max,
    MAX(diem_10) AS diem_10_max
FROM bang_diem
GROUP BY mssv, ma_hp;


SELECT *
FROM v_bang_diem_max
WHERE mssv = 'B2306595'
AND ma_hp LIKE 'XH%';

