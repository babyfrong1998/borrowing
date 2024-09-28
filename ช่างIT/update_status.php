<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// ตรวจสอบว่าข้อมูลที่ต้องการมีอยู่หรือไม่
if (!isset($_POST['ag_id'])) {
    echo "Error: Required data is missing.";
    exit();
}

$ag_id = $_POST['ag_id'];
$u_id = $_SESSION['u_id'];
// เริ่มต้นการทำงานในฐานข้อมูล
$conn->begin_transaction();
try {
    // อัปเดตสถานะอุปกรณ์ในตาราง items_1
    $sql_update_item = "UPDATE items_1 SET ag_status = 'ST001' WHERE ag_id = ?";
    $stmt_item = $conn->prepare($sql_update_item);
    $stmt_item->bind_param('s', $ag_id);
    $stmt_item->execute();
    // อัปเดตสถานะการคืนในตาราง borrowing
    $sql = "UPDATE borrowing SET b_status = 'ST009', b_return_p = ? WHERE b_name = ? AND b_status = 'ST008'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $u_id,$ag_id);
    $stmt->execute();
    // ถ้าทุกอย่างเรียบร้อย ทำการคอมมิตการทำงาน
    $conn->commit();
    echo "Return confirmed successfully.";
} catch (Exception $e) {
    // หากมีข้อผิดพลาด ให้ทำการยกเลิกการทำงาน
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
$stmt->close();
$conn->close();
?>
