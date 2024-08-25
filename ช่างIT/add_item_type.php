<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าข้อมูลถูกส่งมาครบหรือไม่
if (isset($_POST['type_name']) && isset($_POST['type_description'])) {
    $type_name = $_POST['type_name'];
    $type_description = $_POST['type_description'];

    // ค้นหา type_id ที่มีอยู่แล้วในฐานข้อมูล
    $sql = "SELECT type_id FROM item_type ORDER BY type_id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // ดึง type_id ล่าสุดและเพิ่มหมายเลขใหม่
        $row = $result->fetch_assoc();
        $last_type_id = $row['type_id'];
        $new_id_number = intval(substr($last_type_id, 1)) + 1;  // ตัดตัว 'T' ออกและเพิ่ม 1
        $new_type_id = 'T' . str_pad($new_id_number, 3, '0', STR_PAD_LEFT);  // เติม 0 ด้านหน้าให้ได้ 3 หลัก
    } else {
        // ถ้าไม่มีข้อมูลในฐานข้อมูล ให้เริ่มจาก T001
        $new_type_id = 'T001';
    }

    // เพิ่มประเภทอุปกรณ์ลงในฐานข้อมูล
    $sql = "INSERT INTO item_type (type_id, type_name, type_description) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $new_type_id, $type_name, $type_description);
    if ($stmt->execute()) {
        // แสดงข้อความแจ้งเตือนและ redirect ไปยังหน้า home_it.php
        echo "<script>
                alert('เพิ่มประเภทอุปกรณ์เรียบร้อยแล้ว พร้อมรหัสประเภท: " . $new_type_id . "');
                window.location.href = 'home_it.php';
              </script>";
    } else {
        echo "<script>
                alert('เกิดข้อผิดพลาด: " . $stmt->error . "');
                window.location.href = 'home_it.php';
              </script>";
    }
    $stmt->close();
} else {
    echo "<script>
            alert('Error: Required data is missing.');
            window.location.href = 'home_it.php';
          </script>";
}

$conn->close();
?>
