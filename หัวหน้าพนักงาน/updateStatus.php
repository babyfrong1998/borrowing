<?php
include "../connect.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ag_id = $_POST['ag_id']; // จะยังใช้ในตาราง items_1
    $BruID = $_POST['BruID']; // ใช้ในทั้งสองตาราง

    // อัพเดทสถานะ ag_status เป็น ST006 ในตาราง items_1
    $sql = "UPDATE items_1 SET ag_status = 'ST006' WHERE ag_id = ? AND BruID = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $ag_id, $BruID);

        if ($stmt->execute()) {
            // อัพเดท st_id เป็น ST006 ในตาราง borroww โดยใช้เฉพาะ BruID
            $sql_update_borroww = "UPDATE borroww SET st_id = 'ST006' WHERE BruID = ?";
            
            if ($stmt_update_borroww = $conn->prepare($sql_update_borroww)) {
                $stmt_update_borroww->bind_param("s", $BruID);
                
                if ($stmt_update_borroww->execute()) {
                    echo "success";
                } else {
                    echo "error: " . $stmt_update_borroww->error;
                }

                $stmt_update_borroww->close();
            } else {
                echo "error: " . $conn->error;
            }
        } else {
            echo "error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "error: " . $conn->error;
    }
    
    $conn->close();
}
?>
