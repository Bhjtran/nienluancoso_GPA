<?php include '../layouts/header.php'; ?>
<div class="container mt-5">
    <h3>Xin chào, <?= $_SESSION['ho_ten'] ?></h3>
    <a href="?page=du_toan" class="btn btn-success mt-3">Phân tích dự toán GPA</a>
    <a href="?page=thongke" class="btn btn-info mt-3">Thống kê điểm</a>
</div>
<?php include '../layouts/footer.php'; ?>