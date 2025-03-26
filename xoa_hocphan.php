<?php
require_once 'auth.php';
require_once 'config.php';

if (!isset($_GET['masv']) || !isset($_GET['madk']) || !isset($_GET['mahp'])) {
    header("Location: index.php");
    exit();
}

$masv = $_GET['masv'];
$madk = $_GET['madk'];
$mahp = $_GET['mahp'];

try {
    $conn->beginTransaction();

    // Xóa học phần khỏi ChiTietDangKy
    $sql = "DELETE FROM ChiTietDangKy WHERE MaDK = ? AND MaHP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$madk, $mahp]);

    // Tăng số lượng học phần lên 1
    $sql = "UPDATE HocPhan SET SoLuong = SoLuong + 1 WHERE MaHP = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$mahp]);

    // Kiểm tra nếu không còn học phần nào trong đăng ký này
    $sql = "SELECT COUNT(*) FROM ChiTietDangKy WHERE MaDK = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$madk]);
    $count = $stmt->fetchColumn();

    // Nếu không còn học phần nào, xóa cả bản ghi trong DangKy
    if ($count == 0) {
        $sql = "DELETE FROM DangKy WHERE MaDK = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$madk]);
    }

    $conn->commit();
    header("Location: dangky_hocphan.php?masv=" . $masv . "&success=2");
} catch (PDOException $e) {
    $conn->rollBack();
    header("Location: dangky_hocphan.php?masv=" . $masv . "&error=" . urlencode("Lỗi: " . $e->getMessage()));
}
exit();
