<?php
require_once 'config.php';

if (isset($_GET['masv'])) {
    $masv = $_GET['masv'];

    try {
        // Bắt đầu transaction
        $conn->beginTransaction();

        // Lấy thông tin hình ảnh trước khi xóa
        $stmt = $conn->prepare("SELECT Hinh FROM SinhVien WHERE MaSV = ?");
        $stmt->execute([$masv]);
        $sinhvien = $stmt->fetch(PDO::FETCH_ASSOC);

        // Xóa các bản ghi trong ChiTietDangKy liên quan đến sinh viên
        $sql = "DELETE ctdk FROM ChiTietDangKy ctdk 
                INNER JOIN DangKy dk ON ctdk.MaDK = dk.MaDK 
                WHERE dk.MaSV = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$masv]);

        // Xóa các bản ghi trong DangKy
        $sql = "DELETE FROM DangKy WHERE MaSV = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$masv]);

        // Xóa sinh viên
        $sql = "DELETE FROM SinhVien WHERE MaSV = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$masv]);

        // Xóa hình ảnh nếu có
        if ($sinhvien['Hinh'] && file_exists($sinhvien['Hinh'])) {
            unlink($sinhvien['Hinh']);
        }

        // Commit transaction
        $conn->commit();
    } catch (PDOException $e) {
        // Nếu có lỗi, rollback transaction
        $conn->rollBack();
        echo "Lỗi: " . $e->getMessage();
    }
}

header("Location: index.php");
exit();
