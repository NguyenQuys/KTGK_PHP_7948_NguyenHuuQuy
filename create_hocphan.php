<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mahp = $_POST['mahp'];
    $tenhp = $_POST['tenhp'];
    $sotinchi = $_POST['sotinchi'];

    $sql = "INSERT INTO HocPhan (MaHP, TenHP, SoTinChi) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$mahp, $tenhp, $sotinchi]);

    header("Location: hocphan.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm Học phần mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thêm Học phần mới</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="mahp" class="form-label">Mã học phần</label>
                                <input type="text" class="form-control" id="mahp" name="mahp" required maxlength="10">
                            </div>
                            <div class="mb-3">
                                <label for="tenhp" class="form-label">Tên học phần</label>
                                <input type="text" class="form-control" id="tenhp" name="tenhp" required>
                            </div>
                            <div class="mb-3">
                                <label for="sotinchi" class="form-label">Số tín chỉ</label>
                                <input type="number" class="form-control" id="sotinchi" name="sotinchi" required min="1" max="10">
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">Thêm</button>
                                <a href="hocphan.php" class="btn btn-secondary">Quay lại</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>