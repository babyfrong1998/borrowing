<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าข้อมูลถูกส่งมาครบหรือไม่
if (isset($_POST['ag_type']) && isset($_POST['ag_id'])) {
    $ag_type = $_POST['ag_type'];
    $ag_id = $_POST['ag_id'];

    // เพิ่มอุปกรณ์ลงในฐานข้อมูล
    $sql = "INSERT INTO items_1 (ag_type, ag_id, ag_status) VALUES (?, ?, 'ST001')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $ag_type, $ag_id);
    if ($stmt->execute()) {
        echo "เพิ่มอุปกรณ์เรียบร้อยแล้ว";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Error: Required data is missing.";
}

$conn->close();
?>
