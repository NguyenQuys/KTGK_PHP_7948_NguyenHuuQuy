<?php
require_once 'config.php';

if (!isset($_GET['masv'])) {
    header("Location: index.php");
    exit();
}

$masv = $_GET['masv'];

// Lấy danh sách ngành học
$stmt = $conn->query("SELECT * FROM NganhHoc");
$nganhhocs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hoten = $_POST['hoten'];
    $ngaysinh = $_POST['ngaysinh'];
    $gioitinh = $_POST['gioitinh'];
    $manganh = $_POST['manganh'];

    // Xử lý upload hình
    $hinh = $_POST['hinh_cu'];
    if (isset($_FILES['hinh']) && $_FILES['hinh']['error'] == 0) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($_FILES["hinh"]["name"], PATHINFO_EXTENSION));
        $new_filename = $masv . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["hinh"]["tmp_name"], $target_file)) {
            // Xóa hình cũ nếu có
            if ($hinh && file_exists($hinh)) {
                unlink($hinh);
            }
            $hinh = $target_file;
        }
    }

    $sql = "UPDATE SinhVien SET HoTen = ?, NgaySinh = ?, GioiTinh = ?, Hinh = ?, MaNganh = ? WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$hoten, $ngaysinh, $gioitinh, $hinh, $manganh, $masv]);

    header("Location: index.php");
    exit();
}

// Lấy thông tin sinh viên
$stmt = $conn->prepare("SELECT * FROM SinhVien WHERE MaSV = ?");
$stmt->execute([$masv]);
$sinhvien = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sinhvien) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa thông tin Sinh viên</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Sửa thông tin Sinh viên</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="masv" class="form-label">Mã SV</label>
                <input type="text" class="form-control" id="masv" value="<?php echo $sinhvien['MaSV']; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="hoten" class="form-label">Họ tên</label>
                <input type="text" class="form-control" id="hoten" name="hoten" value="<?php echo $sinhvien['HoTen']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="ngaysinh" class="form-label">Ngày sinh</label>
                <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" value="<?php echo $sinhvien['NgaySinh']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="gioitinh" class="form-label">Giới tính</label>
                <select class="form-control" id="gioitinh" name="gioitinh" required>
                    <option value="Nam" <?php echo $sinhvien['GioiTinh'] == 'Nam' ? 'selected' : ''; ?>>Nam</option>
                    <option value="Nữ" <?php echo $sinhvien['GioiTinh'] == 'Nữ' ? 'selected' : ''; ?>>Nữ</option>
                    <option value="Khác" <?php echo $sinhvien['GioiTinh'] == 'Khác' ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="manganh" class="form-label">Ngành học</label>
                <select class="form-control" id="manganh" name="manganh">
                    <option value="">Chọn ngành học</option>
                    <?php foreach ($nganhhocs as $nganh): ?>
                        <option value="<?php echo $nganh['MaNganh']; ?>" <?php echo $sinhvien['MaNganh'] == $nganh['MaNganh'] ? 'selected' : ''; ?>>
                            <?php echo $nganh['TenNganh']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="hinh" class="form-label">Hình ảnh</label>
                <?php if ($sinhvien['Hinh']): ?>
                    <div class="mb-2">
                        <img src="<?php echo $sinhvien['Hinh']; ?>" alt="Hình hiện tại" style="width: 100px; height: 100px; object-fit: cover;">
                    </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="hinh" name="hinh" accept="image/*">
                <input type="hidden" name="hinh_cu" value="<?php echo $sinhvien['Hinh']; ?>">
            </div>
            <button type="submit" class="btn btn-primary">Cập nhật</button>
            <a href="index.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>
</body>

</html>