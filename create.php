<?php
require_once 'config.php';

// Lấy danh sách ngành học
$stmt = $conn->query("SELECT * FROM NganhHoc");
$nganhhocs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $masv = $_POST['masv'];
    $hoten = $_POST['hoten'];
    $ngaysinh = $_POST['ngaysinh'];
    $gioitinh = $_POST['gioitinh'];
    $manganh = $_POST['manganh'];

    // Xử lý upload hình
    $hinh = '';
    if (isset($_FILES['hinh']) && $_FILES['hinh']['error'] == 0) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($_FILES["hinh"]["name"], PATHINFO_EXTENSION));
        $new_filename = $masv . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;

        if (move_uploaded_file($_FILES["hinh"]["tmp_name"], $target_file)) {
            $hinh = $target_file;
        }
    }

    $sql = "INSERT INTO SinhVien (MaSV, HoTen, NgaySinh, GioiTinh, Hinh, MaNganh) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$masv, $hoten, $ngaysinh, $gioitinh, $hinh, $manganh]);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Sinh viên mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Thêm Sinh viên mới</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="masv" class="form-label">Mã SV</label>
                <input type="text" class="form-control" id="masv" name="masv" required maxlength="10">
            </div>
            <div class="mb-3">
                <label for="hoten" class="form-label">Họ tên</label>
                <input type="text" class="form-control" id="hoten" name="hoten" required>
            </div>
            <div class="mb-3">
                <label for="ngaysinh" class="form-label">Ngày sinh</label>
                <input type="date" class="form-control" id="ngaysinh" name="ngaysinh" required>
            </div>
            <div class="mb-3">
                <label for="gioitinh" class="form-label">Giới tính</label>
                <select class="form-control" id="gioitinh" name="gioitinh" required>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Khác">Khác</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="manganh" class="form-label">Ngành học</label>
                <select class="form-control" id="manganh" name="manganh">
                    <option value="">Chọn ngành học</option>
                    <?php foreach ($nganhhocs as $nganh): ?>
                        <option value="<?php echo $nganh['MaNganh']; ?>"><?php echo $nganh['TenNganh']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="hinh" class="form-label">Hình ảnh</label>
                <input type="file" class="form-control" id="hinh" name="hinh" accept="image/*" onchange="previewImage(this)">
                <img id="preview" class="preview-image" alt="Preview">
            </div>
            <button type="submit" class="btn btn-primary">Thêm</button>
            <a href="index.php" class="btn btn-secondary">Quay lại</a>
        </form>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>

</html>