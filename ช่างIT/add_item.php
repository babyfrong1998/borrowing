<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าข้อมูลถูกส่งมาครบหรือไม่
if (isset($_POST['ag_type']) && isset($_POST['ag_id']) && isset($_POST['ag_name'])) {
    $ag_type = $_POST['ag_type'];
    $ag_id = $_POST['ag_id'];
    $ag_name = $_POST['ag_name'];

    // เพิ่มอุปกรณ์ลงในฐานข้อมูล
    $sql = "INSERT INTO items_1 (ag_type, ag_id, ag_name, ag_status) VALUES (?, ?, ?, 'ST001')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $ag_type, $ag_id, $ag_name);
    if ($stmt->execute()) {
        // แสดงข้อความแจ้งเตือนและ redirect ไปยังหน้า home_it.php
        echo "<script>
                alert('เพิ่มอุปกรณ์เรียบร้อยแล้ว');
                window.location.href = 'home_it.php';
              </script>";
    } else {
        echo "<script>
                alert('เกิดข้อผิดพลาด: " . $stmt->error . "');
                window.location.href = 'home_it.php';
              </script>";
    }
    $stmt->close();
} else {
    echo "<script>
            alert('Error: Required data is missing.');
            window.location.href = 'home_it.php';
          </script>";
}

$conn->close();
