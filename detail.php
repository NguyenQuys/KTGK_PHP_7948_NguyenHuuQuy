<?php
require_once 'config.php';

if (!isset($_GET['masv'])) {
    header("Location: index.php");
    exit();
}

$masv = $_GET['masv'];

// Lấy thông tin sinh viên kèm thông tin ngành học
$sql = "SELECT s.*, n.TenNganh 
        FROM SinhVien s 
        LEFT JOIN NganhHoc n ON s.MaNganh = n.MaNganh 
        WHERE s.MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$masv]);
$sinhvien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sinhvien) {
    header("Location: index.php");
    exit();
}

// Lấy danh sách đăng ký học phần
$sql = "SELECT dk.MaDK, dk.NgayDK, hp.MaHP, hp.TenHP, hp.SoTinChi 
        FROM DangKy dk 
        JOIN ChiTietDangKy ctdk ON dk.MaDK = ctdk.MaDK 
        JOIN HocPhan hp ON ctdk.MaHP = hp.MaHP 
        WHERE dk.MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$masv]);
$dangkys = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .student-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .info-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <?php if ($sinhvien['Hinh']): ?>
                            <img src="<?php echo $sinhvien['Hinh']; ?>" alt="Hình sinh viên" class="student-image mb-3">
                        <?php else: ?>
                            <div class="student-image mb-3 bg-light d-flex align-items-center justify-content-center">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <h4 class="card-title"><?php echo $sinhvien['HoTen']; ?></h4>
                        <p class="card-text text-muted"><?php echo $sinhvien['MaSV']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="info-card">
                    <h5 class="mb-4">Thông tin cá nhân</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Mã SV:</strong> <?php echo $sinhvien['MaSV']; ?></p>
                            <p><strong>Họ tên:</strong> <?php echo $sinhvien['HoTen']; ?></p>
                            <p><strong>Ngày sinh:</strong> <?php echo date('d/m/Y', strtotime($sinhvien['NgaySinh'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Giới tính:</strong> <?php echo $sinhvien['GioiTinh']; ?></p>
                            <p><strong>Ngành học:</strong> <?php echo $sinhvien['TenNganh']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5 class="mb-4">Danh sách học phần đã đăng ký</h5>
                    <?php if (count($dangkys) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã đăng ký</th>
                                        <th>Ngày đăng ký</th>
                                        <th>Mã HP</th>
                                        <th>Tên học phần</th>
                                        <th>Số tín chỉ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dangkys as $dk): ?>
                                        <tr>
                                            <td><?php echo $dk['MaDK']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($dk['NgayDK'])); ?></td>
                                            <td><?php echo $dk['MaHP']; ?></td>
                                            <td><?php echo $dk['TenHP']; ?></td>
                                            <td><?php echo $dk['SoTinChi']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Chưa đăng ký học phần nào.</p>
                    <?php endif; ?>
                </div>

                <div class="mt-3">
                    <a href="edit.php?masv=<?php echo $sinhvien['MaSV']; ?>" class="btn btn-warning">Sửa thông tin</a>
                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>

</html>