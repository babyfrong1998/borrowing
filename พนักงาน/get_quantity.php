<?php
include "../connect.php";

if(isset($_POST['type_id'])) {
    $type_id = $_POST['type_id'];

    // ดึงข้อมูลจำนวนเครื่องที่มีสถานะเป็น ST001 และประเภทตรงกับที่เลือก
    $sql = "SELECT COUNT(*) as available FROM items_1 WHERE ag_type = '$type_id' AND ag_status = 'ST001'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $available = $row['available'];

    if($available > 0) {
        for($i = 1; $i <= $available; $i++) {
            echo "<option value='$i'>$i</option>";
        }
    } else {
        echo "<option value='0'>ไม่มีอุปกรณ์ที่พร้อมใช้งาน</option>";
    }
}
?>
