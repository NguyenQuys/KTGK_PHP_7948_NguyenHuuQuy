<?php
require_once 'auth.php';
require_once 'config.php';

if (!isset($_GET['masv'])) {
    header("Location: index.php");
    exit();
}

$masv = $_GET['masv'];

try {
    $conn->beginTransaction();

    // Lấy danh sách mã đăng ký của sinh viên
    $sql = "SELECT MaDK FROM DangKy WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$masv]);
    $madks = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Lấy danh sách học phần đã đăng ký
    $sql = "SELECT DISTINCT MaHP FROM ChiTietDangKy WHERE MaDK IN (" . implode(',', array_fill(0, count($madks), '?')) . ")";
    $stmt = $conn->prepare($sql);
    $stmt->execute($madks);
    $mahps = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Xóa tất cả chi tiết đăng ký
    foreach ($madks as $madk) {
        $sql = "DELETE FROM ChiTietDangKy WHERE MaDK = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$madk]);
    }

    // Tăng số lượng cho tất cả học phần
    foreach ($mahps as $mahp) {
        $sql = "UPDATE HocPhan SET SoLuong = SoLuong + 1 WHERE MaHP = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$mahp]);
    }

    // Xóa tất cả đăng ký của sinh viên
    $sql = "DELETE FROM DangKy WHERE MaSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$masv]);

    $conn->commit();
    header("Location: dangky_hocphan.php?masv=" . $masv . "&success=3");
} catch (PDOException $e) {
    $conn->rollBack();
    header("Location: dangky_hocphan.php?masv=" . $masv . "&error=" . urlencode("Lỗi: " . $e->getMessage()));
}
exit();
