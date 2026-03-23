<?php include '../layouts/header.php'; ?>

<div class="container mt-5">
    <h3>OCR - Nhận dạng văn bản từ ảnh</h3>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Chọn ảnh:</label>
            <input type="file" name="ocr_file" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Chạy OCR</button>
    </form>

    <?php if($result !== null): ?>
        <div class="card mt-3 p-3">
            <h5>Kết quả OCR:</h5>
            <pre><?= htmlspecialchars($result) ?></pre>
        </div>
    <?php endif; ?>
</div>

<?php include '../layouts/footer.php'; ?>