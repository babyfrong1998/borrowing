<?php
include "../connect.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = $_POST['u_fname'];
    $lname = $_POST['u_lname'];
    $email = $_POST['u_email'];
    $address = $_POST['u_address'];
    $username = $_POST['u_username'];
    $password = ($_POST['u_password']);
    $status = $_POST['u_status_id'];

    // สร้าง u_id ที่เริ่มต้นด้วย U ตามด้วยตัวเลขที่ยังไม่มีในฐานข้อมูล
    $lastIdQuery = "SELECT u_id FROM users ORDER BY u_id DESC LIMIT 1";
    $lastIdResult = $conn->query($lastIdQuery);
    
    if ($lastIdResult->num_rows > 0) {
        $lastIdRow = $lastIdResult->fetch_assoc();
        $lastIdNum = intval(substr($lastIdRow['u_id'], 1)) + 1; // ดึงเลขส่วนท้ายออกมาแล้วบวก 1
    } else {
        $lastIdNum = 1; // ถ้ายังไม่มี u_id ใดๆ ให้เริ่มที่ 1
    }
    
    $newUserId = 'U' . str_pad($lastIdNum, 3, '0', STR_PAD_LEFT); // เจนค่า u_id เช่น U001, U002, U003 เป็นต้น

    $sql = "INSERT INTO users (u_id, u_fname, u_lname, u_email, u_address, u_username, u_password, u_status_id) 
            VALUES ('$newUserId', '$fname', '$lname', '$email', '$address', '$username', '$password', '$status')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_user.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
