<?php
session_start();
include "../connect.php";
// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
// ตรวจสอบว่าได้รับข้อมูลจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // รับข้อมูลจากฟอร์ม
    $ag_id = $_POST['ag_id'];
    $ag_name = $_POST['ag_name'];
    $ag_type = $_POST['ag_type'];
    $ag_status = $_POST['ag_status'];
    // ป้องกัน SQL Injection โดยการเตรียมคำสั่ง SQL
    $stmt = $conn->prepare("UPDATE items_1 SET ag_name = ?, ag_type = ?, ag_status = ? WHERE ag_id = ?");
    $stmt->bind_param("ssss", $ag_name, $ag_type, $ag_status, $ag_id);
    // ประมวลผลคำสั่ง SQL
    if ($stmt->execute()) {
        // หากอัปเดตสำเร็จ ให้ redirect กลับไปยังหน้าหลักหรือหน้าที่ต้องการ
        header("Location: manage_items.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
    // ปิดการเชื่อมต่อ
    $stmt->close();
    $conn->close();
}
?>
