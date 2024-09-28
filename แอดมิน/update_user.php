<?php
include "../connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $u_id = $_POST['u_id'];
    $fname = $_POST['u_fname'];
    $lname = $_POST['u_lname'];
    $email = $_POST['u_email'];
    $address = $_POST['u_address'];
    $username = $_POST['u_username'];
    $status = $_POST['u_status_id'];
    // อัพเดตข้อมูลผู้ใช้ในฐานข้อมูล
    $sql = "UPDATE users SET u_fname = '$fname', u_lname = '$lname', u_email = '$email', u_address = '$address', 
            u_username = '$username', u_status_id = '$status' WHERE u_id = '$u_id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_user.php");
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

$conn->close();
?>
