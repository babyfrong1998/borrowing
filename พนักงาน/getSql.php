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
$b_status = 'ST007'; // กำหนดสถานะ "จอง"
$b_return = $return_date ? $return_date : NULL; // ใช้ NULL แทน 0

// ตรวจสอบว่าอุปกรณ์นี้ถูกจองอยู่แล้วหรือไม่
$sql_check_reservation = "SELECT * FROM borrowing WHERE b_name = ? AND b_status = 'ST007'";
$stmt_check_reservation = $conn->prepare($sql_check_reservation);
$stmt_check_reservation->bind_param("s", $b_name);
$stmt_check_reservation->execute();
$result_check_reservation = $stmt_check_reservation->get_result();

if ($result_check_reservation->num_rows > 0) {
    // หากมีการจองอยู่แล้ว แจ้งเตือนผู้ใช้และหยุดกระบวนการ
    echo "Error: This item is already reserved.";
    $stmt_check_reservation->close();
    $conn->close();
    exit();
}
$stmt_check_reservation->close();

// สร้าง b_id จากเลข 1
$new_b_id = 1;

// ตรวจสอบว่า b_id นี้มีอยู่แล้วหรือไม่ ถ้ามีจะเพิ่มค่าทีละ 1
while (true) {
    $sql_check_id = "SELECT b_id FROM borrowing WHERE b_id = ?";
    $stmt_check_id = $conn->prepare($sql_check_id);
    $stmt_check_id->bind_param("i", $new_b_id);
    $stmt_check_id->execute();
    $result_check_id = $stmt_check_id->get_result();
    
    if ($result_check_id->num_rows == 0) {
        // ถ้าไม่มี b_id นี้ในฐานข้อมูล ให้ออกจาก loop
        break;
    }
    $new_b_id++; // เพิ่มค่า b_id ทีละ 1
}

// สร้าง SQL query เพื่อแทรกข้อมูลลงในตาราง borrowing โดยระบุ b_id
$sql = "INSERT INTO borrowing (b_id, b_name, b_date, b_borower, b_return_p, b_agency, b_status, b_return) VALUES (?, ?, ?, ?, '', ?, ?, ?)";

// เตรียม statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

// ผูกพารามิเตอร์
$stmt->bind_param("issssss", $new_b_id, $b_name, $b_date, $b_borower, $b_agency, $b_status, $b_return);

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
