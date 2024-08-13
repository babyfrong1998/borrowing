<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// รับข้อมูลจาก AJAX
if (isset($_POST['b_id']) && isset($_POST['return_date'])) {
    $b_id = $_POST['b_id'];
    $return_date = $_POST['return_date'];

    // ป้องกัน SQL Injection
    $b_id = mysqli_real_escape_string($conn, $b_id);
    $return_date = mysqli_real_escape_string($conn, $return_date);

    // อัพเดตฐานข้อมูล
    $sql = "UPDATE borrowing SET b_return = '$return_date' WHERE b_id = '$b_id'";

    if (mysqli_query($conn, $sql)) {
        echo "Update successful";
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>
