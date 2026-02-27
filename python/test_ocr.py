import cv2
import pytesseract
import sys

# 👇 QUAN TRỌNG: sửa đúng đường dẫn tesseract.exe
pytesseract.pytesseract.tesseract_cmd = r"C:\Program Files\Tesseract-OCR\tesseract.exe"

# 👇 sửa đúng đường dẫn ảnh của bạn
image_path = r"C:\xampp\htdocs\nienluancoso\uploads\Capture.png"

img = cv2.imread(image_path)

if img is None:
    print("❌ Không đọc được ảnh")
    sys.exit()

# Chuyển sang grayscale
gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)

# OCR
text = pytesseract.image_to_string(gray, lang="vie")

print("=== KẾT QUẢ OCR ===")
print(text)