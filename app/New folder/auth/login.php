<?php include '../layouts/header.php'; ?>
<div class="container mt-5">
    <h3>Đăng nhập hệ thống</h3>
    <form method="POST">
        <div class="mb-3">
            <label>MSSV</label>
            <input type="text" name="mssv" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="btn_login" class="btn btn-primary">Đăng nhập</button>
    </form>
</div>
<?php include '../layouts/footer.php'; ?>