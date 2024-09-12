<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าข้อมูลถูกส่งมาครบหรือไม่
if (isset($_POST['ag_type']) && isset($_POST['ag_id']) && isset($_POST['ag_name'])) {
    $ag_type = $_POST['ag_type'];
    $ag_id = $_POST['ag_id'];
    $ag_name = $_POST['ag_name'];

    // ตรวจสอบว่ามีรหัสอุปกรณ์ซ้ำหรือไม่
    $sql_check_id = "SELECT * FROM items_1 WHERE ag_id = ?";
    $stmt_check_id = $conn->prepare($sql_check_id);
    $stmt_check_id->bind_param("s", $ag_id);
    $stmt_check_id->execute();
    $result_check_id = $stmt_check_id->get_result();

    if ($result_check_id->num_rows > 0) {
        // ถ้าพบรหัสอุปกรณ์ซ้ำ
        echo "<script>
                alert('รหัสอุปกรณ์นี้มีอยู่แล้วในระบบ กรุณาเปลี่ยนรหัสอุปกรณ์');
                window.location.href = 'home_it.php';
              </script>";
    } else {
        // ตรวจสอบว่ามีชื่ออุปกรณ์ซ้ำหรือไม่
        $sql_check_name = "SELECT * FROM items_1 WHERE ag_name = ?";
        $stmt_check_name = $conn->prepare($sql_check_name);
        $stmt_check_name->bind_param("s", $ag_name);
        $stmt_check_name->execute();
        $result_check_name = $stmt_check_name->get_result();

        if ($result_check_name->num_rows > 0) {
            // ถ้าพบชื่ออุปกรณ์ซ้ำ
            echo "<script>
                    alert('ชื่ออุปกรณ์นี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออุปกรณ์อื่น');
                    window.location.href = 'home_it.php';
                  </script>";
        } else {
            // ถ้าไม่พบชื่ออุปกรณ์ซ้ำ ให้เพิ่มอุปกรณ์ใหม่ลงในฐานข้อมูล
            $sql_insert = "INSERT INTO items_1 (ag_type, ag_id, ag_name, ag_status) VALUES (?, ?, ?, 'ST001')";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $ag_type, $ag_id, $ag_name);

            if ($stmt_insert->execute()) {
                // แสดงข้อความแจ้งเตือนและ redirect ไปยังหน้า home_it.php
                echo "<script>
                        alert('เพิ่มอุปกรณ์เรียบร้อยแล้ว');
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
        $stmt_check_name->close();
    }
    $stmt_check_id->close();
} else {
    // กรณีที่ข้อมูลไม่ครบ
    echo "<script>
            alert('Error: Required data is missing.');
            window.location.href = 'home_it.php';
          </script>";
}

$conn->close();
?>
