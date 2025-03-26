<?php
require_once 'config.php';

// Lấy danh sách học phần
$sql = "SELECT * FROM HocPhan ORDER BY MaHP";
$stmt = $conn->query($sql);
$hocphans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Học phần</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Danh sách Học phần</h1>
            <div>
                <a href="create_hocphan.php" class="btn btn-primary">Thêm Học phần mới</a>
                <a href="index.php" class="btn btn-secondary">Quay lại</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Mã HP</th>
                                <th>Tên học phần</th>
                                <th>Số tín chỉ</th>
                                <th>Số lượng</th> <!-- Thêm cột Số lượng -->
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hocphans as $hp): ?>
                                <tr>
                                    <td><?php echo $hp['MaHP']; ?></td>
                                    <td><?php echo $hp['TenHP']; ?></td>
                                    <td><?php echo $hp['SoTinChi']; ?></td>
                                    <td><?php echo $hp['SoLuong']; ?></td> <!-- Hiển thị số lượng -->
                                    <td>
                                        <a href="edit_hocphan.php?mahp=<?php echo $hp['MaHP']; ?>" class="btn btn-warning btn-sm">Sửa</a>
                                        <a href="delete_hocphan.php?mahp=<?php echo $hp['MaHP']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</a>
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