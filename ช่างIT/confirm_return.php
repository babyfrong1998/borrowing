<?php
session_start();
include "../connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ag_id']) && $_POST['action'] == 'confirm_return') {
    $ag_id = $_POST['ag_id'];

    // อัปเดตสถานะ ag_status เป็น ST001
    $sql_update = "UPDATE items_1 SET ag_status = 'ST001' WHERE ag_id = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("s", $ag_id);
    
    if ($stmt->execute()) {
        // ตรวจสอบว่ามี ag_status ที่เป็น ST008 อยู่ใน BruID เดียวกันหรือไม่
        $BruID_query = "SELECT BruID FROM items_1 WHERE ag_id = ?";
        $stmt = $conn->prepare($BruID_query);
        $stmt->bind_param("s", $ag_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $BruID = $row['BruID'];
        
        $check_items_query = "SELECT COUNT(*) as count FROM items_1 WHERE BruID = ? AND ag_status = 'ST008'";
        $stmt = $conn->prepare($check_items_query);
        $stmt->bind_param("s", $BruID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        // ถ้าไม่มี ag_status ที่เป็น ST008 แล้ว
        if ($row['count'] == 0) {
            // อัปเดต st_id ในตาราง borroww เป็น ST009
            $update_borroww_query = "UPDATE borroww SET st_id = 'ST009', BrudateRe = NOW() WHERE BruID = ?";
            $stmt = $conn->prepare($update_borroww_query);
            $stmt->bind_param("s", $BruID);
            $stmt->execute();
        }
        
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>