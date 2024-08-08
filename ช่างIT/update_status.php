<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// ตรวจสอบว่าข้อมูลที่ต้องการมีอยู่หรือไม่
if (!isset($_POST['ag_id']) || !isset($_POST['u_id'])) {
    echo "Error: Required data is missing.";
    exit();
}

$ag_id = $_POST['ag_id'];
$u_id = $_POST['u_id'];

// อัปเดตสถานะการคืนในฐานข้อมูล
$sql = "UPDATE borrowing SET b_return_p = ?, b_status = 'ST001' WHERE b_name = ? AND b_status = 'ST002'";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $u_id, $ag_id);

if ($stmt->execute()) {
    // อัปเดตสถานะอุปกรณ์ในตาราง items_1
    $sql_update_item = "UPDATE items_1 SET ag_status = 'ST001' WHERE ag_id = ?";
    $stmt_item = $conn->prepare($sql_update_item);
    $stmt_item->bind_param('s', $ag_id);
    $stmt_item->execute();

    echo "Return confirmed successfully.";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
