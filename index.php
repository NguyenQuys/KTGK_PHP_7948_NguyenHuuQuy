<?php
require_once 'auth.php';
require_once 'config.php';

// Lấy danh sách sinh viên kèm thông tin ngành học
$sql = "SELECT s.*, n.TenNganh 
        FROM SinhVien s 
        LEFT JOIN NganhHoc n ON s.MaNganh = n.MaNganh";
$stmt = $conn->query($sql);
$sinhviens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Danh sách Sinh viên</h1>
            <div>
                <span class="me-3">Xin chào, <?php echo $_SESSION['hoten']; ?></span>
                <a href="create.php" class="btn btn-primary">Thêm Sinh viên mới</a>
                <a href="hocphan.php" class="btn btn-success">Quản lý Học phần</a>
                <a href="logout.php" class="btn btn-danger">Đăng xuất</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Mã SV</th>
                                <th>Họ tên</th>
                                <th>Ngày sinh</th>
                                <th>Giới tính</th>
                                <th>Ngành học</th>
                                <th>Hình</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sinhviens as $sv): ?>
                                <tr>
                                    <td><?php echo $sv['MaSV']; ?></td>
                                    <td><?php echo $sv['HoTen']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($sv['NgaySinh'])); ?></td>
                                    <td><?php echo $sv['GioiTinh']; ?></td>
                                    <td><?php echo $sv['TenNganh']; ?></td>
                                    <td>
                                        <?php if ($sv['Hinh']): ?>
                                            <img src="<?php echo $sv['Hinh']; ?>" alt="Hình sinh viên" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="dangky_hocphan.php?masv=<?php echo $sv['MaSV']; ?>" class="btn btn-success btn-sm">Đăng ký HP</a>
                                        <a href="detail.php?masv=<?php echo $sv['MaSV']; ?>" class="btn btn-info btn-sm">Chi tiết</a>
                                        <a href="edit.php?masv=<?php echo $sv['MaSV']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                        <a href="delete.php?masv=<?php echo $sv['MaSV']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>

</html>