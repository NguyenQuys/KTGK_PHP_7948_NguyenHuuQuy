<?php
require_once 'auth.php';
require_once 'config.php';

if (!isset($_GET['masv'])) {
    header("Location: index.php");
    exit();
}

$masv = $_GET['masv'];

// Lấy thông tin sinh viên
$stmt = $conn->prepare("SELECT s.*, n.TenNganh 
                       FROM SinhVien s 
                       LEFT JOIN NganhHoc n ON s.MaNganh = n.MaNganh 
                       WHERE s.MaSV = ?");
$stmt->execute([$masv]);
$sinhvien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sinhvien) {
    header("Location: index.php");
    exit();
}

// Lấy danh sách học phần chưa đăng ký và còn chỗ
$sql = "SELECT * FROM HocPhan WHERE MaHP NOT IN (
            SELECT hp.MaHP 
            FROM HocPhan hp 
            JOIN ChiTietDangKy ctdk ON hp.MaHP = ctdk.MaHP 
            JOIN DangKy dk ON ctdk.MaDK = dk.MaDK 
            WHERE dk.MaSV = ?
        ) AND SoLuong > 0";
$stmt = $conn->prepare($sql);
$stmt->execute([$masv]);
$hocphans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách học phần đã đăng ký
$sql = "SELECT dk.MaDK, dk.NgayDK, hp.MaHP, hp.TenHP, hp.SoTinChi, hp.SoLuong 
        FROM DangKy dk 
        JOIN ChiTietDangKy ctdk ON dk.MaDK = ctdk.MaDK 
        JOIN HocPhan hp ON ctdk.MaHP = hp.MaHP 
        WHERE dk.MaSV = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$masv]);
$dangkys = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Xử lý xác nhận đăng ký
if (isset($_GET['confirm']) && $_GET['confirm'] == 1 && isset($_SESSION['temp_dangky'])) {
    try {
        $conn->beginTransaction();

        $madk = $_SESSION['temp_dangky']['madk'];
        $hocphans = $_SESSION['temp_dangky']['hocphans'];

        // Kiểm tra số lượng còn lại của các học phần
        foreach ($hocphans as $mahp) {
            $stmt = $conn->prepare("SELECT SoLuong FROM HocPhan WHERE MaHP = ?");
            $stmt->execute([$mahp]);
            $soluong = $stmt->fetchColumn();

            if ($soluong <= 0) {
                throw new Exception("Học phần đã hết chỗ!");
            }
        }

        // Thêm vào bảng DangKy
        $sql = "INSERT INTO DangKy (MaDK, NgayDK, MaSV) VALUES (?, CURDATE(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$madk, $masv]);

        // Thêm các học phần được chọn vào ChiTietDangKy và cập nhật số lượng
        foreach ($hocphans as $mahp) {
            // Thêm vào ChiTietDangKy
            $sql = "INSERT INTO ChiTietDangKy (MaDK, MaHP) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$madk, $mahp]);

            // Giảm số lượng học phần
            $sql = "UPDATE HocPhan SET SoLuong = SoLuong - 1 WHERE MaHP = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$mahp]);
        }

        $conn->commit();
        unset($_SESSION['temp_dangky']); // Xóa dữ liệu tạm thời
        header("Location: dangky_hocphan.php?masv=" . $masv . "&success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Xử lý chọn học phần
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['hocphans']) || !is_array($_POST['hocphans']) || count($_POST['hocphans']) == 0) {
        $error = "Vui lòng chọn ít nhất một học phần!";
    } else {
        // Lưu thông tin đăng ký vào session
        $_SESSION['temp_dangky'] = [
            'madk' => 'DK' . date('Ymd') . rand(1000, 9999),
            'hocphans' => $_POST['hocphans']
        ];
        header("Location: dangky_hocphan.php?masv=" . $masv . "&preview=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký Học phần</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Đăng ký Học phần mới</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if (isset($_GET['preview']) && isset($_SESSION['temp_dangky'])): ?>
                            <div class="alert alert-info">
                                <h5>Xem lại thông tin đăng ký:</h5>
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6 class="card-title">Thông tin đăng ký:</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Mã SV:</strong> <?php echo $sinhvien['MaSV']; ?></p>
                                                <p><strong>Họ tên:</strong> <?php echo $sinhvien['HoTen']; ?></p>
                                                <p><strong>Ngày sinh:</strong> <?php echo date('d/m/Y', strtotime($sinhvien['NgaySinh'])); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Ngành học:</strong> <?php echo $sinhvien['TenNganh']; ?></p>
                                                <p><strong>Ngày đăng ký:</strong> <?php echo date('d/m/Y'); ?></p>
                                            </div>
                                        </div>
                                        <div class="mt-3">
                                            <h6 class="card-title">Học phần đã chọn:</h6>
                                            <ul class="list-group">
                                                <?php
                                                foreach ($_SESSION['temp_dangky']['hocphans'] as $mahp) {
                                                    $stmt = $conn->prepare("SELECT TenHP, SoTinChi, SoLuong FROM HocPhan WHERE MaHP = ?");
                                                    $stmt->execute([$mahp]);
                                                    $hp = $stmt->fetch(PDO::FETCH_ASSOC);
                                                    echo "<li class='list-group-item'>{$hp['TenHP']} ({$hp['SoTinChi']} tín chỉ) - Còn {$hp['SoLuong']} chỗ</li>";
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                        <div class="mt-3">
                                            <a href="dangky_hocphan.php?masv=<?php echo $masv; ?>&confirm=1" class="btn btn-success">Xác nhận đăng ký</a>
                                            <a href="dangky_hocphan.php?masv=<?php echo $masv; ?>" class="btn btn-secondary">Hủy</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                            <div class="alert alert-success">
                                <h5>Đăng ký học phần thành công!</h5>
                                <div class="mt-3">
                                    <a href="dangky_hocphan.php?masv=<?php echo $masv; ?>" class="btn btn-primary">Tiếp tục đăng ký</a>
                                    <a href="index.php" class="btn btn-secondary">Quay lại</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['success']) && $_GET['success'] == 2): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                Hủy đăng ký học phần thành công!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['success']) && $_GET['success'] == 3): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                Đã hủy tất cả đăng ký học phần!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($_GET['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!isset($_GET['preview'])): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Đăng ký học phần mới</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label class="form-label">Chọn học phần:</label>
                                            <div class="row">
                                                <?php foreach ($hocphans as $hp): ?>
                                                    <div class="col-md-6 mb-2">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="hocphans[]"
                                                                value="<?php echo $hp['MaHP']; ?>"
                                                                id="hocphan<?php echo $hp['MaHP']; ?>">
                                                            <label class="form-check-label" for="hocphan<?php echo $hp['MaHP']; ?>">
                                                                <?php echo $hp['TenHP']; ?> (<?php echo $hp['SoTinChi']; ?> tín chỉ) - Còn <?php echo $hp['SoLuong']; ?> chỗ
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Tiếp tục</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Thông tin Sinh viên</h4>
                    </div>
                    <div class="card-body">
                        <p><strong>Mã SV:</strong> <?php echo $sinhvien['MaSV']; ?></p>
                        <p><strong>Họ tên:</strong> <?php echo $sinhvien['HoTen']; ?></p>
                        <p><strong>Ngành:</strong> <?php echo $sinhvien['TenNganh']; ?></p>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>Học phần đã đăng ký</h4>
                        <?php if (count($dangkys) > 0): ?>
                            <a href="xoa_tatca_hocphan.php?masv=<?php echo $masv; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Bạn có chắc chắn muốn xóa tất cả học phần đã đăng ký?')">
                                Xóa tất cả
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (count($dangkys) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Mã HP</th>
                                            <th>Tên HP</th>
                                            <th>TC</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($dangkys as $dk): ?>
                                            <tr>
                                                <td><?php echo $dk['MaHP']; ?></td>
                                                <td><?php echo $dk['TenHP']; ?></td>
                                                <td><?php echo $dk['SoTinChi']; ?></td>
                                                <td>
                                                    <a href="xoa_hocphan.php?masv=<?php echo $masv; ?>&madk=<?php echo $dk['MaDK']; ?>&mahp=<?php echo $dk['MaHP']; ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa học phần này?')">
                                                        Xóa
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Chưa đăng ký học phần nào.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>