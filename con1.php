<?php 
session_start();
include 'connect.php';

if(isset($_POST['username']) && isset($_POST['password'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // คำสั่ง SQL เพื่อดึงข้อมูลชื่อผู้ใช้ รหัสผ่าน และสถานะ
    $sql = "SELECT u_username, u_password, u_fname, u_lname, u_address, u_status_id FROM users WHERE u_username = '$username' AND u_password = '$password'";
    $result = mysqli_query($conn, $sql);

    if($result){
        // ตรวจสอบว่ามีผลลัพธ์หรือไม่
        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);
            $u_status_id = $row['u_status_id'];

            // ตรวจสอบค่า u_status_id และเปลี่ยนเส้นทางตามนั้น
            if($u_status_id == 1){
                $_SESSION['username'] = $row['u_username'];
                $_SESSION['u_fname'] = $row['u_fname'];
                $_SESSION['u_lname'] = $row['u_lname'];
                $_SESSION['u_address'] = $row['u_address'];
                
                header("Location: พนักงาน/home.php");
                exit();
            } elseif($u_status_id == 2){
                header("Location: ช่างIT/home_it.php");
                exit();
            } elseif($u_status_id == 3){
                header("Location: แอดมิน/home_admin.php");
                exit();
            } else {
                // กรณีที่ไม่มีสถานะที่ตรงกัน
                echo "Invalid status.";
            }
        } else {
            // ถ้าไม่มีผลลัพธ์จากคำสั่ง SQL (ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง)
            echo "Invalid username or password.";
        }
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>
