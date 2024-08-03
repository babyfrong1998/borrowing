<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit'])) {
    // รับค่าจากฟอร์มและป้องกัน SQL Injection
    $b_name = mysqli_real_escape_string($conn, $_POST['itemselect']);
    $b_date = mysqli_real_escape_string($conn, $_POST['bordate']);
    $b_borrower = mysqli_real_escape_string($conn, $_POST['u_id']);
    $b_agency = mysqli_real_escape_string($conn, $_POST['u_address']);
    $b_status = mysqli_real_escape_string($conn, $_POST['b_status']);

    // คำสั่ง SQL สำหรับเพิ่มข้อมูลการยืมครุภัณฑ์
    $sql = "INSERT INTO borrowing (b_name, b_date, b_borrower, b_return_p, b_agency, b_status, b_return) 
            VALUES ('$b_name', '$b_date', '$b_borrower', NULL, '$b_agency', '$b_status', NULL)";

    // ตรวจสอบผลการเพิ่มข้อมูล
    if (mysqli_query($conn, $sql)) {
        echo "บันทึกการยืมเรียบร้อยแล้ว";
        header("Location: home.php");
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
