<?php
session_start();
include "../connect.php";
// ตรวจสอบว่าข้อมูลถูกส่งมาครบหรือไม่
if (isset($_POST['type_name']) && isset($_POST['type_description'])) {
    $type_name = $_POST['type_name'];
    $type_description = $_POST['type_description'];
    // ตรวจสอบว่ามีชื่อประเภทอุปกรณ์ซ้ำหรือไม่
    $sql_check = "SELECT * FROM item_type WHERE type_name = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $type_name);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        // ถ้าพบชื่อประเภทอุปกรณ์ซ้ำ
        echo "<script>
                alert('ชื่อประเภทอุปกรณ์นี้มีอยู่แล้วในระบบ กรุณาใช้ชื่อประเภทอื่น');
                window.location.href = 'home_it.php';
              </script>";
    } else {
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
        $sql_insert = "INSERT INTO item_type (type_id, type_name, type_description) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sss", $new_type_id, $type_name, $type_description);
        if ($stmt_insert->execute()) {
            // แสดงข้อความแจ้งเตือนและ redirect ไปยังหน้า home_it.php
            echo "<script>
                    alert('เพิ่มประเภทอุปกรณ์เรียบร้อยแล้ว พร้อมรหัสประเภท: " . $new_type_id . "');
                    window.location.href = 'home_it.php';
                  </script>";
        } else {
            // แสดงข้อความข้อผิดพลาดหากการเพิ่มข้อมูลไม่สำเร็จ
            echo "<script>
                    alert('เกิดข้อผิดพลาด: " . $stmt_insert->error . "');
                    window.location.href = 'home_it.php';
                  </script>";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
} else {
    // กรณีที่ข้อมูลไม่ครบ
    echo "<script>
            alert('Error: Required data is missing.');
            window.location.href = 'home_it.php';
          </script>";
}
$conn->close();
