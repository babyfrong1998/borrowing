<?php
session_start();
include "../connect.php";

// ตรวจสอบการเชื่อมต่อกับฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// รับข้อมูลจากฟอร์ม
$b_name = $_POST['itemselect'];
$b_date = $_POST['bordate'];
$b_borower = $_POST['u_id'];
$b_agency = $_POST['office'];
$return_date = isset($_POST['returnDate']) ? $_POST['returnDate'] : '';

// กำหนดค่า b_status และ b_return ตามการกำหนด returnDate
$b_status = $return_date ? 'ST005' : 'ST002';
$b_return = $return_date ? $return_date : '';

// ตรวจสอบค่าก่อนแทรก
echo "b_name: $b_name<br>";
echo "b_date: $b_date<br>";
echo "b_borrower: $b_borower<br>";
echo "b_agency: $b_agency<br>";
echo "b_status: $b_status<br>";
echo "return_date: $return_date<br>";

// สร้าง SQL query เพื่อแทรกข้อมูลลงในตาราง borrowing โดยไม่ต้องระบุ b_id
$sql = "INSERT INTO borrowing (b_name, b_date, b_borower, b_return_p, b_agency, b_status, b_return) VALUES (?, ?, ?, '', ?, ?, ?)";

// เตรียม statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

// ผูกพารามิเตอร์
$stmt->bind_param("ssssss", $b_name, $b_date, $b_borower, $b_agency, $b_status, $b_return);

// ทำการ execute
if ($stmt->execute()) {
    // เมื่อการบันทึกสำเร็จ ให้ทำการอัปเดตสถานะในตาราง items_1
    $update_sql = "UPDATE items_1 SET ag_status = ? WHERE ag_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if ($update_stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    
    // ผูกพารามิเตอร์สำหรับการอัปเดต
    $update_stmt->bind_param("ss", $b_status, $b_name);
    
    // ทำการ execute
    if ($update_stmt->execute()) {
        echo "New record created and item status updated successfully";
    } else {
        echo "Error updating item status: " . $update_stmt->error;
    }
    
    // ปิด statement สำหรับการอัปเดต
    $update_stmt->close();
} else {
    echo "Error: " . $stmt->error;
}

// ปิดการเชื่อมต่อ
$stmt->close();
$conn->close();
?>
