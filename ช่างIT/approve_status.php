<?php
session_start();
include "../connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ag_id = $_POST['ag_id'];

    // ตรวจสอบสถานะของ b_status ก่อนทำการอัปเดต
    $sqlCheckStatus = "SELECT b_id, b_status, b_return FROM borrowing WHERE b_name = ? AND b_status != 'ST009'";
    $stmtCheckStatus = $conn->prepare($sqlCheckStatus);
    $stmtCheckStatus->bind_param("s", $ag_id);
    $stmtCheckStatus->execute();
    $resultCheckStatus = $stmtCheckStatus->get_result();

    if ($resultCheckStatus->num_rows > 0) {
        // มีแถวที่ b_status ไม่เป็น ST009
        while ($rowCheckStatus = $resultCheckStatus->fetch_assoc()) {
            $b_id = $rowCheckStatus['b_id'];
            $b_return = $rowCheckStatus['b_return'];

            // ตรวจสอบว่ามีการกำหนดค่าในช่อง b_return หรือไม่
            if ($b_return) {
                // หากมีการกำหนด b_return, อัปเดตสถานะเป็น ST005
                $newAgStatus = 'ST005';
                $newBStatus = 'ST005';
            } else {
                // หากไม่มีการกำหนด b_return, อัปเดตสถานะเป็น ST002
                $newAgStatus = 'ST002';
                $newBStatus = 'ST002';
            }

            // อัปเดต ag_status ในตาราง items_1
            $sqlUpdateItems = "UPDATE items_1 SET ag_status = ? WHERE ag_id = ?";
            $stmtUpdateItems = $conn->prepare($sqlUpdateItems);
            $stmtUpdateItems->bind_param("ss", $newAgStatus, $ag_id);
            $stmtUpdateItems->execute();

            // อัปเดต b_status ในตาราง borrowing สำหรับแถวที่ตรงกับ b_id
            $sqlUpdateBorrowing = "UPDATE borrowing SET b_status = ? WHERE b_id = ?";
            $stmtUpdateBorrowing = $conn->prepare($sqlUpdateBorrowing);
            $stmtUpdateBorrowing->bind_param("ss", $newAgStatus, $b_id);
            $stmtUpdateBorrowing->execute();
        }

        // หลังจากทำการอัปเดตเสร็จ, เปลี่ยนเส้นทางกลับไปที่หน้า home_it.php
        header("Location: home_it.php");
        exit();
    } else {
        // ถ้าไม่มีแถวที่ตรงกับเงื่อนไข, ให้ข้อผิดพลาด
        echo "No records found with the given name and status not equal to ST009.";
    }
} else {
    echo "Invalid request method.";
    exit();
}
?>
