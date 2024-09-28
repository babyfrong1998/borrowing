<?php
include "../connect.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $BruID = $_POST['BruID'];
    // อัพเดทสถานะ ag_status เป็น ST008 สำหรับ ag_id ทั้งหมดที่เป็น ST002 หรือ ST005 ในตาราง items_1
    $sql_items = "UPDATE items_1 SET ag_status = 'ST008' WHERE BruID = ? AND (ag_status = 'ST002' OR ag_status = 'ST005'OR ag_status = 'ST006')";
    // อัพเดทสถานะ st_id เป็น ST008 สำหรับ record ที่ตรงกับ BruID ในตาราง borroww
    $sql_borroww = "UPDATE borroww SET st_id = 'ST008' WHERE BruID = ? AND (st_id = 'ST002' OR st_id = 'ST005' OR st_id = 'ST006')";
    // เริ่มการเชื่อมต่อแบบ transaction เพื่อให้ทั้งสองคำสั่ง SQL สำเร็จพร้อมกัน
    $conn->begin_transaction();
    try {
        // เตรียมและรันคำสั่ง SQL สำหรับตาราง items_1
        if ($stmt_items = $conn->prepare($sql_items)) {
            $stmt_items->bind_param("s", $BruID);
            $stmt_items->execute();
            $stmt_items->close();
        } else {
            throw new Exception("Error preparing SQL for items_1: " . $conn->error);
        }
        // เตรียมและรันคำสั่ง SQL สำหรับตาราง borroww
        if ($stmt_borroww = $conn->prepare($sql_borroww)) {
            $stmt_borroww->bind_param("s", $BruID);
            $stmt_borroww->execute();
            $stmt_borroww->close();
        } else {
            throw new Exception("Error preparing SQL for borroww: " . $conn->error);
        }
        // ถ้าทุกอย่างสำเร็จ ให้ commit การเปลี่ยนแปลง
        $conn->commit();
        echo "success";
    } catch (Exception $e) {
        // หากมีข้อผิดพลาด ให้ rollback การเปลี่ยนแปลง
        $conn->rollback();
        echo "error: " . $e->getMessage();
    }
    $conn->close();
}
