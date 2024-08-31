<?php
session_start();
include "../connect.php";


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ag_id']) && $_POST['action'] == 'confirm_return') {
    $ag_id = $_POST['ag_id'];
    $user_id = $_SESSION['u_id']; // ดึง u_id ของผู้ใช้ที่ล็อกอิน

    // ดึงข้อมูลที่เกี่ยวข้องจากตาราง borroww
    $borroww_query = "SELECT BruID, BrudateB, BrudateRe, u_id, number, st_id FROM borroww WHERE BruID = (SELECT BruID FROM items_1 WHERE ag_id = ?)";
    $stmt = $conn->prepare($borroww_query);
    $stmt->bind_param("s", $ag_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $borrow_row = $result->fetch_assoc();

    if ($borrow_row) {
        $BruID = $borrow_row['BruID'];

        // บันทึกประวัติการคืนลงในตาราง borrohistory
        $insert_history_query = "INSERT INTO borrohistory (BruID, b_items, b_date, b_return, b_user, b_agency, b_status, b_return_p) 
                                 VALUES (?, ?, ?, NOW(), ?, ?, 'ST009', ?)";
        $stmt = $conn->prepare($insert_history_query);
        $stmt->bind_param("ssssss", $BruID, $ag_id, $borrow_row['BrudateB'], $borrow_row['u_id'], $borrow_row['number'], $user_id);
        $stmt->execute();

        // อัปเดตสถานะ ag_status เป็น ST001
        $sql_update = "UPDATE items_1 SET ag_status = 'ST001' WHERE ag_id = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("s", $ag_id);
        $stmt->execute();

        // ตรวจสอบว่า ag_status ทั้งหมดใน BruID นั้นเป็น ST001 หรือไม่
        $check_items_query = "SELECT COUNT(*) as count FROM items_1 WHERE BruID = ? AND ag_status != 'ST001'";
        $stmt = $conn->prepare($check_items_query);
        $stmt->bind_param("s", $BruID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // ถ้าไม่มี ag_status ที่ไม่ใช่ ST001 (คือทุกสถานะเป็น ST001)
        if ($row['count'] == 0) {
            // ตรวจสอบ BrudateRe ในตาราง borroww
            if ($borrow_row['BrudateRe'] == '0000-00-00' || $borrow_row['BrudateRe'] == '0000-00-00 00:00:00') {
                $update_borroww_query = "UPDATE borroww SET st_id = 'ST009', BrudateRe = NOW() WHERE BruID = ?";
            } else {
                // ถ้าไม่ใช่ '0000-00-00' อัปเดตแค่ st_id
                $update_borroww_query = "UPDATE borroww SET st_id = 'ST009' WHERE BruID = ?";
            }
            $stmt = $conn->prepare($update_borroww_query);
            $stmt->bind_param("s", $BruID);
            $stmt->execute();
        }

        // เพิ่มการตรวจสอบและอัปเดตเมื่อ st_id เป็น ST006
        if ($borrow_row['st_id'] == 'ST006') {
            if ($borrow_row['BrudateRe'] == '0000-00-00' || $borrow_row['BrudateRe'] == '0000-00-00 00:00:00') {
                $update_stid_query = "UPDATE borroww SET st_id = 'ST002' WHERE BruID = ?";
            } else {
                $update_stid_query = "UPDATE borroww SET st_id = 'ST005' WHERE BruID = ?";
            }
            $stmt = $conn->prepare($update_stid_query);
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