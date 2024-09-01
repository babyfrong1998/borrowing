<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// ตรวจสอบการส่งข้อมูลจากฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['BruID']) && isset($_POST['ag_id']) && is_array($_POST['ag_id'])) {
        $bru_id = $_POST['BruID'];
        $ag_ids = $_POST['ag_id'];
        $return_date = $_POST['BrudateRe']; // รับค่าวันที่คืนจากฟอร์ม

        // ดึงข้อมูลจากตาราง borroww เพื่อเก็บค่า BrudateB
        $sql_borrow_info = "SELECT BrudateB FROM borroww WHERE BruID = ?";
        $stmt_borrow_info = $conn->prepare($sql_borrow_info);
        $stmt_borrow_info->bind_param('s', $bru_id);
        $stmt_borrow_info->execute();
        $borrow_info_result = $stmt_borrow_info->get_result();
        $borrow_info = $borrow_info_result->fetch_assoc();

        // ตรวจสอบว่า BrudateB เป็น 0000-00-00 หรือไม่
        $brudateB = ($borrow_info['BrudateB'] === '0000-00-00') ? date('Y-m-d') : $borrow_info['BrudateB'];

        // กำหนดสถานะที่อัพเดทในตาราง borroww
        $borrow_status = ($return_date !== '0000-00-00') ? 'ST005' : 'ST002';

        // อัพเดทสถานะของการยืมในตาราง borroww
        $sql_update_borrow = "UPDATE borroww SET st_id = ?, BrudateB = ? WHERE BruID = ?";
        $stmt_update_borrow = $conn->prepare($sql_update_borrow);
        $stmt_update_borrow->bind_param('sss', $borrow_status, $brudateB, $bru_id);
        $stmt_update_borrow->execute();
        // ดึงข้อมูลอีกครั้งหลังจากอัพเดท
        $sql_borrow_info = "SELECT u_id, BrudateB, BrudateRe, number FROM borroww WHERE BruID = ?";
        $stmt_borrow_info = $conn->prepare($sql_borrow_info);
        $stmt_borrow_info->bind_param('s', $bru_id);
        $stmt_borrow_info->execute();
        $borrow_info_result = $stmt_borrow_info->get_result();
        $borrow_info = $borrow_info_result->fetch_assoc();

        foreach ($ag_ids as $ag_id) {
            // อัพเดทสถานะของอุปกรณ์ในตาราง items_1 และเก็บ BruID
            $sql_update_items = "UPDATE items_1 SET ag_status = ?, BruID = ? WHERE ag_id = ?";
            $stmt_update_items = $conn->prepare($sql_update_items);
            $stmt_update_items->bind_param('sss', $borrow_status, $bru_id, $ag_id);
            $stmt_update_items->execute();

            // ตรวจสอบว่า BrudateRe เป็น 0000-00-00 หรือไม่ เพื่อตัดสินใจสถานะการบันทึกประวัติ
            $history_status = ($borrow_info['BrudateRe'] !== '0000-00-00') ? 'ST005' : 'ST002';

            // บันทึกข้อมูลการยืมลงในตาราง borrohistory
            $sql_insert_history = "INSERT INTO borrohistory (BruID, b_items, b_date, b_return, b_user, b_agency, b_status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert_history = $conn->prepare($sql_insert_history);
            $stmt_insert_history->bind_param('sssssss', $bru_id, $ag_id, $brudateB, $return_date, $borrow_info['u_id'], $borrow_info['number'], $history_status);
            $stmt_insert_history->execute();
        }

        // แสดงข้อความแจ้งเตือน
        echo "<script>
         alert('การยืมอุปกรณ์ได้ถูกยืนยันแล้ว.');
         window.location.href = 'home_it.php'; // เปลี่ยนไปยังหน้าอื่นถ้าต้องการ
       </script>";
    } else {
        echo "<script>
         alert('ข้อมูลไม่ครบถ้วน.');
         window.location.href = 'home_it.php'; // เปลี่ยนไปยังหน้าอื่นถ้าต้องการ
       </script>";
    }
} else {
    echo "<script>
     alert('วิธีการส่งคำขอไม่ถูกต้อง.');
     window.location.href = 'home_it.php'; // เปลี่ยนไปยังหน้าอื่นถ้าต้องการ
   </script>";
}

$conn->close();
