<?php
require_once 'config.php';

if (isset($_GET['mahp'])) {
    $mahp = $_GET['mahp'];

    try {
        // Bắt đầu transaction
        $conn->beginTransaction();

        // Xóa các bản ghi trong ChiTietDangKy
        $sql = "DELETE FROM ChiTietDangKy WHERE MaHP = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$mahp]);

        // Xóa học phần
        $sql = "DELETE FROM HocPhan WHERE MaHP = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$mahp]);

        // Commit transaction
        $conn->commit();
    } catch (PDOException $e) {
        // Nếu có lỗi, rollback transaction
        $conn->rollBack();
        echo "Lỗi: " . $e->getMessage();
    }
}

header("Location: hocphan.php");
exit();
