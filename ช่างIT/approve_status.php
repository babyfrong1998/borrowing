<?php
session_start();
include "../connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ag_id = $_POST['ag_id'];

    // ตรวจสอบว่ามีการกำหนดค่าในช่อง b_return หรือไม่
    $sqlCheck = "SELECT b_return FROM borrowing WHERE b_name = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("s", $ag_id);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();
    $rowCheck = $resultCheck->fetch_assoc();

    if ($rowCheck['b_return']) {
        // หากมีการกำหนด b_return, อัปเดตสถานะเป็น ST005
        $newAgStatus = 'ST005';
    } else {
        // หากไม่มีการกำหนด b_return, อัปเดตสถานะเป็น ST002
        $newAgStatus = 'ST002';
    }

    // อัปเดต ag_status ในตาราง items_1
    $sqlUpdateItems = "UPDATE items_1 SET ag_status = ? WHERE ag_id = ?";
    $stmtUpdateItems = $conn->prepare($sqlUpdateItems);
    $stmtUpdateItems->bind_param("ss", $newAgStatus, $ag_id);
    $stmtUpdateItems->execute();

    // อัปเดต b_status ในตาราง borrowing
    $sqlUpdateBorrowing = "UPDATE borrowing SET b_status = ? WHERE b_name = ?";
    $stmtUpdateBorrowing = $conn->prepare($sqlUpdateBorrowing);
    $stmtUpdateBorrowing->bind_param("ss", $newAgStatus, $ag_id);
    $stmtUpdateBorrowing->execute();

    // หลังจากทำการอัปเดตเสร็จ, เปลี่ยนเส้นทางกลับไปที่หน้า home_it.php
    header("Location: home_it.php");
    exit();
} else {
    echo "Invalid request method.";
    exit();
}
?>
